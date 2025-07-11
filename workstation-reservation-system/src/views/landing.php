<?php
session_start();
?>
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
        .slide-in {
            opacity: 0;
            transform: translateX(-40px);
            animation: slideIn 1.2s 0.2s cubic-bezier(.4,2,.6,1) forwards;
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
        .hero {
            padding: 80px 0 40px 0;
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .glass-card {
            background: rgba(255,255,255,0.18);
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.25);
            padding: 2.5rem 2.5rem 2rem 2.5rem;
            max-width: 540px;
            margin: 0 auto;
        }
        .hero-logo {
            font-size: 3.5rem;
            color: #4f8cff;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 8px rgba(79,140,255,0.10);
        }
        .hero-title {
            font-size: 2.3rem;
            font-weight: 700;
            color: #fff;
            text-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .hero-desc {
            font-size: 1.15rem;
            color: #e3e3e3;
            margin-bottom: 2rem;
        }
        .cta-btn {
            font-size: 1.1rem;
            padding: 0.75rem 2.5rem;
        }
        .features-row {
            margin-top: 2.5rem;
            margin-bottom: 2.5rem;
        }
        .feature-card {
            background: rgba(255,255,255,0.18);
            border-radius: 1.2rem;
            box-shadow: 0 2px 16px rgba(79,140,255,0.10);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border: 1px solid rgba(255,255,255,0.18);
            padding: 1.5rem 1rem;
            color: #fff;
            min-height: 180px;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .feature-card:hover {
            box-shadow: 0 6px 32px rgba(79,140,255,0.18);
            transform: translateY(-2px) scale(1.03);
        }
        .feature-icon {
            font-size: 2.2rem;
            margin-bottom: 0.7rem;
            color: #ffd200;
            text-shadow: 0 2px 8px rgba(0,0,0,0.10);
        }
        .feature-title {
            font-size: 1.15rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .feature-desc {
            font-size: 1rem;
            color: #e3e3e3;
        }
        @media (max-width: 767.98px) {
            .hero-title { font-size: 1.5rem; }
            .glass-card { padding: 1.2rem 0.7rem; }
            .features-row { margin-top: 1.2rem; margin-bottom: 1.2rem; }
            .feature-card { min-height: 140px; padding: 1rem 0.5rem; }
        }
        footer {
            background: none;
            color: #e3e3e3;
            font-size: 1em;
            letter-spacing: 0.02em;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/layout/navbar.php'; ?>
    <!-- Hero Section -->
    <section class="hero fade-in">
        <div class="glass-card text-center">
            <div class="hero-logo mb-2"><i class="bi bi-pc-display"></i></div>
            <h1 class="hero-title mb-3">Welcome to the Jitume Lab<br>Workstation Reservation System</h1>
            <p class="hero-desc mb-4">Reserve your spot, manage your bookings, and access the lab efficiently.<br>For students and staff at Seme Technical and Vocational College (STVC).</p>
            <a href="/WRS/workstation-reservation-system/src/views/auth/register.php" class="btn btn-success cta-btn shadow-lg me-2">Get Started</a>
            <a href="/WRS/workstation-reservation-system/src/views/auth/login.php" class="btn btn-outline-light cta-btn">Login</a>
        </div>
    </section>
    <!-- Features Section -->
    <section class="container features-row">
        <div class="row justify-content-center g-4">
            <div class="col-12 col-sm-6 col-md-3 slide-in">
                <div class="feature-card text-center h-100">
                    <div class="feature-icon"><i class="bi bi-calendar-check"></i></div>
                    <div class="feature-title">Easy Booking</div>
                    <div class="feature-desc">Reserve a workstation in seconds with our intuitive interface.</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 slide-in">
                <div class="feature-card text-center h-100">
                    <div class="feature-icon"><i class="bi bi-clock-history"></i></div>
                    <div class="feature-title">Real-Time Status</div>
                    <div class="feature-desc">See workstation availability and your reservations instantly.</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 slide-in">
                <div class="feature-card text-center h-100">
                    <div class="feature-icon"><i class="bi bi-shield-lock"></i></div>
                    <div class="feature-title">Secure Access</div>
                    <div class="feature-desc">Your data and bookings are protected with secure authentication.</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 slide-in">
                <div class="feature-card text-center h-100">
                    <div class="feature-icon"><i class="bi bi-people"></i></div>
                    <div class="feature-title">For Students & Staff</div>
                    <div class="feature-desc">Designed for the STVC community to maximize lab efficiency.</div>
                </div>
            </div>
        </div>
    </section>
    <!-- Contact & Branding Section -->
    <section class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8">
                <div class="glass-card text-center py-4 px-3 mt-4" style="backdrop-filter: blur(7px);">
                    <div class="mb-3">
                        <span class="display-4" style="color:#4f8cff;"><i class="bi bi-pc-display"></i></span>
                    </div>
                    <h3 class="fw-bold mb-2" style="color:#185a9d;">Jitume Lab @ STVC</h3>
                    <p class="mb-3" style="color:#333;font-size:1.1em;">Empowering students and staff with seamless, secure, and smart access to digital resources. Jitume Lab is your gateway to efficient workstation management at Seme Technical and Vocational College.</p>
                    <div class="mb-3">
                        <span class="fw-semibold"><i class="bi bi-envelope-at me-1"></i> Email:</span> <a href="mailto:info@jitumelab.stvc.ac.ke" class="text-decoration-none text-primary">info@jitumelab.stvc.ac.ke</a><br>
                        <span class="fw-semibold"><i class="bi bi-telephone me-1"></i> Phone:</span> <a href="tel:+254700000000" class="text-decoration-none text-primary">+254 700 000 000</a>
                    </div>
                    <div class="d-flex justify-content-center gap-3 mt-3">
                        <a href="#" class="text-primary fs-3" title="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-info fs-3" title="Twitter"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-primary fs-3" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-danger fs-3" title="Instagram"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <footer class="text-center text-white-50 mt-5 mb-3">
        &copy; <?php echo date('Y'); ?> Jitume Lab – STVC. All rights reserved.
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 