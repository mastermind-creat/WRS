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
    <link rel="stylesheet" href="/WRS/workstation-reservation-system/src/public/css/admin.css">
</head>
<body>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
<div class="container-fluid">
    <div class="row flex-nowrap">
        <div class="col-lg-3 p-0 sidebar">
            <?php include __DIR__ . '/../layout/sidebar.php'; ?>
        </div>
        <main class="col-lg-9 dashboard-main fade-in" style="margin-top: 10px;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="bi bi-bar-chart-line"></i> Reports</h2>
                <form class="d-flex gap-2 align-items-center" method="get">
                    <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>">
                    <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
                </form>
            </div>
            <div class="row g-4 mb-4">
                <div class="col-lg-6 slide-in">
                    <div class="card shadow card-equal">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-calendar-range"></i> Reservations Per Day</h5>
                            <button class="btn btn-outline-secondary btn-sm mb-2" onclick="exportTableToCSV('reservations-table', 'reservations_report.csv')"><i class="bi bi-download"></i> Export CSV</button>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle mb-0" id="reservations-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Total Reservations</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($reservationReport)): ?>
                                            <tr><td colspan="2" class="text-center">No reservations found for this period.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($reservationReport as $row): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['total_reservations']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 slide-in">
                    <div class="card shadow card-equal">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-person-lines-fill"></i> User Activity</h5>
                            <button class="btn btn-outline-secondary btn-sm mb-2" onclick="exportTableToCSV('user-activity-table', 'user_activity_report.csv')"><i class="bi bi-download"></i> Export CSV</button>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle mb-0" id="user-activity-table">
                                    <thead>
                                        <tr>
                                            <th>User ID</th>
                                            <th>Username</th>
                                            <th>Activity Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($userActivityReport)): ?>
                                            <tr><td colspan="3" class="text-center">No user activity found for this period.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($userActivityReport as $row): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['activity_count']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-4 mt-2">
                <div class="col-12 slide-in">
                    <div class="card shadow card-equal">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-pc-display"></i> Workstation Usage</h5>
                            <button class="btn btn-outline-secondary btn-sm mb-2" onclick="exportTableToCSV('workstation-usage-table', 'workstation_usage_report.csv')"><i class="bi bi-download"></i> Export CSV</button>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle mb-0" id="workstation-usage-table">
                                    <thead>
                                        <tr>
                                            <th>Workstation ID</th>
                                            <th>Workstation Name</th>
                                            <th>Usage Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($workstationUsageReport)): ?>
                                            <tr><td colspan="3" class="text-center">No workstation usage found for this period.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($workstationUsageReport as $row): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['workstation_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['workstation_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['usage_count']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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
<script>
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    let csv = [];
    for (let row of table.rows) {
        let rowData = [];
        for (let cell of row.cells) {
            // Escape quotes and commas
            let text = cell.innerText.replace(/"/g, '""');
            if (text.indexOf(',') !== -1 || text.indexOf('"') !== -1) {
                text = '"' + text + '"';
            }
            rowData.push(text);
        }
        csv.push(rowData.join(','));
    }
    const csvString = csv.join('\n');
    const blob = new Blob([csvString], { type: 'text/csv' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
</body>
</html>