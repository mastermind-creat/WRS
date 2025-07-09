<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/Reservation.php';
require_once '../../models/Workstation.php';
require_once '../../controllers/ReservationController.php';

$reservationModel = new Reservation($pdo);
$workstationModel = new Workstation(null, null, null, null); // Not used for static methods, but required by controller
$reservationController = new ReservationController($reservationModel, $workstationModel);

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /WRS/workstation-reservation-system/src/views/auth/login.php');
    exit();
}

$reservations = $reservationController->getAdminReservations();
$workstations = Workstation::getAllWorkstations($pdo);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Reservations</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
        }
        .dashboard-main {
            background: #f8f9fa;
            border-radius: 1rem;
            box-shadow: 0 2px 16px rgba(79,140,255,0.08);
            padding: 2rem 2rem 1rem 2rem;
            min-height: 100vh;
        }
        .sidebar {
            min-height: 100vh;
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
        .table-responsive { margin-top: 2rem; }
        .card-equal {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .card-equal .card-body {
            flex: 1 1 auto;
        }
        @media (max-width: 991.98px) {
            .dashboard-main { padding: 1rem; }
            .sidebar { min-height: auto; }
        }
        @media (max-width: 767.98px) {
            .dashboard-main { padding: 0.5rem; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <div class="col-lg-3 p-0 sidebar">
                <?php include __DIR__ . '/../layout/sidebar.php'; ?>
            </div>
            <main class="col-lg-9 dashboard-main fade-in">
                <div class="row mb-4">
                    <div class="col-12 text-center">
                        <h1 class="mb-2"><i class="bi bi-list-check"></i> Manage Reservations</h1>
                        <p class="lead">View, approve, or reject workstation reservations below.</p>
                    </div>
                </div>
                <?php if (isset($_GET['msg']) && $_GET['msg'] === 'approved'): ?>
                    <div class="alert alert-success">Reservation approved successfully.</div>
                <?php endif; ?>
                <div class="card shadow card-equal mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3"><i class="bi bi-clock-history"></i> All Reservations</h5>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Reservation ID</th>
                                        <th>User</th>
                                        <th>Workstation</th>
                                        <th>WS Status</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservations as $reservation): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                                            <td><?php echo htmlspecialchars($reservation['user_id']); ?></td>
                                            <td><?php echo htmlspecialchars($reservation['workstation_id']); ?></td>
                                            <td>
                                            <?php
                                                $wsStatus = '';
                                                foreach ($workstations as $ws) {
                                                    if ($ws['id'] == $reservation['workstation_id']) {
                                                        $wsStatus = $ws['status'];
                                                        break;
                                                    }
                                                }
                                            ?>
                                            <span class="badge bg-<?php echo $wsStatus === 'busy' ? 'danger' : 'secondary'; ?>"><?php echo ucfirst($wsStatus); ?></span>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($reservation['start_time'])); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($reservation['end_time'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $reservation['status'] === 'approved' ? 'success' : ($reservation['status'] === 'pending' ? 'warning text-dark' : 'danger'); ?>">
                                                    <?php echo ucfirst($reservation['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($reservation['status'] === 'pending'): ?>
                                                    <a href="approve.php?id=<?php echo $reservation['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                                                <?php endif; ?>
                                                <a href="cancel.php?id=<?php echo $reservation['id']; ?>" class="btn btn-danger btn-sm">Reject</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>