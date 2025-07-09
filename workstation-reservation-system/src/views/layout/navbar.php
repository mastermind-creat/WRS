<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current = $_SERVER['REQUEST_URI'];
function isActive($path) {
    global $current;
    return strpos($current, $path) === 0 ? 'active' : '';
}
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$isLoggedIn = isset($_SESSION['user_id']);
?>
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background: rgba(33, 37, 41, 0.95) !important;">
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
                <?php if ($isLoggedIn && $role === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/WRS/workstation-reservation-system/src/views/admin/dashboard.php'); ?>" href="/WRS/workstation-reservation-system/src/views/admin/dashboard.php">Admin Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/WRS/workstation-reservation-system/src/views/admin/reservations.php'); ?>" href="/WRS/workstation-reservation-system/src/views/admin/reservations.php">Reservations</a>
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
        </div>
    </div>
</nav> 