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
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
        }
        .dashboard-main {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 16px rgba(79,140,255,0.08);
            padding: 2rem 2rem 1rem 2rem;
            max-width: 700px;
            margin: 2rem auto;
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
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: none;
            }
        }
        @media (max-width: 767.98px) {
            .dashboard-main { padding: 1rem; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
    <div class="dashboard-main fade-in">
        <div class="text-center mb-4">
            <i class="bi bi-person-circle" style="font-size: 3rem; color: #4f8cff;"></i>
            <h2 class="mt-2">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <p class="lead">Access and manage your workstation reservations easily.</p>
        </div>
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <a href="/WRS/workstation-reservation-system/src/views/user/reserve.php" class="text-decoration-none">
                    <div class="card card-action text-center border-0 shadow h-100" style="background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%); color: #fff;">
                        <div class="card-body">
                            <i class="bi bi-calendar-plus display-5"></i>
                            <h5 class="card-title mt-2">Reserve</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="/WRS/workstation-reservation-system/src/views/user/my_reservations.php" class="text-decoration-none">
                    <div class="card card-action text-center border-0 shadow h-100" style="background: linear-gradient(135deg, #4f8cff 0%, #6dd5ed 100%); color: #fff;">
                        <div class="card-body">
                            <i class="bi bi-list-check display-5"></i>
                            <h5 class="card-title mt-2">My Reservations</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="/WRS/workstation-reservation-system/src/views/auth/logout.php" class="text-decoration-none">
                    <div class="card card-action text-center border-0 shadow h-100" style="background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); color: #333;">
                        <div class="card-body">
                            <i class="bi bi-box-arrow-right display-5"></i>
                            <h5 class="card-title mt-2">Logout</h5>
                        </div>
                    </div>
                </a>
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
</body>
</html>