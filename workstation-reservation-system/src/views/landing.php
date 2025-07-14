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
    <link rel="stylesheet" href="/WRS/workstation-reservation-system/src/public/css/style.css">
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
                    <div class="feature-icon"><i class="bi bi-calendar-check text-success"></i></div>
                    <div class="feature-title text-success">Easy Booking</div>
                    <div class="feature-desc">Reserve a workstation in seconds with our intuitive interface.</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 slide-in">
                <div class="feature-card text-center h-100">
                    <div class="feature-icon"><i class="bi bi-clock-history text-primary"></i></div>
                    <div class="feature-title text-primary">Real-Time Status</div>
                    <div class="feature-desc">See workstation availability and your reservations instantly.</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 slide-in">
                <div class="feature-card text-center h-100">
                    <div class="feature-icon"><i class="bi bi-shield-lock text-danger"></i></div>
                    <div class="feature-title text-danger">Secure Access</div>
                    <div class="feature-desc">Your data and bookings are protected with secure authentication.</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 slide-in">
                <div class="feature-card text-center h-100">
                    <div class="feature-icon"><i class="bi bi-people"></i></div>
                    <div class="feature-title text-warning">For Students & Staff</div>
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