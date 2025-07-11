<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: /WRS/workstation-reservation-system/src/views/auth/login.php');
    exit();
}
require_once '../../config/database.php';
require_once '../../models/Reservation.php';

$reservationModel = new Reservation($pdo);
$reservations = $reservationModel->getReservationsByUser($_SESSION['user_id']);
$message = '';

// Handle cancel reservation
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $reservationId = (int)$_GET['cancel'];
    $reservationModel->cancelReservation($reservationId);
    header('Location: my_reservations.php?msg=cancelled');
    exit();
}
if (isset($_GET['msg']) && $_GET['msg'] === 'cancelled') {
    $message = 'Reservation cancelled.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations - Workstation Reservation System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%); }
        .dashboard-main { background: #fff; border-radius: 1rem; box-shadow: 0 2px 16px rgba(79,140,255,0.08); padding: 2rem 2rem 1rem 2rem; max-width: 800px; margin: 2rem auto; }
        @media (max-width: 767.98px) { .dashboard-main { padding: 1rem; } }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layout/navbar.php'; ?>
    <div class="dashboard-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-list-check"></i> My Reservations</h2>
            <a href="/WRS/workstation-reservation-system/src/views/user/reserve.php" class="btn btn-success"><i class="bi bi-calendar-plus"></i> New Reservation</a>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-info text-center"> <?php echo $message; ?> </div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Workstation</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($reservations) === 0): ?>
                        <tr><td colspan="5" class="text-center text-muted">No reservations found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($reservations as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['workstation_id']); ?></td>
                                <td><?php echo date('M d, H:i', strtotime($r['start_time'])); ?></td>
                                <td><?php echo date('M d, H:i', strtotime($r['end_time'])); ?></td>
                                <td><span class="badge bg-<?php echo $r['status'] === 'approved' ? 'success' : ($r['status'] === 'pending' ? 'warning text-dark' : 'danger'); ?>"><?php echo ucfirst($r['status']); ?></span></td>
                                <td>
                                    <?php if (in_array($r['status'], ['pending', 'approved'])): ?>
                                        <a href="my_reservations.php?cancel=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this reservation?');"><i class="bi bi-x-circle"></i> Cancel</a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 