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
    <style>
        body {
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
        }
        .dashboard-main {
            background: var(--card-bg, #fff);
        }
        .card-equal {
            border-radius: 1.1rem !important;
            box-shadow: 0 2px 16px rgba(79,140,255,0.10);
        }
        .card-header {
            border-radius: 1.1rem 1.1rem 0 0 !important;
            font-weight: 600;
            font-size: 1.1em;
            letter-spacing: 0.01em;
        }
        .card-header.bg-primary, .card-header.bg-success, .card-header.bg-warning {
            color: #fff;
        }
        .table thead th {
            background: #f5faff;
            color: #185a9d;
            border-bottom: 2px solid #e0eafc;
        }
        .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: #f8fbff;
        }
        .table-hover > tbody > tr:hover {
            background-color: #e0eafc;
        }
        .highlight-badge {
            font-size: 1em;
            font-weight: 600;
            border-radius: 1em;
            padding: 0.3em 0.8em;
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
                        <div class="card-header bg-primary"><i class="bi bi-calendar-range me-1"></i> Reservations Per Day</div>
                        <div class="card-body">
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
                                                    <td><span class="badge bg-success highlight-badge"><?php echo htmlspecialchars($row['total_reservations']); ?></span></td>
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
                        <div class="card-header bg-success"><i class="bi bi-person-lines-fill me-1"></i> User Activity</div>
                        <div class="card-body">
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
                                                    <td><span class="badge bg-primary highlight-badge"><?php echo htmlspecialchars($row['activity_count']); ?></span></td>
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
                        <div class="card-header bg-warning"><i class="bi bi-pc-display me-1"></i> Workstation Usage</div>
                        <div class="card-body">
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
                                                    <td><span class="badge bg-warning text-dark highlight-badge"><?php echo htmlspecialchars($row['usage_count']); ?></span></td>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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

// Chart.js dark mode support
function updateChartsForTheme() {
    if (window.Chart && Chart.instances) {
        Object.values(Chart.instances).forEach(function(chart) {
            if (!chart) return;
            var isDark = document.body.getAttribute('data-theme') === 'dark';
            if (chart.options.scales) {
                if (chart.options.scales.x) {
                    chart.options.scales.x.grid = chart.options.scales.x.grid || {};
                    chart.options.scales.x.ticks = chart.options.scales.x.ticks || {};
                    chart.options.scales.x.grid.color = isDark ? '#444' : '#e0eafc';
                    chart.options.scales.x.ticks.color = isDark ? '#fff' : '#222';
                }
                if (chart.options.scales.y) {
                    chart.options.scales.y.grid = chart.options.scales.y.grid || {};
                    chart.options.scales.y.ticks = chart.options.scales.y.ticks || {};
                    chart.options.scales.y.grid.color = isDark ? '#444' : '#e0eafc';
                    chart.options.scales.y.ticks.color = isDark ? '#fff' : '#222';
                }
            }
            if (chart.options.plugins && chart.options.plugins.legend && chart.options.plugins.legend.labels) {
                chart.options.plugins.legend.labels.color = isDark ? '#fff' : '#222';
            }
            if (chart.options.plugins && chart.options.plugins.title) {
                chart.options.plugins.title.color = isDark ? '#fff' : '#222';
            }
            chart.update('none');
        });
    }
}
// Listen for theme changes
const observer = new MutationObserver(updateChartsForTheme);
observer.observe(document.body, { attributes: true, attributeFilter: ['data-theme'] });
window.addEventListener('DOMContentLoaded', updateChartsForTheme);
</script>
</body>
</html>