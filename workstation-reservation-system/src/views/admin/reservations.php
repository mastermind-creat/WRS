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

// Date filter logic
$dateFilter = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';
if ($dateFilter) {
    $reservations = array_filter($reservations, function($r) use ($dateFilter) {
        return strpos($r['start_time'], $dateFilter) === 0;
    });
}
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
        .table-responsive { 
            margin-top: 2rem;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        .card-equal {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .card-equal .card-body {
            flex: 1 1 auto;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        .table td {
            vertical-align: middle;
        }
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
        }
        .action-buttons .btn {
            margin-right: 0.3rem;
            margin-bottom: 0.3rem;
        }
        .status-badge {
            min-width: 80px;
            display: inline-block;
            text-align: center;
        }
        .ws-status-badge {
            min-width: 70px;
            display: inline-block;
            text-align: center;
        }
        @media (max-width: 991.98px) {
            .dashboard-main { padding: 1rem; }
            .sidebar { min-height: auto; }
            .action-buttons .btn {
                display: block;
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
        @media (max-width: 767.98px) {
            .dashboard-main { padding: 0.5rem; }
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
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
            <main class="col-lg-9 mt-4 dashboard-main fade-in">
                <div class="row mb-4">
                    <div class="col-12 text-center">
                        <h1 class="mb-2"><i class="bi bi-list-check"></i> Manage Reservations</h1>
                        <p class="lead">View, approve, or restore workstation reservations below.</p>
                        <form method="get" class="d-inline-block mt-2 mb-0">
                            <label for="filter_date" class="me-2">Filter by Date:</label>
                            <input type="date" id="filter_date" name="filter_date" value="<?php echo htmlspecialchars($dateFilter); ?>">
                            <button type="submit" class="btn btn-primary btn-sm ms-2">Filter</button>
                            <a href="reservations.php" class="btn btn-secondary btn-sm ms-2">Reset</a>
                        </form>
                    </div>
                </div>
                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-<?php 
                        echo $_GET['msg'] === 'approved' ? 'success' : 
                             ($_GET['msg'] === 'rejected' ? 'danger' : 'info'); 
                    ?> alert-dismissible fade show">
                        <?php 
                        echo $_GET['msg'] === 'approved' ? 'Reservation approved successfully.' : 
                             ($_GET['msg'] === 'rejected' ? 'Reservation rejected successfully.' : 
                             'Reservation restored successfully.');
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <div class="card shadow card-equal mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3"><i class="bi bi-clock-history"></i> All Reservations</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
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
                                    <?php if (empty($reservations)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">No reservations found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($reservations as $reservation): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-person-circle me-2"></i>
                                                        <?php echo htmlspecialchars($reservation['user_name'] ?? 'User #' . $reservation['user_id']); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-pc-display-horizontal me-2"></i>
                                                        <?php echo htmlspecialchars($reservation['workstation_name'] ?? 'WS #' . $reservation['workstation_id']); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                        $wsStatus = '';
                                                        $wsStatusClass = 'secondary';
                                                        foreach ($workstations as $ws) {
                                                            if ($ws['id'] == $reservation['workstation_id']) {
                                                                $wsStatus = $ws['status'];
                                                                switch(strtolower($wsStatus)) {
                                                                    case 'available': $wsStatusClass = 'success'; break;
                                                                    case 'busy': $wsStatusClass = 'danger'; break;
                                                                    case 'maintenance': $wsStatusClass = 'warning text-dark'; break;
                                                                    case 'reserved': $wsStatusClass = 'primary'; break;
                                                                    default: $wsStatusClass = 'secondary';
                                                                }
                                                                break;
                                                            }
                                                        }
                                                    ?>
                                                    <span class="badge ws-status-badge bg-<?php echo $wsStatusClass; ?>">
                                                        <i class="bi <?php 
                                                            echo $wsStatus === 'available' ? 'bi-check-circle' : 
                                                                 ($wsStatus === 'busy' ? 'bi-person-fill' : 
                                                                 ($wsStatus === 'maintenance' ? 'bi-tools' : 
                                                                 ($wsStatus === 'reserved' ? 'bi-calendar-check' : 'bi-question-circle'))); 
                                                        ?> me-1"></i>
                                                        <?php echo ucfirst($wsStatus); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-calendar-event me-2"></i>
                                                        <?php echo date('M d, Y H:i', strtotime($reservation['start_time'])); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-calendar-x me-2"></i>
                                                        <?php echo date('M d, Y H:i', strtotime($reservation['end_time'])); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge status-badge bg-<?php 
                                                        echo $reservation['status'] === 'approved' ? 'success' : 
                                                             ($reservation['status'] === 'pending' ? 'warning text-dark' : 'danger'); 
                                                    ?>">
                                                        <i class="bi <?php 
                                                            echo $reservation['status'] === 'approved' ? 'bi-check-circle' : 
                                                                 ($reservation['status'] === 'pending' ? 'bi-hourglass' : 'bi-x-circle'); 
                                                        ?> me-1"></i>
                                                        <?php echo ucfirst($reservation['status'] === 'canceled' ? 'Canceled' : $reservation['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="action-buttons">
                                                    <?php if ($reservation['status'] === 'pending'): ?>
                                                        <a href="approve.php?id=<?php echo $reservation['id']; ?>" 
                                                           class="btn btn-success btn-sm" 
                                                           title="Approve this reservation">
                                                            <i class="bi bi-check-lg"></i> Approve
                                                        </a>
                                                        <a href="reject.php?id=<?php echo $reservation['id']; ?>" 
                                                           class="btn btn-danger btn-sm" 
                                                           title="Reject this reservation">
                                                            <i class="bi bi-x-lg"></i> Reject
                                                        </a>
                                                    <?php elseif ($reservation['status'] === 'canceled'): ?>
                                                        <a href="undo_cancel.php?id=<?php echo $reservation['id']; ?>" 
                                                           class="btn btn-warning btn-sm" 
                                                           title="Restore this reservation">
                                                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">No actions available</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>