<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jitume Lab – Workstation Reservation System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, rgba(79,140,255,0.85) 0%, rgba(109,213,237,0.85) 100%), url('../public/images/lab.webp') center center/cover no-repeat fixed;
        }
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 1.2s ease-out forwards;
        }
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: none;
            }
        }
        .hero {
            padding: 80px 0 60px 0;
        }
        .hero-title {
            font-size: 2.8rem;
            font-weight: 700;
            color: #fff;
            text-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .hero-desc {
            font-size: 1.3rem;
            color: #e3e3e3;
            margin-bottom: 2rem;
        }
        .cta-btn {
            font-size: 1.1rem;
            padding: 0.75rem 2.5rem;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/layout/navbar.php'; ?>
    <!-- Hero Section -->
    <section class="hero d-flex align-items-center justify-content-center fade-in">
        <div class="container text-center">
            <h1 class="hero-title mb-3">Welcome to the Jitume Lab<br>Workstation Reservation System</h1>
            <p class="hero-desc mb-4">Reserve your spot, manage your bookings, and access the lab efficiently.<br>For students and staff at Seme Technical and Vocational College (STVC).</p>
            <a href="/WRS/workstation-reservation-system/src/views/auth/register.php" class="btn btn-success cta-btn shadow-lg me-2">Get Started</a>
            <a href="/WRS/workstation-reservation-system/src/views/auth/login.php" class="btn btn-outline-light cta-btn">Login</a>
        </div>
    </section>
    <footer class="text-center text-white-50 mt-5 mb-3">
        &copy; <?php echo date('Y'); ?> Jitume Lab – STVC. All rights reserved.
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 