<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/Reservation.php';
$reservationModel = new Reservation($pdo);
$userId = $_SESSION['user_id'];
$reservations = $reservationModel->getReservationsByUser($userId);
$resCount = count($reservations);
$badges = [
    [
        'name' => 'First Reservation',
        'milestone' => 1,
        'icon' => 'bi-emoji-smile',
        'color' => 'success',
        'desc' => 'Made your very first reservation!'
    ],
    [
        'name' => '5 Reservations',
        'milestone' => 5,
        'icon' => 'bi-calendar-check',
        'color' => 'primary',
        'desc' => 'Reserved 5 workstations.'
    ],
    [
        'name' => '10 Reservations',
        'milestone' => 10,
        'icon' => 'bi-lightning-charge',
        'color' => 'warning',
        'desc' => '10 reservations made!'
    ],
    [
        'name' => '20 Reservations',
        'milestone' => 20,
        'icon' => 'bi-trophy',
        'color' => 'info',
        'desc' => '20 reservations milestone.'
    ],
    [
        'name' => '30 Reservations',
        'milestone' => 30,
        'icon' => 'bi-award',
        'color' => 'secondary',
        'desc' => '30 reservations completed.'
    ],
    [
        'name' => '40 Reservations',
        'milestone' => 40,
        'icon' => 'bi-gem',
        'color' => 'purple',
        'desc' => '40 reservations achieved.'
    ],
    [
        'name' => '50 Reservations',
        'milestone' => 50,
        'icon' => 'bi-star-fill',
        'color' => 'warning',
        'desc' => '50 reservations superstar!'
    ],
    [
        'name' => '75 Reservations',
        'milestone' => 75,
        'icon' => 'bi-fire',
        'color' => 'danger',
        'desc' => '75 reservations on fire!'
    ],
    [
        'name' => '100 Reservations',
        'milestone' => 100,
        'icon' => 'bi-rocket',
        'color' => 'success',
        'desc' => '100 reservations rocket!'
    ],
    [
        'name' => '200 Reservations',
        'milestone' => 200,
        'icon' => 'bi-cup',
        'color' => 'primary',
        'desc' => '200 reservations champion!'
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achievements - Workstation Reservation System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/WRS/workstation-reservation-system/src/public/css/user.css">
</head>
<body>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="mb-3"><i class="bi bi-star me-2"></i> Achievements & Badges</h2>
                    <p class="lead">Celebrate your milestones! Earn badges as you use the Workstation Reservation System.</p>
                    <hr>
                    <div class="row g-4 text-center">
                        <?php foreach ($badges as $badge):
                            $unlocked = $resCount >= $badge['milestone']; ?>
                            <div class="col-md-3 col-6">
                                <div class="p-3 border rounded bg-light h-100 <?php echo $unlocked ? 'badge-unlocked' : 'badge-locked'; ?>">
                                    <i class="bi <?php echo $badge['icon']; ?> display-4 text-<?php echo $unlocked ? $badge['color'] : 'secondary'; ?> mb-2"></i>
                                    <h6 class="mt-2 mb-1"><?php echo htmlspecialchars($badge['name']); ?></h6>
                                    <?php if ($unlocked): ?>
                                        <div class="small text-success"><i class="bi bi-unlock-fill"></i> <?php echo $badge['desc']; ?></div>
                                    <?php else: ?>
                                        <div class="small text-muted"><i class="bi bi-lock-fill"></i> Unlock at <?php echo $badge['milestone']; ?> reservations</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="alert alert-info mt-4">
                        <i class="bi bi-info-circle me-2"></i>
                        More badges coming soon as you continue to use the system!
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