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
$now = date('Y-m-d H:i:s');
// Helper to classify reservation
function getReservationState($r, $now) {
    if ($r['end_time'] < $now) return 'past';
    if ($r['start_time'] > $now) return 'upcoming';
    if ($r['start_time'] <= $now && $r['end_time'] > $now) return 'active';
    return 'unknown';
}

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
    <link rel="stylesheet" href="/WRS/workstation-reservation-system/src/public/css/user.css">
</head>
<body>
    <?php include __DIR__ . '/../layout/navbar.php'; ?>
    <div class="dashboard-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary"><i class="bi bi-list-check"></i> My Reservations</h2>
            <a href="/WRS/workstation-reservation-system/src/views/user/reserve.php" class="btn btn-success rounded-pill shadow-sm"><i class="bi bi-calendar-plus"></i> New Reservation</a>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-info text-center rounded-pill shadow-sm"> <?php echo $message; ?> </div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle shadow-sm">
                <thead>
                    <tr>
                        <th><i class="bi bi-pc-display"></i> Workstation</th>
                        <th><i class="bi bi-calendar-event"></i> Start</th>
                        <th><i class="bi bi-calendar-check"></i> End</th>
                        <th><i class="bi bi-info-circle"></i> Status</th>
                        <th><i class="bi bi-gear"></i> Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($reservations) === 0): ?>
                        <tr><td colspan="5" class="text-center text-muted">No reservations found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($reservations as $r):
                            $state = getReservationState($r, $now);
                            $rowClass = $state === 'active' ? 'table-success' : ($state === 'upcoming' ? 'table-info' : ($state === 'past' ? 'table-light' : ''));
                        ?>
                        <tr class="<?php echo $rowClass; ?>">
                            <td><?php echo htmlspecialchars($r['workstation_id']); ?></td>
                            <td><?php echo date('M d, H:i', strtotime($r['start_time'])); ?></td>
                            <td><?php echo date('M d, H:i', strtotime($r['end_time'])); ?></td>
                            <td><span class="badge bg-<?php echo $r['status'] === 'approved' ? 'success' : ($r['status'] === 'pending' ? 'warning text-dark' : 'danger'); ?>"><?php echo ucfirst($r['status']); ?></span></td>
                            <td>
                                <?php if ($r['status'] === 'pending' && $state === 'upcoming'): ?>
                                    <a href="my_reservations.php?cancel=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this reservation?');" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel this upcoming pending reservation"><i class="bi bi-x-circle"></i> Cancel</a>
                                <?php elseif ($r['status'] === 'pending' && $state === 'active'): ?>
                                    <span class="text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="Cannot cancel: reservation is already active.">—</span>
                                <?php elseif ($r['status'] === 'pending' && $state === 'past'): ?>
                                    <span class="text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="Cannot cancel: reservation is in the past.">—</span>
                                <?php elseif ($r['status'] === 'approved'): ?>
                                    <span class="text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="Cannot cancel: reservation is already approved by admin.">—</span>
                                <?php elseif ($r['status'] === 'canceled'): ?>
                                    <span class="text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="Reservation already canceled.">—</span>
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    </script>
</body>
</html> 