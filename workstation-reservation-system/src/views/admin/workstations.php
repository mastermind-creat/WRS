<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /WRS/workstation-reservation-system/src/views/auth/login.php');
    exit();
}
require_once '../../config/database.php';
require_once '../../models/Workstation.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['workstation_id'], $_POST['status'])) {
    $id = $_POST['workstation_id'];
    $statuses = $_POST['status'];
    if (is_array($statuses)) {
        $status = implode(',', $statuses);
    } else {
        $status = $statuses;
    }
    $query = "UPDATE workstations SET status = :status WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    header('Location: workstations.php?msg=updated');
    exit();
}

$workstations = Workstation::getAllWorkstations($pdo);
$statusOptions = [
    'Available' => 'Available',
    'Reserved' => 'Reserved',
    'Maintenance' => 'Maintenance',
    'No Network' => 'No Network',
    'Not Working' => 'Not Working',
    'Busy' => 'Busy',
    'Idle' => 'Idle',
    'Cleaning' => 'Cleaning',
    'Software Update' => 'Software Update',
    'Hardware Issue' => 'Hardware Issue',
    'Power Issue' => 'Power Issue',
    'In Use' => 'In Use',
    'Offline' => 'Offline',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Workstations</title>
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
        <main class="col-lg-9 dashboard-main fade-in" style="margin-top: 5px;">
            <h2 class="mb-4"><i class="bi bi-pc-display"></i> Manage Workstations</h2>
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
                <div class="alert alert-success">Workstation status updated successfully.</div>
            <?php endif; ?>
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                    <th>Update Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($workstations as $ws): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ws['id']); ?></td>
                                    <td><?php echo htmlspecialchars($ws['name']); ?></td>
                                    <td>
                                        <?php
                                        $statusList = array_map('trim', explode(',', $ws['status']));
                                        foreach ($statusList as $status):
                                            $badgeClass = 'secondary';
                                            switch (strtolower($status)) {
                                                case 'available': $badgeClass = 'success'; break;
                                                case 'reserved': $badgeClass = 'primary'; break;
                                                case 'maintenance': $badgeClass = 'warning text-dark'; break;
                                                case 'no network': $badgeClass = 'info text-dark'; break;
                                                case 'not working': $badgeClass = 'danger'; break;
                                            }
                                        ?>
                                            <span class="badge bg-<?php echo $badgeClass; ?> me-1"><?php echo htmlspecialchars($status); ?></span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($ws['location'] ?? 'N/A'); ?></td>
                                    <td>
                                        <form method="post" class="d-flex align-items-center gap-2 status-form">
                                            <input type="hidden" name="workstation_id" value="<?php echo $ws['id']; ?>">
                                            <input type="hidden" name="status[]" class="status-hidden-input" value="<?php echo htmlspecialchars(implode(',', $statusList)); ?>">
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Set Status
                                                </button>
                                                <ul class="dropdown-menu p-2" style="min-width: 200px;">
                                                    <?php foreach ($statusOptions as $value => $label): ?>
                                                        <li>
                                                            <div class="form-check">
                                                                <input class="form-check-input status-checkbox" type="checkbox" value="<?php echo $value; ?>" id="status_<?php echo $ws['id'] . '_' . $value; ?>" <?php if (in_array($value, $statusList)) echo 'checked'; ?>>
                                                                <label class="form-check-label" for="status_<?php echo $ws['id'] . '_' . $value; ?>"><?php echo $label; ?></label>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                        </form>
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
<script>
document.querySelectorAll('.status-form').forEach(function(form) {
    var checkboxes = form.querySelectorAll('.status-checkbox');
    var hiddenInput = form.querySelector('.status-hidden-input');
    form.addEventListener('submit', function(e) {
        var selected = [];
        checkboxes.forEach(function(cb) {
            if (cb.checked) selected.push(cb.value);
        });
        // Remove all previous hidden inputs
        form.querySelectorAll('input[name="status[]"]').forEach(function(input) { input.remove(); });
        // Add new hidden inputs for each selected status
        selected.forEach(function(val) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'status[]';
            input.value = val;
            form.appendChild(input);
        });
    });
});
</script>
</body>
</html> 