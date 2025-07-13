<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header('Location: /WRS/workstation-reservation-system/src/views/auth/login.php');
    exit();
}
require_once '../../config/database.php';
require_once '../../models/User.php';
require_once '../../models/Workstation.php';
require_once '../../models/Reservation.php';

// Get system stats
$userModel = new User($pdo);
$users = $userModel->getAllUsers();
$userCount = count($users);

// Count users by role
$superAdminCount = 0;
$adminCount = 0;
$regularUserCount = 0;
foreach ($users as $user) {
    switch ($user['role']) {
        case 'super_admin':
            $superAdminCount++;
            break;
        case 'admin':
            $adminCount++;
            break;
        case 'user':
            $regularUserCount++;
            break;
    }
}

$workstations = Workstation::getAllWorkstations($pdo);
$workstationCount = count($workstations);
$reservationModel = new Reservation($pdo);
$reservations = $reservationModel->getAllReservations();
$reservationCount = count($reservations);

// Count reservations by status
$approvedCount = 0;
$pendingCount = 0;
$rejectedCount = 0;
foreach ($reservations as $reservation) {
    switch ($reservation['status']) {
        case 'approved':
            $approvedCount++;
            break;
        case 'pending':
            $pendingCount++;
            break;
        case 'canceled':
            $rejectedCount++;
            break;
    }
}

// Prepare data for charts (last 7 days)
$reservationsPerDay = [];
$labels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('D', strtotime($date));
    $count = 0;
    foreach ($reservations as $r) {
        if (substr($r['start_time'], 0, 10) === $date) {
            $count++;
        }
    }
    $reservationsPerDay[] = $count;
}

// Get 5 most recent reservations
usort($reservations, function ($a, $b) {
    return strtotime($b['start_time']) - strtotime($a['start_time']);
});
$recentReservations = array_slice($reservations, 0, 5);

