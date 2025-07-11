<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /WRS/workstation-reservation-system/src/views/auth/login.php');
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

require_once '../../config/database.php';
require_once '../../models/User.php';
require_once '../../models/Reservation.php';

$userModel = new User($pdo);
$reservationModel = new Reservation($pdo);

// Handle promote to admin
if (isset($_GET['promote']) && is_numeric($_GET['promote']) && isset($_GET['csrf']) && $_GET['csrf'] === $_SESSION['csrf_token']) {
    $userId = (int)$_GET['promote'];
    $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
    $stmt->execute([$userId]);
    $_SESSION['toast'] = 'User promoted to admin successfully!';
    $redirectParams = [];
    if (!empty($_GET['search'])) $redirectParams[] = 'search=' . urlencode($_GET['search']);
    if (!empty($_GET['role']) && $_GET['role'] !== 'all') $redirectParams[] = 'role=' . urlencode($_GET['role']);
    $redirectParams[] = 'msg=promoted';
    header('Location: users.php?' . implode('&', $redirectParams));
    exit();
}

// Handle delete user
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && isset($_GET['csrf']) && $_GET['csrf'] === $_SESSION['csrf_token']) {
    $userId = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $_SESSION['toast'] = 'User deleted successfully!';
    $redirectParams = [];
    if (!empty($_GET['search'])) $redirectParams[] = 'search=' . urlencode($_GET['search']);
    if (!empty($_GET['role']) && $_GET['role'] !== 'all') $redirectParams[] = 'role=' . urlencode($_GET['role']);
    $redirectParams[] = 'msg=deleted';
    header('Location: users.php?' . implode('&', $redirectParams));
    exit();
}

// Handle edit user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $userId = (int)$_POST['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, role=? WHERE id=?");
    $stmt->execute([$username, $email, $role, $userId]);
    $_SESSION['toast'] = 'User updated successfully!';
    $redirectParams = [];
    if (!empty($_POST['search'])) $redirectParams[] = 'search=' . urlencode($_POST['search']);
    if (!empty($_POST['role']) && $_POST['role'] !== 'all') $redirectParams[] = 'role=' . urlencode($_POST['role']);
    $redirectParams[] = 'msg=updated';
    header('Location: users.php?' . implode('&', $redirectParams));
    exit();
}

// Handle create admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if ($username && $email && $password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
        try {
            $stmt->execute([$username, $email, $hashed]);
            $_SESSION['toast'] = 'Admin created successfully!';
            $redirectParams = [];
            if (!empty($_POST['search'])) $redirectParams[] = 'search=' . urlencode($_POST['search']);
            if (!empty($_POST['role']) && $_POST['role'] !== 'all') $redirectParams[] = 'role=' . urlencode($_POST['role']);
            $redirectParams[] = 'msg=created';
            header('Location: users.php?' . implode('&', $redirectParams));
            exit();
        } catch (PDOException $e) {
            $message = 'Error: ' . htmlspecialchars($e->getMessage());
        }
    } else {
        $message = 'All fields are required.';
    }
}

$toast = isset($_SESSION['toast']) ? $_SESSION['toast'] : '';
unset($_SESSION['toast']);

$search = isset($_GET['search']) ? $_GET['search'] : '';
$roleFilter = isset($_GET['role']) ? $_GET['role'] : 'all';

$users = $userModel->getAllUsers();

if ($search) {
    $users = array_filter($users, function($u) use ($search) {
        return stripos($u['username'], $search) !== false || stripos($u['email'], $search) !== false;
    });
}

if ($roleFilter !== 'all') {
    $users = array_filter($users, function($u) use ($roleFilter) {
        return $u['role'] === $roleFilter;
    });
}

