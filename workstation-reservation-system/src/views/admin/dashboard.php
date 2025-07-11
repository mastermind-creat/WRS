<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
$workstations = Workstation::getAllWorkstations($pdo);
$workstationCount = count($workstations);
$reservationModel = new Reservation($pdo);
$reservations = $reservationModel->getAllReservations();
$reservationCount = count($reservations);

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
usort($reservations, function($a, $b) {
    return strtotime($b['start_time']) - strtotime($a['start_time']);
});
$recentReservations = array_slice($reservations, 0, 5);

// Get 5 most recent users
usort($users, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
$recentUsers = array_slice($users, 0, 5);

$busyCount = 0;
$idleCount = 0;
foreach ($workstations as $ws) {
    if (strtolower($ws['status']) === 'busy' || strtolower($ws['status']) === 'reserved') {
        $busyCount++;
    } else {
        $idleCount++;
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
            <main class="col-lg-9 dashboard-main fade-in" style="margin-top: 70px;">
                <div class="welcome-banner">
                    <div>
                        <h2 class="mb-1">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                        <div class="date"><i class="bi bi-calendar-event"></i> <?php echo date('l, F j, Y'); ?></div>
                    </div>
                    <div class="d-flex align-items-center justify-content-end">
                        <div>
                            <span class="fs-5">System Quick Stats</span>
                            <span class="badge bg-primary ms-2" title="Total Users"><i class="bi bi-people-fill"></i> <?php echo $userCount; ?></span>
                            <span class="badge bg-success ms-2" title="Total Workstations"><i class="bi bi-pc-display-horizontal"></i> <?php echo $workstationCount; ?></span>
                            <span class="badge bg-warning text-dark ms-2" title="Total Reservations"><i class="bi bi-calendar-check"></i> <?php echo $reservationCount; ?></span>
                        </div>
                        <button class="darkmode-toggle ms-3" id="darkmode-toggle" title="Toggle dark mode"><i class="bi bi-moon-stars"></i></button>
                    </div>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-md-4 slide-in">
                        <div class="card shadow text-center border-0 card-equal" style="background: linear-gradient(135deg, #4f8cff 0%, #6dd5ed 100%); color: #fff;" data-bs-toggle="tooltip" data-bs-placement="top" title="Total registered users">
                            <div class="card-body">
                                <i class="bi bi-people-fill display-5"></i>
                                <h5 class="card-title mt-2">Users</h5>
                                <p class="card-text fs-4 fw-bold"><?php echo $userCount; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 slide-in">
                        <div class="card shadow text-center border-0 card-equal" style="background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%); color: #fff;" data-bs-toggle="tooltip" data-bs-placement="top" title="Total workstations, busy and idle">
                            <div class="card-body">
                                <i class="bi bi-pc-display-horizontal display-5"></i>
                                <h5 class="card-title mt-2">Workstations</h5>
                                <p class="card-text fs-4 fw-bold"><?php echo $workstationCount; ?></p>
                                <div class="progress my-2" title="Busy/Idle Ratio">
                                    <?php $busyPercent = $workstationCount > 0 ? round(($busyCount / $workstationCount) * 100) : 0; ?>
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $busyPercent; ?>%" aria-valuenow="<?php echo $busyPercent; ?>" aria-valuemin="0" aria-valuemax="100">Busy: <?php echo $busyCount; ?></div>
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo 100 - $busyPercent; ?>%" aria-valuenow="<?php echo 100 - $busyPercent; ?>" aria-valuemin="0" aria-valuemax="100">Idle: <?php echo $idleCount; ?></div>
                                </div>
                                <span class="badge bg-danger me-2" title="Busy workstations">Busy: <?php echo $busyCount; ?></span>
                                <span class="badge bg-success" title="Idle workstations">Idle: <?php echo $idleCount; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 slide-in">
                        <div class="card shadow text-center border-0 card-equal" style="background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); color: #333;" data-bs-toggle="tooltip" data-bs-placement="top" title="Total reservations in the system">
                            <div class="card-body">
                                <i class="bi bi-calendar-check display-5"></i>
                                <h5 class="card-title mt-2">Reservations</h5>
                                <p class="card-text fs-4 fw-bold"><?php echo $reservationCount; ?></p>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="row g-4 mb-4">
                    <div class="col-md-8">
                        <div class="card shadow h-100 card-equal">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-graph-up"></i> Reservations (Last 7 Days)</h5>
                                <canvas id="reservationsChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow h-100 text-center card-equal">
                            <div class="card-body">
                                <i class="bi bi-star-fill display-6 text-warning"></i>
                                <h5 class="card-title mt-2">Most Active Users</h5>
                                <div class="d-flex flex-column align-items-center mt-3">
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
                                        $avatarUrl = !empty($u['avatar']) ? '/WRS/workstation-reservation-system/uploads/avatars/' . $u['avatar'] : 'https://ui-avatars.com/api/?name=' . urlencode($u['username']) . '&background=185a9d&color=fff&size=64';
                                    ?>
                                    <div class="d-flex align-items-center mb-2 w-100">
                                        <img src="<?php echo $avatarUrl; ?>" alt="Profile" class="rounded-circle me-2" style="width:40px;height:40px;object-fit:cover;box-shadow:0 2px 8px rgba(0,0,0,0.10);">
                                        <span class="fw-semibold text-truncate" style="max-width:120px;"> <?php echo htmlspecialchars($u['username']); ?> </span>
                                        <span class="badge bg-light text-dark ms-auto"> <?php echo $userReservationCounts[$uid]; ?> </span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <a href="/WRS/workstation-reservation-system/src/views/admin/reports.php" class="text-decoration-none mt-3 d-block">
                            <div class="card shadow h-100 text-center card-equal">
                                <div class="card-body">
                                    <i class="bi bi-bar-chart-line display-6 text-secondary"></i>
                                    <h5 class="card-title mt-2">View Reports</h5>
                                </div>
                            </div>
                        </a>
                        <a href="/WRS/workstation-reservation-system/src/views/auth/logout.php" class="text-decoration-none mt-3 d-block">
                            <div class="card shadow h-100 text-center card-equal">
                                <div class="card-body">
                                    <i class="bi bi-box-arrow-right display-6 text-danger"></i>
                                    <h5 class="card-title mt-2">Logout</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="row g-4 mt-4">
                    <div class="col-8 mb-4">
                        <div class="card shadow card-equal">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-clock-history"></i> Recent Reservations</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Workstation</th>
                                                <th>Start</th>
                                                <th>End</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (
                                                $recentReservations as $r): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($r['user_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($r['workstation_id']); ?></td>
                                                    <td><?php echo date('M d, H:i', strtotime($r['start_time'])); ?></td>
                                                    <td><?php echo date('M d, H:i', strtotime($r['end_time'])); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $r['status'] === 'approved' ? 'success' : ($r['status'] === 'pending' ? 'warning text-dark' : 'danger'); ?>">
                                                            <?php echo ucfirst($r['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($r['status'] === 'pending'): ?>
                                                            <a href="/WRS/workstation-reservation-system/src/views/admin/approve.php?id=<?php echo $r['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                                                            <a href="/WRS/workstation-reservation-system/src/views/admin/cancel.php?id=<?php echo $r['id']; ?>" class="btn btn-danger btn-sm">Reject</a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-8">
                        <div class="card shadow card-equal">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-person-lines-fill"></i> Recent Users</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Role</th>
                                                <th>Registered</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentUsers as $u): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                                                    <td><span class="badge bg-<?php echo $u['role'] === 'admin' ? 'primary' : 'secondary'; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                                                    <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
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
                    legend: { display: false },
                    tooltip: { enabled: true }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    </script>
    <script>
// Enable Bootstrap tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl);
});
// Dark mode toggle
const toggleBtn = document.getElementById('darkmode-toggle');
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
function setTheme(theme) {
    document.body.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    toggleBtn.innerHTML = theme === 'dark' ? '<i class="bi bi-brightness-high"></i>' : '<i class="bi bi-moon-stars"></i>';
}
const savedTheme = localStorage.getItem('theme');
if (savedTheme) {
    setTheme(savedTheme);
} else if (prefersDark) {
    setTheme('dark');
}
toggleBtn.addEventListener('click', function() {
    const current = document.body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    setTheme(current);
});
</script>
</body>
</html>