// Get 5 most recent users
usort($users, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
$recentUsers = array_slice($users, 0, 5);

// Pagination for Recent Reservations
$resPerPage = 5;
$resPage = isset($_GET['res_page']) ? max(1, intval($_GET['res_page'])) : 1;
$resTotal = count($recentReservations);
$resPages = max(1, ceil($resTotal / $resPerPage));
$resStart = ($resPage - 1) * $resPerPage;
$resPaginated = array_slice($recentReservations, $resStart, $resPerPage);
// Pagination for Recent Users
$userPerPage = 5;
$userPage = isset($_GET['user_page']) ? max(1, intval($_GET['user_page'])) : 1;
$userTotal = count($recentUsers);
$userPages = max(1, ceil($userTotal / $userPerPage));
$userStart = ($userPage - 1) * $userPerPage;
$userPaginated = array_slice($recentUsers, $userStart, $userPerPage);

$busyCount = 0;
$idleCount = 0;
foreach ($workstations as $ws) {
    if (strtolower($ws['status']) === 'busy' || strtolower($ws['status']) === 'reserved') {
        $busyCount++;
    } else {
        $idleCount++;
    }
}

// Fetch admin avatar and name for welcome banner
$adminAvatarUrl = null;
$adminName = null;
if (isset($_SESSION['user_id'])) {
    $admin = $userModel->getUserById($_SESSION['user_id']);
    if ($admin) {
        $adminName = $admin['username'];
        if (!empty($admin['avatar'])) {
            $adminAvatarUrl = '/WRS/workstation-reservation-system/uploads/avatars/' . $admin['avatar'];
        } else {
            $adminAvatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($admin['username']) . '&background=185a9d&color=fff&size=64';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Workstation Reservation System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/WRS/workstation-reservation-system/src/public/css/admin.css">
</head>

<body>
    <?php include __DIR__ . '/../layout/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <div class="col-lg-3 p-0 sidebar">
                <?php include __DIR__ . '/../layout/sidebar.php'; ?>
            </div>
            <main class="col-lg-9 dashboard-main fade-in">
                <div class="welcome-banner d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <img src="<?php echo $adminAvatarUrl; ?>" alt="Admin Avatar" class="rounded-circle me-3"
                            style="width:64px;height:64px;box-shadow:0 2px 8px rgba(0,0,0,0.12);">
                        <div>
                            <h2 class="mb-1">Welcome, <?php echo htmlspecialchars($adminName); ?>.</h2>
                            <div class="date"><i class="bi bi-calendar-event"></i> <?php echo date('l, F j, Y'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-end">
                        <div>
                            <span class="fs-5">System Quick Stats</span>
                            <span class="badge bg-primary ms-2" title="Total Users"><i class="bi bi-people-fill"></i>
                                <?php echo $userCount; ?></span>
                            <span class="badge bg-success ms-2" title="Total Workstations"><i
                                    class="bi bi-pc-display-horizontal"></i> <?php echo $workstationCount; ?></span>
                            <span class="badge bg-warning text-dark ms-2" title="Total Reservations"><i
                                    class="bi bi-calendar-check"></i> <?php echo $reservationCount; ?></span>
                        </div>
                    </div>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-md-4 slide-in">
                        <div class="card shadow text-center border-0 card-equal card-action"
                            style="background: linear-gradient(135deg, #4f8cff 0%, #6dd5ed 100%); color: #fff;">
                            <div class="card-body">
                                <i class="bi bi-people-fill display-5"></i>
                                <h5 class="card-title mt-2">Users</h5>
                                <p class="card-text fs-4 fw-bold"><?php echo $userCount; ?></p>
                                <div class="d-flex justify-content-center gap-2 mt-2">
                                    <span class="badge bg-danger" title="Super Admins">
                                        <i class="bi bi-shield-star me-1"></i><?php echo $superAdminCount; ?>
                                    </span>
                                    <span class="badge bg-primary" title="Admins">
                                        <i class="bi bi-shield-check me-1"></i><?php echo $adminCount; ?>
                                    </span>
                                    <span class="badge bg-light text-dark" title="Regular Users">
                                        <i class="bi bi-person me-1"></i><?php echo $regularUserCount; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 slide-in">
                        <div class="card shadow text-center border-0 card-equal card-action"
                            style="background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%); color: #fff;">
                            <div class="card-body">
                                <i class="bi bi-pc-display-horizontal display-5"></i>
                                <h5 class="card-title mt-2">Workstations</h5>
                                <p class="card-text fs-4 fw-bold"><?php echo $workstationCount; ?></p>
                                <div class="progress my-2" title="Busy/Idle Ratio">
                                    <?php $busyPercent = $workstationCount > 0 ? round(($busyCount / $workstationCount) * 100) : 0; ?>
                                    <div class="progress-bar bg-danger" role="progressbar"
                                        style="width: <?php echo $busyPercent; ?>%"
                                        aria-valuenow="<?php echo $busyPercent; ?>" aria-valuemin="0"
                                        aria-valuemax="100">Busy: <?php echo $busyCount; ?></div>
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: <?php echo 100 - $busyPercent; ?>%"
                                        aria-valuenow="<?php echo 100 - $busyPercent; ?>" aria-valuemin="0"
                                        aria-valuemax="100">Idle: <?php echo $idleCount; ?></div>
                                </div>
                                <span class="badge bg-danger me-2" title="Busy workstations">Busy:
                                    <?php echo $busyCount; ?></span>
                                <span class="badge bg-success" title="Idle workstations">Idle:
                                    <?php echo $idleCount; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 slide-in">
                        <div class="card shadow text-center border-0 card-equal card-action"
                            style="background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); color: #333;">
                            <div class="card-body">
                                <i class="bi bi-calendar-check display-5"></i>
                                <h5 class="card-title mt-2">Reservations</h5>
                                <p class="card-text fs-4 fw-bold"><?php echo $reservationCount; ?></p>
                                <div class="d-flex justify-content-center gap-2 mt-2">
                                    <span class="badge bg-success" title="Approved">
                                        <i class="bi bi-check-circle me-1"></i><?php echo $approvedCount; ?>
                                    </span>
                                    <span class="badge bg-warning text-dark" title="Pending">
                                        <i class="bi bi-clock me-1"></i><?php echo $pendingCount; ?>
                                    </span>
                                    <span class="badge bg-danger" title="Rejected/Canceled">
                                        <i class="bi bi-x-circle me-1"></i><?php echo $rejectedCount; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Quick Action Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3 slide-in">
                        <a href="/WRS/workstation-reservation-system/src/views/admin/users.php"
                            class="text-decoration-none">
                            <div class="card card-action text-center border-0 shadow h-100"
                                style="background: linear-gradient(135deg, #4f8cff 0%, #6dd5ed 100%); color: #fff;">
                                <div class="card-body">
                                    <i class="bi bi-people display-5"></i>
                                    <h5 class="card-title mt-2">Manage Users</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 slide-in">
                        <a href="/WRS/workstation-reservation-system/src/views/admin/workstations.php"
                            class="text-decoration-none">
                            <div class="card card-action text-center border-0 shadow h-100"
                                style="background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%); color: #fff;">
                                <div class="card-body">
                                    <i class="bi bi-pc-display display-5"></i>
                                    <h5 class="card-title mt-2">Manage Workstations</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 slide-in">
                        <a href="/WRS/workstation-reservation-system/src/views/admin/reports.php"
                            class="text-decoration-none">
                            <div class="card card-action text-center border-0 shadow h-100"
                                style="background: linear-gradient(135deg, #ffb347 0%, #ffcc33 100%); color: #333;">
                                <div class="card-body">
                                    <i class="bi bi-bar-chart-line display-5"></i>
                                    <h5 class="card-title mt-2">View Reports</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 slide-in">
                        <a href="/WRS/workstation-reservation-system/src/views/admin/dashboard.php"
                            class="text-decoration-none">
                            <div class="card card-action text-center border-0 shadow h-100"
                                style="background: linear-gradient(135deg, #e96443 0%, #904e95 100%); color: #fff;">
                                <div class="card-body">
                                    <i class="bi bi-plus-circle display-5"></i>
                                    <h5 class="card-title mt-2">Add Workstation</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-md-8">
                        <div class="card shadow h-100 card-equal" style="min-height:290px; max-height:300px;">
                            <div class="card-body d-flex flex-column justify-content-center align-items-center"
                                style="height: 100%; min-height:280px; max-height:300px;">
                                <h5 class="card-title"><i class="bi bi-graph-up"></i> Reservations (Last 7 Days)</h5>
                                <canvas id="reservationsChart" height="60"
                                    style="max-width:100%;max-height:120px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow h-100 text-center card-equal"
                            style="min-height:290px; max-height:300px;">
                            <div class="card-body d-flex flex-column justify-content-center align-items-center"
                                style="height: 100%; min-height:280px; max-height:300px; overflow: hidden;">
                                <i class="bi bi-star-fill display-6 text-warning"></i>
                                <h5 class="card-title mt-2">Most Active Users</h5>
                                <div class="d-flex flex-column align-items-center mt-3 w-100" style="max-height: 140px; overflow-y: auto;">
                                    <?php
                                    // Count reservations per user
                                    $userReservationCounts = [];
                                    foreach ($reservations as $r) {
                                        $uid = $r['user_id'];
                                        if (!isset($userReservationCounts[$uid])) $userReservationCounts[$uid] = 0;
                                        $userReservationCounts[$uid]++;
                                    }
                                    // Sort by reservation count desc
                                    arsort($userReservationCounts);
                                    $topUserIds = array_slice(array_keys($userReservationCounts), 0, 5);
                                    // Map user id to user data
                                    $userMap = [];
                                    foreach ($users as $u) {
                                        $userMap[$u['id']] = $u;
                                    }
                                    foreach ($topUserIds as $uid):
                                        $u = $userMap[$uid] ?? null;
                                        if (!$u) continue;
                                        $avatarUrl = !empty($u['avatar']) ? '/WRS/workstation-reservation-system/uploads/avatars/' . $u['avatar'] : 'https://ui-avatars.com/api/?name=' . urlencode($u['username']) . '&background=185a9d&color=fff&size=40';
                                    ?>
                                        <div class="d-flex align-items-center mb-2 w-100" style="gap: 0.5rem;">
                                            <img src="<?php echo $avatarUrl; ?>" alt="Profile" class="rounded-circle"
                                                style="width:32px;height:32px;object-fit:cover;box-shadow:0 2px 8px rgba(0,0,0,0.10);">
                                            <span class="fw-semibold text-truncate" style="max-width:80px; font-size:0.98em;">
                                                <?php echo htmlspecialchars($u['username']); ?> </span>
                                            <span class="badge bg-light text-dark ms-auto" style="font-size:0.95em;">
                                                <?php echo $userReservationCounts[$uid]; ?> </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4 mt-4">
                    <div class="col-md-6 mb-4">
                        <div class="card shadow card-equal" style="min-height: 320px; max-width: 100%;">
                            <div class="card-body p-3">
                                <h5 class="card-title"><i class="bi bi-clock-history"></i> Recent Reservations</h5>
                                <div class="table-responsive" style="max-height: 260px; overflow-y: auto;">
                                    <table class="table table-striped align-middle mb-0 table-sm">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Workstation</th>
                                                <th>Start</th>
                                                <th>End</th>
                                                <th>Status</th>
                                                <!-- <th>Actions</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($resPaginated as $r):
                                                $u = $userMap[$r['user_id']] ?? null;
                                                $avatarUrl = $u && !empty($u['avatar']) ? '/WRS/workstation-reservation-system/uploads/avatars/' . $u['avatar'] : 'https://ui-avatars.com/api/?name=' . urlencode($u['username'] ?? $r['user_id']) . '&background=185a9d&color=fff&size=40';
                                            ?>
                                                <tr>
                                                    <td class="d-flex align-items-center gap-2">
                                                        <img src="<?php echo $avatarUrl; ?>" alt="Profile"
                                                            class="rounded-circle"
                                                            style="width:28px;height:28px;object-fit:cover;">
                                                        <span
                                                            style="font-size:0.95em;"><?php echo htmlspecialchars($u['username'] ?? $r['user_id']); ?></span>
                                                    </td>
                                                    <td style="font-size:0.95em;">
                                                        <?php echo htmlspecialchars($r['workstation_id']); ?></td>
                                                    <td style="font-size:0.95em;">
                                                        <?php echo date('M d, H:i', strtotime($r['start_time'])); ?></td>
                                                    <td style="font-size:0.95em;">
                                                        <?php echo date('M d, H:i', strtotime($r['end_time'])); ?></td>
                                                    <td>
                                                        <span
                                                            class="badge bg-<?php echo $r['status'] === 'approved' ? 'success' : ($r['status'] === 'pending' ? 'warning text-dark' : 'danger'); ?>">
                                                            <?php echo ucfirst($r['status']); ?>
                                                        </span>
                                                    </td>

                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Pagination for reservations -->
                                <nav aria-label="Reservations pagination" class="mt-2">
                                    <ul class="pagination pagination-sm justify-content-center mb-0">
                                        <?php for ($i = 1; $i <= $resPages; $i++): ?>
                                            <li class="page-item <?php if ($i == $resPage) echo 'active'; ?>">
                                                <a class="page-link"
                                                    href="?res_page=<?php echo $i; ?><?php if ($userPage > 1) echo '&user_page=' . $userPage; ?>">
                                                    <?php echo $i; ?> </a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card shadow card-equal" style="min-height: 320px; max-width: 100%;">
                            <div class="card-body p-3">
                                <h5 class="card-title"><i class="bi bi-person-lines-fill"></i> Recent Users</h5>
                                <div class="table-responsive" style="max-height: 260px; overflow-y: auto;">
                                    <table class="table table-striped align-middle mb-0 table-sm">
                                        <thead>
                                            <tr>
                                                <th>Avatar</th>
                                                <th>Username</th>
                                                <th>Role</th>
                                                <th>Registered</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($userPaginated as $u):
                                                $avatarUrl = !empty($u['avatar']) ? '/WRS/workstation-reservation-system/uploads/avatars/' . $u['avatar'] : 'https://ui-avatars.com/api/?name=' . urlencode($u['username']) . '&background=185a9d&color=fff&size=40';
                                            ?>
                                                <tr>
                                                    <td><img src="<?php echo $avatarUrl; ?>" alt="Profile"
                                                            class="rounded-circle"
                                                            style="width:28px;height:28px;object-fit:cover;"></td>
                                                    <td style="font-size:0.95em;">
                                                        <?php echo htmlspecialchars($u['username']); ?></td>
                                                    <td><span
                                                            class="badge bg-<?php echo $u['role'] === 'admin' ? 'primary' : 'secondary'; ?>">
                                                            <?php echo ucfirst($u['role']); ?> </span></td>
                                                    <td style="font-size:0.95em;">
                                                        <?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Pagination for users -->
                                <nav aria-label="Users pagination" class="mt-2">
                                    <ul class="pagination pagination-sm justify-content-center mb-0">
                                        <?php for ($i = 1; $i <= $userPages; $i++): ?>
                                            <li class="page-item <?php if ($i == $userPage) echo 'active'; ?>">
                                                <a class="page-link"
                                                    href="?user_page=<?php echo $i; ?><?php if ($resPage > 1) echo '&res_page=' . $resPage; ?>">
                                                    <?php echo $i; ?> </a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const ctx = document.getElementById('reservationsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Reservations',
                    data: <?php echo json_encode($reservationsPerDay); ?>,
                    borderColor: '#4f8cff',
                    backgroundColor: 'rgba(79,140,255,0.15)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
    <style>
        .dashboard-main .card {
            border-radius: 1.1rem !important;
            box-shadow: 0 2px 16px rgba(79, 140, 255, 0.10);
            transition: box-shadow 0.2s, transform 0.2s;
            padding-bottom: 0.5rem;
        }

        .dashboard-main .card:hover {
            box-shadow: 0 6px 32px rgba(79, 140, 255, 0.18);
            transform: translateY(-2px) scale(1.015);
        }

        .dashboard-main .card .card-body {
            padding: 2rem 1.5rem 1.5rem 1.5rem;
        }

        .dashboard-main .row.g-4 {
            margin-bottom: 2.5rem !important;
        }

        .dashboard-main .welcome-banner {
            margin-bottom: 2.5rem !important;
        }
    </style>
</body>

</html>