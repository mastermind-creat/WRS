<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Workstation Reservation System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/WRS/workstation-reservation-system/src/public/css/admin.css">
</head>
<body>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="mb-3"><i class="bi bi-info-circle me-2"></i> About the Workstation Reservation System</h2>
                    <p class="lead">The Workstation Reservation System (WRS) is a modern web application designed to help users and administrators efficiently manage and reserve computer workstations in a lab or shared environment.</p>
                    <hr>
                    <h5 class="mt-4 mb-2">Key Features:</h5>
                    <ul>
                        <li>Easy online reservation of workstations for users</li>
                        <li>Admin dashboard for managing users, workstations, and reservations</li>
                        <li>Real-time status and analytics of workstation usage</li>
                        <li>Profile management and reservation history for users</li>
                        <li>Responsive, mobile-friendly design with dark mode support</li>
                        <li>Secure authentication and role-based access</li>
                    </ul>
                    <h5 class="mt-4 mb-2">How It Works:</h5>
                    <ol>
                        <li>Users log in and reserve available workstations for specific time slots.</li>
                        <li>Admins approve, reject, or manage reservations and workstation statuses.</li>
                        <li>Users can view their reservation history and manage their profiles.</li>
                    </ol>
                    <div class="alert alert-info mt-4">
                        <i class="bi bi-lightbulb me-2"></i>
                        For support or feedback, contact your lab administrator.
                    </div>
                    <a href="/WRS/workstation-reservation-system/src/views/user/dashboard.php" class="btn btn-primary mt-3"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 