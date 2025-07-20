<?php
// Footer layout for the application
?>

<style>
.footer a.text-light.text-decoration-none {
    transition: color 0.2s, text-shadow 0.2s;
}
.footer a.text-light.text-decoration-none:hover {
    color: #ffd200 !important;
    text-shadow: 0 2px 8px rgba(255,210,0,0.15);
}
.footer .fs-4 a, .footer .d-flex.gap-3.fs-4 a {
    transition: color 0.2s, transform 0.3s cubic-bezier(.4,2,.6,1), box-shadow 0.2s;
    display: inline-block;
}
.footer .fs-4 a:hover, .footer .d-flex.gap-3.fs-4 a:hover {
    color: #fff !important;
    transform: scale(1.2) rotate(-8deg);
    box-shadow: 0 4px 16px rgba(79,140,255,0.18);
    background: linear-gradient(135deg, #4f8cff 0%, #6dd5ed 100%);
    border-radius: 50%;
}
.footer .badge {
    transition: background 0.2s, color 0.2s, transform 0.2s;
}
.footer .badge:hover {
    background: #ffd200 !important;
    color: #232526 !important;
    transform: scale(1.1);
}
</style>

<footer class="footer mt-auto py-4 bg-dark text-light">
    <div class="container">
        <div class="row">
            <!-- Subscription -->
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="mb-3">Subscribe to our Newsletter</h5>
                <form class="d-flex flex-column flex-sm-row gap-2" action="#" method="post" onsubmit="Swal.fire({icon: 'success', title: 'Subscribed!', text: 'Thank you for subscribing to our newsletter.'}); return false;">
                    <input type="email" name="subscribe_email" class="form-control" placeholder="Your email" required>
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </form>
                <small class="text-muted d-block mt-2">Get updates on lab schedules, new features, and more.</small>
            </div>
            <!-- Quick Links -->
            <div class="col-md-2 mb-4 mb-md-0">
                <h5 class="mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="/WRS/workstation-reservation-system/src/views/landing.php" class="text-light text-decoration-none">Home</a></li>
                    <li><a href="/WRS/workstation-reservation-system/src/views/auth/login.php" class="text-light text-decoration-none">Login</a></li>
                    <li><a href="/WRS/workstation-reservation-system/src/views/auth/register.php" class="text-light text-decoration-none">Register</a></li>
                    <li><a href="/WRS/workstation-reservation-system/src/views/user/about.php" class="text-light text-decoration-none">About</a></li>
                </ul>
            </div>
            <!-- Contact Info -->
            <div class="col-md-3 mb-4 mb-md-0">
                <h5 class="mb-3">Contact Us</h5>
                <ul class="list-unstyled">
                    <li><i class="bi bi-envelope-at me-2"></i> <a href="mailto:info@jitumelab.stvc.ac.ke" class="text-light text-decoration-none">info@jitumelab.stvc.ac.ke</a></li>
                    <li><i class="bi bi-telephone me-2"></i> <a href="tel:+254700000000" class="text-light text-decoration-none">+254 700 000 000</a></li>
                    <li><i class="bi bi-geo-alt me-2"></i> Seme Technical and Vocational College, Kenya</li>
                </ul>
            </div>
            <!-- Social Icons -->
            <div class="col-md-3">
                <h5 class="mb-3">Connect with Us</h5>
                <div class="d-flex gap-3 fs-4">
                    <a href="#" class="text-primary" title="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-info" title="Twitter"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="text-primary" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    <a href="#" class="text-danger" title="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-danger" title="YouTube"><i class="bi bi-youtube"></i></a>
                </div>
                <div class="mt-3">
                    <span class="badge bg-success">#JitumeLab</span>
                    <span class="badge bg-primary">#STVC</span>
                </div>
            </div>
        </div>
        <hr class="bg-secondary mt-4 mb-2">
        <div class="row">
            <div class="col text-center">
                <small>&copy; <?php echo date("Y"); ?> Seme Technical and Vocational College. All rights reserved. | Workstation Reservation System</small>
            </div>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/public/js/main.js"></script>
</body>
</html>