if (!isset($message)) $message = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        /* :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --sidebar-width: 240px;
        }
        
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, #232526 0%, #414345 100%);
        }
        
        .dashboard-main {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
        }
        
        .page-header {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        
        .user-table {
            background: white;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .user-table thead {
            background: var(--primary-gradient);
            color: white;
        }
        
        .user-table th {
            font-weight: 500;
            padding: 1rem 1.25rem;
        }
        
        .user-table td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
        }
        
        .badge-admin {
            background: var(--primary-gradient);
            color: white;
        }
        
        .badge-user {
            background: #e9ecef;
            color: #495057;
        }
        
        .btn-create {
            background: var(--primary-gradient);
            border: none;
            font-weight: 500;
        }
        
        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .toast-container {
            z-index: 1100;
        }
        
        @media (max-width: 991.98px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .dashboard-main {
                margin-left: 0;
                padding: 1rem;
            }
        } */
        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<div class="container-fluid">
    <div class="row flex-nowrap">
        <div class="col-lg-3 p-0 sidebar">
            <?php include __DIR__ . '/../layout/sidebar.php'; ?>
        </div>
        <main class="col-lg-9 dashboard-main">
            <!-- Page Header -->
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1"><i class="bi bi-people me-2"></i>Manage Users</h2>
                    <p class="mb-0 text-muted">Administer and manage system users</p>
                </div>
                <button class="btn btn-success text-white" data-bs-toggle="modal" data-bs-target="#createAdminModal">
                    <i class="bi bi-person-plus me-2"></i>Create Admin
                </button>
            </div>
            
            <!-- Search and Filter -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <form class="row g-3" method="get">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" name="search" placeholder="Search username or email" value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" name="role">
                                <option value="all" <?php if ($roleFilter === 'all') echo 'selected'; ?>>All Roles</option>
                                <option value="admin" <?php if ($roleFilter === 'admin') echo 'selected'; ?>>Admin</option>
                                <option value="user" <?php if ($roleFilter === 'user') echo 'selected'; ?>>User</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Messages -->
            <?php if ($message): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'promoted'): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    User promoted to admin successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'created'): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    Admin user created successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Users Table -->
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table user-table mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Registered</th>
                                <th>Reservations</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">No users found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><?php echo $u['id']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php
                                                $avatarUrl = !empty($u['avatar'])
                                                    ? '/WRS/workstation-reservation-system/uploads/avatars/' . $u['avatar']
                                                    : 'https://ui-avatars.com/api/?name=' . urlencode($u['username']) . '&background=667eea&color=fff&size=64';
                                                ?>
                                                <img src="<?php echo $avatarUrl; ?>" class="avatar me-2" alt="<?php echo htmlspecialchars($u['username']); ?>">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($u['username']); ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $u['role'] === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                                                <i class="bi <?php echo $u['role'] === 'admin' ? 'bi-shield-check' : 'bi-person'; ?> me-1"></i>
                                                <?php echo ucfirst($u['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                        <td>
                                            <span class="badge bg-info text-white">
                                                <i class="bi bi-calendar-check me-1"></i>
                                                <?php echo count($reservationModel->getReservationsByUser($u['id'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $u['id']; ?>" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <?php if ($u['role'] !== 'admin'): ?>
                                                    <a href="users.php?promote=<?php echo $u['id']; ?>&csrf=<?php echo $csrf_token; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>" class="btn btn-sm btn-outline-success" title="Promote">
                                                        <i class="bi bi-person-up"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal<?php echo $u['id']; ?>" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modals -->
<?php foreach ($users as $u): ?>
    <!-- Edit Modal -->
    <div class="modal fade" id="editUserModal<?php echo $u['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    <input type="hidden" name="role" value="<?php echo htmlspecialchars($roleFilter); ?>">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($u['username']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($u['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role">
                                <option value="user" <?php if ($u['role'] === 'user') echo 'selected'; ?>>User</option>
                                <option value="admin" <?php if ($u['role'] === 'admin') echo 'selected'; ?>>Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_user" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteUserModal<?php echo $u['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete user <strong><?php echo htmlspecialchars($u['username']); ?></strong>?</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        This action cannot be undone.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="users.php?delete=<?php echo $u['id']; ?>&csrf=<?php echo $csrf_token; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Create Admin Modal -->
<div class="modal fade" id="createAdminModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <input type="hidden" name="role" value="<?php echo htmlspecialchars($roleFilter); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Create Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                        <div class="form-text">Minimum 8 characters</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_admin" class="btn btn-success">Create Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3 toast-container">
    <div id="toastMsg" class="toast align-items-center text-white bg-primary border-0 <?php if ($toast) echo 'show'; ?>" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>
                <?php echo htmlspecialchars($toast); ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Initialize toast
    document.addEventListener('DOMContentLoaded', function() {
        var toastEl = document.getElementById('toastMsg');
        if (toastEl) {
            var toast = new bootstrap.Toast(toastEl);
            toast.show();
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                toast.hide();
            }, 5000);
        }
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Reset form when modal is closed
        var createAdminModal = document.getElementById('createAdminModal');
        if (createAdminModal) {
            createAdminModal.addEventListener('hidden.bs.modal', function () {
                var form = this.querySelector('form');
                if (form) {
                    form.reset();
                }
            });
        }
    });
</script>
</body>
</html>