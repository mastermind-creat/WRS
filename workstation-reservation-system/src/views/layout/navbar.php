<?php
// Remove session_start() from here to avoid 'headers already sent' warnings
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }
$current = $_SERVER['REQUEST_URI'];
function isActive($path) {
    global $current;
    return strpos($current, $path) === 0 ? 'active' : '';
}
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$isLoggedIn = isset($_SESSION['user_id']);

// Fetch user info for avatar if logged in
$userAvatarUrl = null;
$userName = null;
if ($isLoggedIn) {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../models/User.php';
    $userModel = new User($pdo);
    $user = $userModel->getUserById($_SESSION['user_id']);
    if ($user) {
        $userName = $user['username'];
        if (!empty($user['avatar'])) {
            $userAvatarUrl = '/WRS/workstation-reservation-system/uploads/avatars/' . $user['avatar'];
        } else {
            $userAvatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=185a9d&color=fff&size=64';
        }
    }
}

if ($isLoggedIn && in_array($role, ['admin', 'super_admin'])) {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../models/Reservation.php';
    $reservationModel = new Reservation($pdo);
    $pendingCount = $reservationModel->countPendingReservations();
}
?>
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm  mb-4" style="background: rgba(33, 37, 41, 0.95) !important;">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/WRS/workstation-reservation-system/src/views/landing.php">
            <i class="bi bi-pc-display me-2"></i> Jitume Lab
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo isActive('/WRS/workstation-reservation-system/src/views/landing.php'); ?>" href="/WRS/workstation-reservation-system/src/views/landing.php">Home</a>
                </li>
                <?php if ($isLoggedIn && in_array($role, ['admin', 'super_admin'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/WRS/workstation-reservation-system/src/views/admin/dashboard.php'); ?>" href="/WRS/workstation-reservation-system/src/views/admin/dashboard.php">
                            <?php echo $role === 'super_admin' ? 'Super Admin' : 'Admin'; ?> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/WRS/workstation-reservation-system/src/views/admin/reservations.php'); ?>" href="/WRS/workstation-reservation-system/src/views/admin/reservations.php">
                            Reservations
                            <?php if (isset($pendingCount) && $pendingCount > 0): ?>
                                <span class="badge bg-danger ms-1" style="font-size:0.8em;vertical-align:top;animation: pulseBadge 1s infinite alternate;">
                                    <?php echo $pendingCount; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/WRS/workstation-reservation-system/src/views/admin/reports.php'); ?>" href="/WRS/workstation-reservation-system/src/views/admin/reports.php">Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/WRS/workstation-reservation-system/src/views/admin/users.php'); ?>" href="/WRS/workstation-reservation-system/src/views/admin/users.php">Manage Users</a>
                    </li>
                <?php elseif ($isLoggedIn && $role === 'user'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/WRS/workstation-reservation-system/src/views/user/dashboard.php'); ?>" href="/WRS/workstation-reservation-system/src/views/user/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/WRS/workstation-reservation-system/src/views/user/reserve.php'); ?>" href="/WRS/workstation-reservation-system/src/views/user/reserve.php">Reserve</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/WRS/workstation-reservation-system/src/views/user/my_reservations.php'); ?>" href="/WRS/workstation-reservation-system/src/views/user/my_reservations.php">My Reservations</a>
                    </li>
                <?php endif; ?>
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/WRS/workstation-reservation-system/src/views/auth/logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/WRS/workstation-reservation-system/src/views/auth/login.php'); ?>" href="/WRS/workstation-reservation-system/src/views/auth/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/WRS/workstation-reservation-system/src/views/auth/register.php'); ?>" href="/WRS/workstation-reservation-system/src/views/auth/register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
            <?php if ($isLoggedIn && $userAvatarUrl): ?>
                <div class="d-flex align-items-center ms-3">
                    <img src="<?php echo $userAvatarUrl; ?>" alt="Profile" class="rounded-circle avatar" style="width:38px;height:38px;object-fit:cover;box-shadow:0 2px 8px rgba(0,0,0,0.10);" title="<?php echo htmlspecialchars($userName); ?>">
                </div>
            <?php endif; ?>
            <!-- Theme toggler button -->
            <button class="btn btn-outline-light ms-3" id="navbar-theme-toggle" type="button" title="Toggle dark mode" style="border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;font-size:1.3em;">
                <i class="bi bi-moon-stars" id="navbar-theme-icon"></i>
            </button>
        </div>
    </div>
</nav>
<style>
@keyframes pulseBadge {
    from { box-shadow: 0 0 0 0 #dc3545; }
    to { box-shadow: 0 0 8px 2px #dc3545; }
}
</style>
<script>
(function() {
    var toggleBtn = document.getElementById('navbar-theme-toggle');
    var icon = document.getElementById('navbar-theme-icon');
    if (toggleBtn && icon) {
        var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        function setTheme(theme) {
            document.body.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            icon.className = theme === 'dark' ? 'bi bi-brightness-high' : 'bi bi-moon-stars';
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
})();
</script> 