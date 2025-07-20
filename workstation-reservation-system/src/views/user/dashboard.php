<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: /WRS/workstation-reservation-system/src/views/auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Workstation Reservation System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/WRS/workstation-reservation-system/src/public/css/user.css">
</head>
<body>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php
require_once '../../config/database.php';
require_once '../../models/User.php';
require_once '../../models/Reservation.php';
$userModel = new User($pdo);
$reservationModel = new Reservation($pdo);
// Pagination for reservation history
$perPage = 8;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;
$totalReservations = count($reservationModel->getReservationsByUser($_SESSION['user_id']));
$totalPages = ceil($totalReservations / $perPage);
$reservations = $reservationModel->getReservationsByUser($_SESSION['user_id'], $perPage, $offset);
$user = $userModel->getUserById($_SESSION['user_id']);
$resCount = count($reservations);
// Add this block to generate chart data for the last 7 days
$labels = [];
$reservationsPerDay = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('D', strtotime($date)); // e.g., Mon, Tue
    $reservationsPerDay[$date] = 0;
}
foreach ($reservations as $r) {
    $date = date('Y-m-d', strtotime($r['start_time']));
    if (isset($reservationsPerDay[$date])) {
        $reservationsPerDay[$date]++;
    }
}
$reservationsPerDay = array_values($reservationsPerDay); // for Chart.js
// Badge logic (same as achievements.php)
$badges = [
    [ 'name' => 'First Reservation', 'milestone' => 1, 'icon' => 'bi-emoji-smile', 'color' => 'success', 'desc' => 'Made your very first reservation!' ],
    [ 'name' => '5 Reservations', 'milestone' => 5, 'icon' => 'bi-calendar-check', 'color' => 'primary', 'desc' => 'Reserved 5 workstations.' ],
    [ 'name' => '10 Reservations', 'milestone' => 10, 'icon' => 'bi-lightning-charge', 'color' => 'warning', 'desc' => '10 reservations made!' ],
    [ 'name' => '20 Reservations', 'milestone' => 20, 'icon' => 'bi-trophy', 'color' => 'info', 'desc' => '20 reservations milestone.' ],
    [ 'name' => '30 Reservations', 'milestone' => 30, 'icon' => 'bi-award', 'color' => 'secondary', 'desc' => '30 reservations completed.' ],
    [ 'name' => '40 Reservations', 'milestone' => 40, 'icon' => 'bi-gem', 'color' => 'purple', 'desc' => '40 reservations achieved.' ],
    [ 'name' => '50 Reservations', 'milestone' => 50, 'icon' => 'bi-star-fill', 'color' => 'warning', 'desc' => '50 reservations superstar!' ],
    [ 'name' => '75 Reservations', 'milestone' => 75, 'icon' => 'bi-fire', 'color' => 'danger', 'desc' => '75 reservations on fire!' ],
    [ 'name' => '100 Reservations', 'milestone' => 100, 'icon' => 'bi-rocket', 'color' => 'success', 'desc' => '100 reservations rocket!' ],
    [ 'name' => '200 Reservations', 'milestone' => 200, 'icon' => 'bi-cup', 'color' => 'primary', 'desc' => '200 reservations champion!' ],
];
$recentBadge = null;
$nextBadge = null;
foreach ($badges as $i => $badge) {
    if ($resCount >= $badge['milestone']) {
        $recentBadge = $badge;
    } elseif ($nextBadge === null) {
        $nextBadge = $badge;
    }
}
$avatarUrl = !empty($user['avatar']) ? '/WRS/workstation-reservation-system/uploads/avatars/' . $user['avatar'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=185a9d&color=fff&size=64';
?>
<?php
// Find active reservation (approved, now between start and end)
$now = date('Y-m-d H:i:s');
$activeReservation = null;
foreach ($reservations as $r) {
    if ($r['status'] === 'approved' && $r['start_time'] <= $now && $r['end_time'] > $now) {
        $activeReservation = $r;
        break;
    }
}
// Helper: get the correct countdown start time (approved_at or start_time)
function getCountdownStart($reservation) {
    if (!empty($reservation['approved_at'])) {
        return $reservation['approved_at'];
    }
    return $reservation['start_time'];
}
?>
    <div class="dashboard-main fade-in">
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="welcome-banner">
                    <div>
                        <h2 class="mb-1">Welcome, <?php echo htmlspecialchars($user['username']); ?>.</h2>
                        <div class="date"><i class="bi bi-calendar-event"></i> <?php echo date('l, F j, Y'); ?></div>
                    </div>
                    <div class="d-flex align-items-center justify-content-end">
                        <div>
                            <span class="fs-5">Your Quick Stats</span>
                            <span class="badge bg-primary ms-2" title="Your User ID"><i class="bi bi-person-badge"></i> <?php echo $user['id']; ?></span>
                            <span class="badge bg-success ms-2" title="Total Reservations"><i class="bi bi-calendar-check"></i> <?php echo count($reservations); ?></span>
                        </div>
                    </div>
                    <button class="darkmode-toggle ms-3" id="darkmode-toggle" title="Toggle dark mode"><i class="bi bi-moon-stars"></i></button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center slide-in" style="background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%); color: #fff;" data-bs-toggle="tooltip" data-bs-placement="top" title="Your profile info">
                    <img src="<?php echo $avatarUrl; ?>" alt="Profile" class="rounded-circle me-3" style="width:64px;height:64px;box-shadow:0 2px 8px rgba(0,0,0,0.12);">
                    <div>
                        <h4 class="mb-0"><?php echo htmlspecialchars($user['username']); ?></h4>
                        <div class="small">Email: <?php echo htmlspecialchars($user['email']); ?></div>
                        <div class="small text-warning">User ID: <?php echo htmlspecialchars($user['id']); ?></div>
                        <?php if ($recentBadge): ?>
                        <div class="mt-2">
                            <span class="badge bg-<?php echo $recentBadge['color']; ?> d-inline-flex align-items-center" style="font-size:1em;"><i class="bi <?php echo $recentBadge['icon']; ?> me-1"></i> <?php echo $recentBadge['name']; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($nextBadge): ?>
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-muted">Next Badge: <strong><?php echo $nextBadge['name']; ?></strong></span>
                        <span class="small text-muted"><?php echo min($resCount, $nextBadge['milestone']); ?>/<?php echo $nextBadge['milestone']; ?></span>
                    </div>
                    <div class="progress" style="height: 18px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?php echo $nextBadge['color']; ?>" role="progressbar" style="width: <?php echo min(100, round(($resCount/$nextBadge['milestone'])*100)); ?>%" aria-valuenow="<?php echo min(100, round(($resCount/$nextBadge['milestone'])*100)); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="row g-4 mb-4">
            <div class="col-md-2 slide-in">
                <a href="/WRS/workstation-reservation-system/src/views/user/reserve.php" class="text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" title="Reserve a new workstation">
                    <div class="card card-action text-center border-0 shadow h-100" style="background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%); color: #fff;">
                        <div class="card-body">
                            <i class="bi bi-calendar-plus display-5"></i>
                            <h5 class="card-title mt-2">Reserve</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-2 slide-in">
                <a href="/WRS/workstation-reservation-system/src/views/user/my_reservations.php" class="text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" title="View your reservation history">
                    <div class="card card-action text-center border-0 shadow h-100" style="background: linear-gradient(135deg, #4f8cff 0%, #6dd5ed 100%); color: #fff;">
                        <div class="card-body">
                            <i class="bi bi-list-check display-5"></i>
                            <h5 class="card-title mt-2">My Reservations</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-2 slide-in">
                <a href="/WRS/workstation-reservation-system/src/views/user/profile.php" class="text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit your profile details">
                    <div class="card card-action text-center border-0 shadow h-100" style="background: linear-gradient(135deg, #185a9d 0%, #43cea2 100%); color: #fff;">
                        <div class="card-body">
                            <i class="bi bi-person-lines-fill display-5"></i>
                            <h5 class="card-title mt-2">Edit Profile</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-2 slide-in">
                <a href="/WRS/workstation-reservation-system/src/views/auth/logout.php" class="text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" title="Logout from your account">
                    <div class="card card-action text-center border-0 shadow h-100" style="background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); color: #333;">
                        <div class="card-body">
                            <i class="bi bi-box-arrow-right display-5"></i>
                            <h5 class="card-title mt-2">Logout</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-2 slide-in">
                <!-- Info card links to about.php -->
                <a href="/WRS/workstation-reservation-system/src/views/user/about.php" class="text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" title="About this system">
                    <div class="card card-action text-center border-0 shadow h-100" style="background: linear-gradient(135deg, #ffb347 0%, #ffcc33 100%); color: #333;">
                        <div class="card-body">
                            <i class="bi bi-info-circle display-5"></i>
                            <h5 class="card-title mt-2">Info</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-2 slide-in">
                <!-- Star card links to achievements.php -->
                <a href="/WRS/workstation-reservation-system/src/views/user/achievements.php" class="text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" title="View your achievements and badges">
                    <div class="card card-action text-center border-0 shadow h-100" style="background: linear-gradient(135deg, #e96443 0%, #904e95 100%); color: #fff;">
                        <div class="card-body">
                            <i class="bi bi-star display-5"></i>
                            <h5 class="card-title mt-2">Achievements</h5>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <?php if ($activeReservation): ?>
            <div class="alert alert-info d-flex align-items-center" id="countdown-alert">
                <i class="bi bi-clock-history me-2"></i>
                <div>
                    <strong>Active Reservation:</strong> Workstation <?php echo htmlspecialchars($activeReservation['workstation_id']); ?> <br>
                    Time left: <span id="countdown-timer"></span>
                </div>
            </div>
        <?php endif; ?>
        <div class="mt-4">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-clock-history"></i> Reservation History</h5>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Workstation</th>
                                            <th>Start</th>
                                            <th>End</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($reservations) === 0): ?>
                                            <tr><td colspan="4" class="text-center text-muted">No reservations found.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($reservations as $r): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($r['workstation_id']); ?></td>
                                                    <td><?php echo date('M d, H:i', strtotime($r['start_time'])); ?></td>
                                                    <td><?php echo date('M d, H:i', strtotime($r['end_time'])); ?></td>
                                                    <td><span class="badge bg-<?php echo $r['status'] === 'approved' ? 'success' : ($r['status'] === 'pending' ? 'warning text-dark' : 'danger'); ?>"><?php echo ucfirst($r['status']); ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm mb-4" style="height: 100%; min-height: 320px; max-height: 360px;">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center" style="height: 100%;">
                            <h5 class="card-title mb-3"><i class="bi bi-graph-up"></i> Your Reservations (Last 7 Days)</h5>
                            <?php
                            // Debug output for chart data
                            // echo '<pre style="font-size:0.9em;background:#f8f9fa;padding:8px;border-radius:6px;">';
                            // echo 'Labels: ' . htmlspecialchars(json_encode($labels)) . "\n";
                            // echo 'Data: ' . htmlspecialchars(json_encode($reservationsPerDay)) . "\n";
                            // echo '</pre>';
                            ?>
                            <canvas id="userReservationsChart" height="140" style="max-width:100%;max-height:180px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if ($totalPages > 1): ?>
        <div class="mt-4">
            <nav aria-label="Reservation pagination">
                <ul class="pagination justify-content-center mt-3">
                    <li class="page-item<?php if ($page <= 1) echo ' disabled'; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>" tabindex="-1">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item<?php if ($i == $page) echo ' active'; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item<?php if ($page >= $totalPages) echo ' disabled'; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
        <div class="mt-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-lightbulb"></i> Tips for a Smooth Reservation</h5>
                    <ul class="mb-0">
                        <li>Reserve your workstation in advance to secure your spot.</li>
                        <li>Check your reservation status regularly.</li>
                        <li>Cancel reservations you no longer need to free up resources for others.</li>
                        <li>Contact the lab admin for any issues or special requests.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Enable Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        // Dark mode toggle
        var toggleBtn = document.getElementById('darkmode-toggle');
        if (toggleBtn) {
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            function setTheme(theme) {
                document.body.setAttribute('data-theme', theme);
                localStorage.setItem('theme', theme);
                toggleBtn.innerHTML = theme === 'dark' ? '<i class="bi bi-brightness-high"></i>' : '<i class="bi bi-moon-stars"></i>';
            }
            var savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                setTheme(savedTheme);
            } else if (prefersDark) {
                setTheme('dark');
            }
            toggleBtn.addEventListener('click', function() {
                var current = document.body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                setTheme(current);
            });
        }
        // Chart.js for user reservations
        var chartCanvas = document.getElementById('userReservationsChart');
        if (chartCanvas) {
            var ctx = chartCanvas.getContext('2d');
            var chartData = {
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
            };
            var hasData = chartData.datasets[0].data.some(function(val){ return val > 0; });
            if (hasData) {
                new Chart(ctx, {
                    type: 'line',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: true }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1 } }
                        }
                    }
                });
            } else {
                chartCanvas.parentNode.innerHTML += '<div class="text-muted text-center mt-4">No reservation data for the last 7 days.</div>';
                chartCanvas.style.display = 'none';
            }
        }
    </script>
    <?php if ($activeReservation): ?>
