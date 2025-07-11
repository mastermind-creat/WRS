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
    <style>
        :root {
            --main-bg: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
            --card-bg: #fff;
            --text-color: #222;
            --banner-bg: linear-gradient(135deg, #43cea2 0%, #185a9d 100%);
            --banner-text: #fff;
            --primary: #4f8cff;
            --success: #43cea2;
            --warning: #ffd200;
        }
        body[data-theme='dark'] {
            --main-bg: linear-gradient(135deg, #232526 0%, #414345 100%);
            --card-bg: #23272f;
            --text-color: #f1f1f1;
            --banner-bg: linear-gradient(135deg, #232526 0%, #414345 100%);
            --banner-text: #fff;
            --primary: #90caf9;
            --success: #43cea2;
            --warning: #ffd200;
        }
        body {
            min-height: 100vh;
            background: var(--main-bg);
            color: var(--text-color);
        }
        .dashboard-main {
            background: var(--card-bg);
            border-radius: 1rem;
            box-shadow: 0 2px 16px rgba(79,140,255,0.08);
            padding: 2rem 2rem 1rem 2rem;
            width: 100vw;
            max-width: 100vw;
            margin: 0;
            min-height: 100vh;
            transition: background 0.3s, color 0.3s;
        }
        .card-action {
            transition: transform 0.15s;
        }
        .card-action:hover {
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 4px 24px rgba(79,140,255,0.12);
        }
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 1s ease-out forwards;
        }
        .slide-in {
            opacity: 0;
            transform: translateX(-40px);
            animation: slideIn 1s 0.2s cubic-bezier(.4,2,.6,1) forwards;
        }
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: none;
            }
        }
        @keyframes slideIn {
            to {
                opacity: 1;
                transform: none;
            }
        }
        .welcome-banner {
            background: var(--banner-bg);
            color: var(--banner-text);
            border-radius: 1rem;
            box-shadow: 0 2px 16px rgba(67,233,123,0.08);
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: fadeInUp 1s;
        }
        .welcome-banner .date {
            font-size: 1.1em;
            font-weight: 500;
            opacity: 0.85;
        }
        .darkmode-toggle {
            background: var(--banner-bg);
            color: var(--banner-text);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3em;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.10);
            margin-left: 1rem;
            transition: background 0.3s, color 0.3s;
        }
        @media (max-width: 991.98px) {
            .dashboard-main { padding: 1rem; max-width: 100vw; }
            .welcome-banner { flex-direction: column; gap: 1rem; text-align: center; }
        }
        @media (max-width: 767.98px) {
            .dashboard-main { padding: 0.5rem; border-radius: 0; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<?php
require_once '../../config/database.php';
require_once '../../models/User.php';
require_once '../../models/Reservation.php';
$userModel = new User($pdo);
$reservationModel = new Reservation($pdo);
$user = $userModel->getUserById($_SESSION['user_id']);
$reservations = $reservationModel->getReservationsByUser($_SESSION['user_id']);
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
?>
    <div class="dashboard-main fade-in">
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
                <button class="darkmode-toggle ms-3" id="darkmode-toggle" title="Toggle dark mode"><i class="bi bi-moon-stars"></i></button>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center slide-in" style="background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%); color: #fff;" data-bs-toggle="tooltip" data-bs-placement="top" title="Your profile info">
                    <img src="<?php echo $avatarUrl; ?>" alt="Profile" class="rounded-circle me-3" style="width:64px;height:64px;box-shadow:0 2px 8px rgba(0,0,0,0.12);">
                    <div>
                        <h4 class="mb-0"><?php echo htmlspecialchars($user['username']); ?></h4>
                        <div class="small">Email: <?php echo htmlspecialchars($user['email']); ?></div>
                        <div class="small text-warning">User ID: <?php echo htmlspecialchars($user['id']); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4 mb-4">
            <div class="col-md-4 slide-in">
                <a href="/WRS/workstation-reservation-system/src/views/user/reserve.php" class="text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" title="Reserve a new workstation">
                    <div class="card card-action text-center border-0 shadow h-100" style="background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%); color: #fff;">
                        <div class="card-body">
                            <i class="bi bi-calendar-plus display-5"></i>
                            <h5 class="card-title mt-2">Reserve</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 slide-in">
                <a href="/WRS/workstation-reservation-system/src/views/user/my_reservations.php" class="text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" title="View your reservation history">
                    <div class="card card-action text-center border-0 shadow h-100" style="background: linear-gradient(135deg, #4f8cff 0%, #6dd5ed 100%); color: #fff;">
                        <div class="card-body">
                            <i class="bi bi-list-check display-5"></i>
                            <h5 class="card-title mt-2">My Reservations</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 slide-in">
                <a href="/WRS/workstation-reservation-system/src/views/user/profile.php" class="text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit your profile details">
                    <div class="card card-action text-center border-0 shadow h-100" style="background: linear-gradient(135deg, #185a9d 0%, #43cea2 100%); color: #fff;">
                        <div class="card-body">
                            <i class="bi bi-person-lines-fill display-5"></i>
                            <h5 class="card-title mt-2">Edit Profile</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 slide-in">
                <a href="/WRS/workstation-reservation-system/src/views/auth/logout.php" class="text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" title="Logout from your account">
                    <div class="card card-action text-center border-0 shadow h-100" style="background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); color: #333;">
                        <div class="card-body">
                            <i class="bi bi-box-arrow-right display-5"></i>
                            <h5 class="card-title mt-2">Logout</h5>
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
    <?php if ($activeReservation): ?>
        <script>
        (function() {
            // Set end time from PHP
            var endTime = new Date("<?php echo $activeReservation['end_time']; ?>").getTime();
            var notified = false;
            function updateCountdown() {
                var now = new Date().getTime();
                var distance = endTime - now;
                if (distance < 0) {
                    document.getElementById('countdown-timer').textContent = 'Time is up!';
                    document.getElementById('countdown-alert').classList.remove('alert-info');
                    document.getElementById('countdown-alert').classList.add('alert-danger');
                    return;
                }
                var hours = Math.floor((distance / (1000 * 60 * 60)));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                document.getElementById('countdown-timer').textContent =
                    (hours > 0 ? hours + 'h ' : '') + minutes + 'm ' + seconds + 's';
                // Notify at 10 minutes left
                if (!notified && distance <= 10 * 60 * 1000 && distance > 0) {
                    notified = true;
                    var alert = document.getElementById('countdown-alert');
                    alert.classList.remove('alert-info');
                    alert.classList.add('alert-warning');
                    alert.innerHTML += '<div class="mt-2 fw-bold text-danger">Only 10 minutes left in your reservation!</div>';
                }
            }
            updateCountdown();
            setInterval(updateCountdown, 1000);
        })();
        </script>
    <?php endif; ?>
</body>
</html>