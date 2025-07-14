<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/Workstation.php';
require_once '../../models/Reservation.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /WRS/workstation-reservation-system/src/views/auth/login.php");
    exit();
}

// Fetch all workstations
$workstations = Workstation::getAllWorkstations($pdo);
$reservationModel = new Reservation($pdo);
$message = '';
$hasActiveReservation = $reservationModel->userHasActiveReservation($_SESSION['user_id']);

// Dynamically assign workstation IDs to layout positions
$workstationIds = array_column($workstations, 'id');
$layout = [
    'left' => [],
    'right' => [],
    'bottom' => []
];
$maxLeft = 48; // 6 rows x 8
$maxRight = 14; // 3 rows x 4 + 2 rows x 1 (if needed)
$maxBottom = 8;

for ($i = 0, $n = count($workstationIds); $i < $n; $i++) {
    if (count($layout['left']) < $maxLeft) {
        $layout['left'][] = $workstationIds[$i];
    } elseif (count($layout['right']) < $maxRight) {
        $layout['right'][] = $workstationIds[$i];
    } elseif (count($layout['bottom']) < $maxBottom) {
        $layout['bottom'][] = $workstationIds[$i];
    }
}
$workstationMap = [];
foreach ($workstations as $ws) {
    $workstationMap[$ws['id']] = $ws;
}
$renderDesk = function($ids, $class) use ($workstationMap) {
    static $popoverScripts = [];
    echo '<div class="desk ' . $class . '">';
    foreach ($ids as $id) {
        $ws = isset($workstationMap[$id]) ? $workstationMap[$id] : null;
        $name = $ws ? htmlspecialchars($ws['name']) : 'N/A';
        $statusRaw = $ws && !empty($ws['status']) ? $ws['status'] : 'Available';
        $statusList = array_map('trim', explode(',', $statusRaw));
        $popoverContent = '<div style="min-width:160px;"><strong>' . $name . '</strong><br/><div class="d-flex flex-wrap gap-2 mt-2">';
        foreach ($statusList as $status) {
            $statusEsc = htmlspecialchars($status);
            $iconClass = 'bi-question-circle'; // default icon
            $badgeClass = 'bg-secondary';
            
            switch (strtolower($statusEsc)) {
                case 'available':
                    $iconClass = 'bi-check-circle';
                    $badgeClass = 'bg-success';
                    break;
                case 'reserved':
                    $iconClass = 'bi-calendar-check';
                    $badgeClass = 'bg-primary';
                    break;
                case 'maintenance':
                    $iconClass = 'bi-tools';
                    $badgeClass = 'bg-warning text-dark';
                    break;
                case 'no network':
                    $iconClass = 'bi-wifi-off';
                    $badgeClass = 'bg-danger';
                    break;
                case 'not working':
                    $iconClass = 'bi-exclamation-triangle';
                    $badgeClass = 'bg-danger';
                    break;
                case 'busy':
                    $iconClass = 'bi-person-fill';
                    $badgeClass = 'bg-secondary';
                    break;
                case 'idle':
                    $iconClass = 'bi-hourglass';
                    $badgeClass = 'bg-light text-dark';
                    break;
                case 'cleaning':
                    $iconClass = 'bi-bucket';
                    $badgeClass = 'bg-info text-dark';
                    break;
                case 'software update':
                    $iconClass = 'bi-download';
                    $badgeClass = 'bg-primary';
                    break;
                case 'hardware issue':
                    $iconClass = 'bi-cpu';
                    $badgeClass = 'bg-danger';
                    break;
                case 'power issue':
                    $iconClass = 'bi-plug';
                    $badgeClass = 'bg-warning text-dark';
                    break;
                case 'in use':
                    $iconClass = 'bi-person-check';
                    $badgeClass = 'bg-success';
                    break;
                case 'offline':
                    $iconClass = 'bi-power';
                    $badgeClass = 'bg-secondary';
                    break;
            }
            // Display icon with text below
            $popoverContent .= '<div class="d-flex flex-column align-items-center">';
            $popoverContent .= '<span class="badge ' . $badgeClass . ' mb-1"><i class="bi ' . $iconClass . '"></i></span>';
            $popoverContent .= '<small class="text-muted text-center" style="font-size:0.65rem;line-height:1;">' . $statusEsc . '</small>';
            $popoverContent .= '</div>';
        }
        $popoverContent .= '</div></div>';
        $divId = 'ws-popover-' . $id;
        echo '<div class="computer idle" data-workstation-id="' . $id . '" id="' . $divId . '" tabindex="0"><i class="bi bi-pc-display"></i></div>';
        // Store raw HTML with proper escaping
        $popoverScripts[] = 'window.wsPopovers = window.wsPopovers || {}; window.wsPopovers["' . $divId . '"] = `' . str_replace('`', '\`', $popoverContent) . '`;';
    }
    echo '</div>';
    // Output the popover JS object at the end of the desks
    if (!empty($popoverScripts)) {
        echo '<script>' . implode("\n", $popoverScripts) . '</script>';
        $popoverScripts = [];
    }
};

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['workstation_id'])) {
    $workstationId = $_POST['workstation_id'];
    $startTime = $_POST['start_time'];
    $endTime = $_POST['end_time'];

    if ($reservationModel->isWorkstationAvailable($workstationId, $startTime, $endTime)) {
        if ($reservationModel->createReservation($_SESSION['user_id'], $workstationId, $startTime, $endTime)) {
            $message = 'Reservation successful!';
        } else {
            $message = 'Reservation failed. Please try again.';
        }
    } else {
        $message = 'This workstation is already reserved for the selected time.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve Workstation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/WRS/workstation-reservation-system/src/public/css/user.css">
</head>
<body>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
    <div class="container py-4">
        <h2 class="text-center mb-4"><i class="bi bi-calendar-plus"></i> Reserve a Workstation</h2>
        <?php if ($hasActiveReservation): ?>
            <div class="alert alert-warning text-center mb-4">
                You already have an active or upcoming reservation. You can only make another reservation once your current reservation time is up.
            </div>
        <?php endif; ?>
        <div class="row justify-content-center align-items-center g-4">
            <div class="col-lg-6 d-flex justify-content-center">
                <div class="lab-container" id="lab-layout">
                  <!-- Render computers with data-workstation-id attributes -->
                  <?php
                  // Render desks with only real workstation IDs
                  $renderDesk($layout['left'], 'left1');
                  $renderDesk($layout['right'], 'right1');
                  $renderDesk($layout['bottom'], 'right-bottom');
                  ?>
                  <div class="circle"></div>
                  <div class="rectangle"></div>
                  <div class="divider"></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-lg p-4 fade-in">
                    <div class="card-body">
                        <h4 class="card-title text-center mb-4">Reservation Form</h4>
                        <?php if ($message): ?>
                            <div class="alert alert-info text-center"><?php echo $message; ?></div>
                        <?php endif; ?>
                        <form method="POST" action="" id="reservation-form" <?php if ($hasActiveReservation) echo 'style="pointer-events:none;opacity:0.5;"'; ?>>
                            <input type="hidden" name="workstation_id" id="workstation_id" required>
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="datetime-local" name="start_time" id="start_time" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="duration" class="form-label">Duration</label>
                                <select name="duration" id="duration" class="form-select" required>
                                    <option value="1">1 hour</option>
                                    <option value="2">2 hours</option>
                                    <option value="3">3 hours</option>
                                    <option value="4">4 hours</option>
                                    <option value="5">5 hours</option>
                                </select>
                            </div>
                            <input type="hidden" name="end_time" id="end_time" required>
                            <div class="mb-3">
                                <label class="form-label">Select a Computer from the Lab Layout</label>
                                <div id="selected-computer" class="mb-2 text-primary"></div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" id="reserve-btn" disabled>Reserve</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Helper to fetch busy computers for the selected time range
    async function fetchBusyComputers(start, end) {
        if (!start || !end) return [];
        const resp = await fetch('workstation_status.php?start_time=' + encodeURIComponent(start) + '&end_time=' + encodeURIComponent(end));
        if (!resp.ok) return [];
        return await resp.json();
    }
    function updateLabLayout(busyIds, selectedId) {
        document.querySelectorAll('.computer').forEach(el => {
            const wsId = el.getAttribute('data-workstation-id');
            el.classList.remove('busy', 'idle', 'selected');
            if (busyIds.includes(wsId)) {
                el.classList.add('busy');
                el.setAttribute('aria-disabled', 'true');
            } else {
                el.classList.add('idle');
                el.removeAttribute('aria-disabled');
            }
            if (wsId == selectedId) {
                el.classList.add('selected');
            }
        });
    }
    let selectedWorkstation = null;
    async function refreshLabStatus() {
        const start = document.getElementById('start_time').value;
        const end = document.getElementById('end_time').value;
        const busyIds = await fetchBusyComputers(start, end);
        updateLabLayout(busyIds, selectedWorkstation);
        // Disable selection if selected is now busy
        if (selectedWorkstation && busyIds.includes(selectedWorkstation)) {
            selectedWorkstation = null;
            document.getElementById('workstation_id').value = '';
            document.getElementById('selected-computer').textContent = '';
            document.getElementById('reserve-btn').disabled = true;
        }
    }
    function updateEndTime() {
        const startInput = document.getElementById('start_time');
        const durationInput = document.getElementById('duration');
        const endInput = document.getElementById('end_time');
        if (startInput.value && durationInput.value) {
            const start = new Date(startInput.value);
            const hours = parseInt(durationInput.value);
            const end = new Date(start.getTime() + hours * 60 * 60 * 1000);
            // Format as yyyy-MM-ddTHH:mm
            const pad = n => n.toString().padStart(2, '0');
            const endStr = `${end.getFullYear()}-${pad(end.getMonth()+1)}-${pad(end.getDate())}T${pad(end.getHours())}:${pad(end.getMinutes())}`;
            endInput.value = endStr;
        } else {
            endInput.value = '';
        }
    }
    // Server UTC time as ISO string
    var serverNowUTC = "<?php echo gmdate('Y-m-d\TH:i:s'); ?>";
    window.addEventListener('DOMContentLoaded', function() {
        const startInput = document.getElementById('start_time');
        if (startInput) {
            // Use server UTC time as base
            let now = new Date(serverNowUTC + 'Z');
            // Format as yyyy-MM-ddTHH:mm (to the minute, not rounded)
            const pad = n => n.toString().padStart(2, '0');
            const local = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
            startInput.value = local;
            updateEndTime();
            refreshLabStatus();
        }
    });
    document.getElementById('start_time').addEventListener('change', function() {
        updateEndTime();
        refreshLabStatus();
    });
    document.getElementById('duration').addEventListener('change', function() {
        updateEndTime();
        refreshLabStatus();
    });
    document.querySelectorAll('.computer').forEach(el => {
        el.addEventListener('click', function() {
            if (!el.classList.contains('idle') || el.getAttribute('aria-disabled') === 'true') return;
            selectedWorkstation = el.getAttribute('data-workstation-id');
            document.getElementById('workstation_id').value = selectedWorkstation;
            document.getElementById('selected-computer').textContent = 'Selected: ' + el.title;
            document.getElementById('reserve-btn').disabled = false;
            updateLabLayout(Array.from(document.querySelectorAll('.computer.busy')).map(e => e.getAttribute('data-workstation-id')), selectedWorkstation);
        });
    });
    // On page load, disable reserve button
    document.getElementById('reserve-btn').disabled = true;
    // Add spacing between workstations
    const style = document.createElement('style');
    style.innerHTML = `
      .desk { gap: 16px !important; grid-gap: 16px !important; }
      .lab-container { padding: 16px; }
    `;
    document.head.appendChild(style);
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.wsPopovers) {
        Object.keys(window.wsPopovers).forEach(function(id) {
            var el = document.getElementById(id);
            if (el) {
                new bootstrap.Popover(el, {
                    trigger: 'hover focus',
                    placement: 'top',
                    container: 'body',
                    html: true,
                    sanitize: false,
                    content: window.wsPopovers[id]
                });
            }
        });
    }
});
    </script>
</body>
</html>