<script>
(function() {
    // Output all times in UTC ISO format
    var startTime = new Date("<?php echo gmdate('Y-m-d\TH:i:s\Z', strtotime(getCountdownStart($activeReservation))); ?>").getTime();
    var endTime = new Date("<?php echo gmdate('Y-m-d\TH:i:s\Z', strtotime($activeReservation['end_time'])); ?>").getTime();
    // Get server's current UTC time as base
    var serverNow = new Date("<?php echo gmdate('Y-m-d\TH:i:s\Z'); ?>").getTime();
    var clientNow = new Date().getTime();
    // Calculate offset between server and client
    var offset = serverNow - clientNow;
    var notified = false;
    var timerInterval = null;
    function updateCountdown() {
        // Use server time as base, incremented by seconds elapsed
        var now = new Date().getTime() + offset;
        var countdownTimer = document.getElementById('countdown-timer');
        var countdownAlert = document.getElementById('countdown-alert');
        if (!countdownTimer || !countdownAlert) return;
        if (now < startTime) {
            countdownTimer.textContent = 'Waiting for reservation to start...';
            countdownAlert.classList.remove('alert-danger', 'alert-warning');
            countdownAlert.classList.add('alert-info');
            return;
        }
        var distance = endTime - now;
        if (distance <= 0) {
            countdownTimer.textContent = "Time is up!";
            countdownAlert.classList.remove('alert-info', 'alert-warning');
            countdownAlert.classList.add('alert-danger');
            clearInterval(timerInterval);
            return;
        }
        var hours = Math.floor((distance / (1000 * 60 * 60)));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);
        countdownTimer.textContent =
            (hours > 0 ? hours + 'h ' : '') + minutes + 'm ' + seconds + 's';
        // Notify at 10 minutes left
        if (!notified && distance <= 10 * 60 * 1000 && distance > 0) {
            notified = true;
            countdownAlert.classList.remove('alert-info');
            countdownAlert.classList.add('alert-warning');
            if (!document.getElementById('ten-min-warning')) {
                var warn = document.createElement('div');
                warn.className = 'mt-2 fw-bold text-danger';
                warn.id = 'ten-min-warning';
                warn.textContent = 'Only 10 minutes left in your reservation!';
                countdownAlert.appendChild(warn);
            }
        }
    }
    updateCountdown();
    timerInterval = setInterval(updateCountdown, 1000);
})();
</script>
<?php endif; ?>
<?php if (isset($_SESSION['login_success'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: 'Login Successful',
        text: 'Welcome back!',
        confirmButtonColor: '#3085d6'
    });
});
</script>
<?php unset($_SESSION['login_success']); endif; ?>
</body>
</html>