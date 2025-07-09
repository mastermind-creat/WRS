<?php
$current = $_SERVER['REQUEST_URI'];
function isActiveSidebar($path) {
    global $current;
    return strpos($current, $path) !== false ? 'active' : '';
}
?>
<aside class="d-flex flex-column flex-shrink-0 p-3 text-bg-dark sidebar position-fixed top-0 start-0" style="width: 240px; height: 100vh; background: linear-gradient(135deg, #232526 0%, #414345 100%); z-index: 1030;">
    <a href="/WRS/workstation-reservation-system/src/views/admin/dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <i class="mt-5 bi bi-speedometer2 me-2"></i>
        <span class="mt-5 fs-4">Admin Panel</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="/WRS/workstation-reservation-system/src/views/admin/dashboard.php" class="nav-link text-white <?php echo isActiveSidebar('dashboard.php'); ?>">
                <i class="bi bi-house-door me-2"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="/WRS/workstation-reservation-system/src/views/admin/reservations.php" class="nav-link text-white <?php echo isActiveSidebar('reservations.php'); ?>">
                <i class="bi bi-list-check me-2"></i> Reservations
            </a>
        </li>
        <li>
            <a href="/WRS/workstation-reservation-system/src/views/admin/reports.php" class="nav-link text-white <?php echo isActiveSidebar('reports.php'); ?>">
                <i class="bi bi-bar-chart-line me-2"></i> Reports
            </a>
        </li>
        <li>
            <a href="/WRS/workstation-reservation-system/src/views/admin/users.php" class="nav-link text-white <?php echo isActiveSidebar('users.php'); ?>">
                <i class="bi bi-people me-2"></i> Manage Users
            </a>
        </li>
        <li>
            <a href="/WRS/workstation-reservation-system/src/views/auth/logout.php" class="nav-link text-white">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </li>
    </ul>
    <hr>
    <div class="text-white-50 small">&copy; <?php echo date('Y'); ?> Jitume Lab</div>
</aside>
<style>
.sidebar .nav-link.active, .sidebar .nav-link:hover {
    background: linear-gradient(90deg, #4f8cff 0%, #6dd5ed 100%);
    color: #fff !important;
}
@media (max-width: 991.98px) {
    .sidebar {
        /* position: static !important; */
        width: 100% !important;
        /* height: auto !important; */
        min-height: auto;
    }
}
/* body {
    padding-left: 2px;
}
@media (max-width: 991.98px) {
    body {
        padding-left: 0;
    }
} */
</style> 