<?php
session_start();
require_once '../../config/database.php';
require_once '../../controllers/ReportController.php';

$reportController = new ReportController($pdo);

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /workstation-reservation-system/src/views/auth/login.php");
    exit();
}

// Date range filter (default: last 7 days)
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-6 days', strtotime($endDate)));

$reservationReport = $reportController->generateReservationReport($startDate, $endDate);
$userActivityReport = $reportController->generateUserActivityReport($startDate, $endDate);
$workstationUsageReport = $reportController->generateWorkstationUsageReport($startDate, $endDate);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reports</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%); }
        .dashboard-main { background: #fff; border-radius: 1rem; box-shadow: 0 2px 16px rgba(79,140,255,0.08); padding: 2rem 2rem 1rem 2rem; min-height: 90vh; }
        @media (max-width: 991.98px) { .dashboard-main { padding: 1rem; } }
    </style>
</head>
<body>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-3 p-0">
            <?php include __DIR__ . '/../layout/sidebar.php'; ?>
        </div>
        <main class="col-lg-9 dashboard-main">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-bar-chart-line"></i> Reports</h2>
                <form class="d-flex gap-2" method="get">
                    <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>">
                    <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
                </form>
            </div>
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card shadow">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-calendar-range"></i> Reservations Per Day</h5>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Total Reservations</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reservationReport as $row): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['date']); ?></td>
                                                <td><?php echo htmlspecialchars($row['total_reservations']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card shadow">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-person-lines-fill"></i> User Activity</h5>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>User ID</th>
                                            <th>Activity Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($userActivityReport as $row): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['activity_count']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-pc-display"></i> Workstation Usage</h5>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Workstation ID</th>
                                            <th>Usage Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($workstationUsageReport as $row): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['workstation_id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['usage_count']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>