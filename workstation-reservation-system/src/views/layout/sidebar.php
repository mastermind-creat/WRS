<?php
$current = $_SERVER['REQUEST_URI'];
function isActiveSidebar($path) {
    global $current;
    return strpos($current, $path) !== false ? 'active' : '';
}
?>
<!-- Sidebar Offcanvas for small screens -->
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="adminSidebarOffcanvas" aria-labelledby="adminSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="adminSidebarLabel">Admin Panel</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <?php /* Sidebar content below is duplicated for offcanvas */ ?>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="/WRS/workstation-reservation-system/src/views/admin/dashboard.php" class="nav-link text-dark <?php echo isActiveSidebar('dashboard.php'); ?>">
                    <i class="bi bi-house-door me-2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="/WRS/workstation-reservation-system/src/views/admin/reservations.php" class="nav-link text-dark <?php echo isActiveSidebar('reservations.php'); ?>">
                    <i class="bi bi-list-check me-2"></i> Reservations
                </a>
            </li>
            <li>
                <a href="/WRS/workstation-reservation-system/src/views/admin/reports.php" class="nav-link text-dark <?php echo isActiveSidebar('reports.php'); ?>">
                    <i class="bi bi-bar-chart-line me-2"></i> Reports
                </a>
            </li>
            <li>
                <a href="/WRS/workstation-reservation-system/src/views/admin/users.php" class="nav-link text-dark <?php echo isActiveSidebar('users.php'); ?>">
                    <i class="bi bi-people me-2"></i> Manage Users
                </a>
            </li>
            <li>
                <a href="/WRS/workstation-reservation-system/src/views/admin/workstations.php" class="nav-link text-dark <?php echo isActiveSidebar('workstations.php'); ?>">
                    <i class="bi bi-pc-display me-2"></i> Manage Workstations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="/WRS/workstation-reservation-system/src/views/admin/profile.php">
                    <i class="bi bi-person-circle me-2"></i> Edit Profile
                </a>
            </li>
            <li>
                <a href="/WRS/workstation-reservation-system/src/views/auth/logout.php" class="nav-link text-dark">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </li>
        </ul>
        <hr>
        <div class="text-secondary small">&copy; <?php echo date('Y'); ?> Jitume Lab</div>
    </div>
</div>

<!-- Desktop Sidebar -->
<aside class="d-none d-lg-flex flex-column flex-shrink-0 p-3 text-bg-dark sidebar position-fixed top-0 start-0" style="width: 240px; height: 100vh; background: linear-gradient(135deg, #232526 0%, #414345 100%); z-index: 1030;">
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
            <a href="/WRS/workstation-reservation-system/src/views/admin/workstations.php" class="nav-link text-white <?php echo isActiveSidebar('workstations.php'); ?>">
                <i class="bi bi-pc-display me-2"></i> Manage Workstations
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/WRS/workstation-reservation-system/src/views/admin/profile.php">
                <i class="bi bi-person-circle me-2"></i> Edit Profile
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

<!-- Sidebar Toggle Button (visible on small screens) -->
<button class="btn btn-primary d-lg-none position-fixed" style="top: 1rem; left: 1rem; z-index: 2000;" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebarOffcanvas" aria-controls="adminSidebarOffcanvas">
    <i class="bi bi-list"></i>
</button>

<style>
.sidebar .nav-link.active, .sidebar .nav-link:hover {
    background: linear-gradient(90deg, #4f8cff 0%, #6dd5ed 100%);
    color: #fff !important;
}

/* Main content adjustments */
.dashboard-main {
    margin-left: -240px; /* Match sidebar width */
    padding: 20px;
    width: calc(100% - 240px);
}

/* Remove default body padding */
body {
    padding-left: 0 !important;
    margin-left: 0 !important;
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    .sidebar {
        width: 100% !important;
        position: relative !important;
        height: auto !important;
    }
    
    .dashboard-main {
        margin-left: 0;
        width: 100%;
    }
}

/* Optional: If you want to remove the slight gap at the top */
.sidebar {
    top: 0 !important;
    padding-top: 0 !important;
}

/* Remove any default margins from the main container */
.container-fluid {
    padding-left: 0;
    margin-left: 0;
}

/* Ensure the row has no margin */
.row.flex-nowrap {
    margin-left: 0;
    margin-right: 0;
}
</style>