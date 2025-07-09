<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /WRS/workstation-reservation-system/src/views/auth/login.php');
    exit();
}
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
    header('Location: users.php?msg=promoted');
    exit();
}
// Handle delete user
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && isset($_GET['csrf']) && $_GET['csrf'] === $_SESSION['csrf_token']) {
    $userId = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $_SESSION['toast'] = 'User deleted.';
    header('Location: users.php');
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
    $_SESSION['toast'] = 'User updated.';
    header('Location: users.php');
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
            header('Location: users.php');
            exit();
        } catch (PDOException $e) {
            $message = 'Error: ' . htmlspecialchars($e->getMessage());
        }
    } else {
        $message = 'All fields are required.';
    }
}
// Toast notification
$toast = isset($_SESSION['toast']) ? $_SESSION['toast'] : '';
unset($_SESSION['toast']);

// Always fetch the latest user list after all actions
$users = $userModel->getAllUsers();

// Ensure these variables are always defined before HTML
if (!isset($search)) $search = '';
if (!isset($roleFilter)) $roleFilter = '';
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
        body { background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%); }
        .dashboard-main { background: #fff; border-radius: 1rem; box-shadow: 0 2px 16px rgba(79,140,255,0.08); padding: 2rem 2rem 1rem 2rem; min-height: 90vh; }
        @media (max-width: 991.98px) { .dashboard-main { padding: 1rem; } }
    </style>
</head>
<body>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-3 p-0">
            <?php include __DIR__ . '/../layout/sidebar.php'; ?>
        </div>
        <main class="col-lg-9 dashboard-main">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-people"></i> Manage Users</h2>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createAdminModal"><i class="bi bi-person-plus"></i> Create Admin</button>
            </div>
            <form class="row g-3 mb-3" method="get">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Search username or email" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="role">
                        <option value="all" <?php if ($roleFilter === 'all') echo 'selected'; ?>>All Roles</option>
                        <option value="admin" <?php if ($roleFilter === 'admin') echo 'selected'; ?>>Admin</option>
                        <option value="user" <?php if ($roleFilter === 'user') echo 'selected'; ?>>User</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> Filter</button>
                </div>
            </form>
            <?php if ($message): ?>
                <div class="alert alert-info text-center"> <?php echo $message; ?> </div>
            <?php endif; ?>
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'promoted'): ?>
                <div class="alert alert-success text-center">User promoted to admin successfully!</div>
            <?php endif; ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered</th>
                            <th>Reservations</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><span class="badge bg-<?php echo $u['role'] === 'admin' ? 'primary' : 'secondary'; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                <td><span class="badge bg-info text-dark"><?php echo count($reservationModel->getReservationsByUser($u['id'])); ?></span></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $u['id']; ?>"><i class="bi bi-pencil"></i></button>
                                        <?php if ($u['role'] !== 'admin'): ?>
                                            <a href="users.php?promote=<?php echo $u['id']; ?>&csrf=<?php echo $csrf_token; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-person-up"></i></a>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal<?php echo $u['id']; ?>"><i class="bi bi-trash"></i></button>
                                    </div>
                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editUserModal<?php echo $u['id']; ?>" tabindex="-1" aria-labelledby="editUserModalLabel<?php echo $u['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editUserModalLabel<?php echo $u['id']; ?>"><i class="bi bi-pencil"></i> Edit User</h5>
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
                                    <div class="modal fade" id="deleteUserModal<?php echo $u['id']; ?>" tabindex="-1" aria-labelledby="deleteUserModalLabel<?php echo $u['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteUserModalLabel<?php echo $u['id']; ?>"><i class="bi bi-trash"></i> Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete user <strong><?php echo htmlspecialchars($u['username']); ?></strong>?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <a href="users.php?delete=<?php echo $u['id']; ?>&csrf=<?php echo $csrf_token; ?>" class="btn btn-danger">Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Create Admin Modal -->
            <div class="modal fade" id="createAdminModal" tabindex="-1" aria-labelledby="createAdminModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <div class="modal-header">
                                <h5 class="modal-title" id="createAdminModalLabel"><i class="bi bi-person-plus"></i> Create Admin</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" name="password" required>
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
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
                <div id="toastMsg" class="toast align-items-center text-bg-primary border-0 <?php if ($toast) echo 'show'; ?>" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <?php echo htmlspecialchars($toast); ?>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    if (document.getElementById('toastMsg')) {
        var toast = new bootstrap.Toast(document.getElementById('toastMsg'));
        toast.show();
    }
</script>
</body>
</html> 