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
    echo '<div class="desk '.$class.'">';
    foreach ($ids as $id) {
        $ws = isset($workstationMap[$id]) ? $workstationMap[$id] : null;
        $name = $ws ? htmlspecialchars($ws['name']) : 'N/A';
        echo '<div class="computer idle" data-workstation-id="'.$id.'" title="'.$name.'"><i class="bi bi-pc-display"></i></div>';
    }
    echo '</div>';
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
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
        }
        .lab-container {
            position: relative;
            width: 650px;
            height: 550px;
            background-color: #4a6e5c;
            border: 2px solid #eee;
            border-radius: 1rem;
        }
        .desk {
            position: absolute;
            background: #ccc;
            display: grid;
            grid-template-columns: repeat(4, 20px);
            grid-gap: 5px;
            padding: 5px;
            border-radius: 5px;
        }
        .computer {
            width: 20px;
            height: 20px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: transform 0.3s;
            border: 2px solid transparent;
        }
        .computer.idle {
            background: #2196f3;
            color: #fff;
            cursor: pointer;
        }
        .computer.busy {
            background: #43a047;
            color: #fff;
            cursor: not-allowed;
            opacity: 0.7;
        }
        .computer.selected {
            border: 2px solid #ff9800;
            box-shadow: 0 0 8px #ff9800;
        }
        .computer:hover.idle {
            transform: scale(1.2);
            box-shadow: 0 0 5px white;
        }
        .circle {
            position: absolute;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #ccc;
            left: 300px;
            top: 20px;
        }
        .rectangle {
            position: absolute;
            width: 80px;
            height: 40px;
            background: #ccc;
            left: 400px;
            top: 30px;
        }
        .divider {
            position: absolute;
            width: 5px;
            height: 60px;
            background: #ccc;
            right: 10px;
            top: 20px;
        }
        .desk.left1 { top: 10px; left: 10px; }
        .desk.left2 { top: 90px; left: 10px; }
        .desk.left3 { top: 170px; left: 10px; }
        .desk.left4 { top: 250px; left: 10px; }
        .desk.left5 { top: 330px; left: 10px; }
        .desk.left6 { top: 410px; left: 10px; }
        .desk.right1 { top: 100px; left: 530px; grid-template-columns: repeat(2, 20px); }
        .desk.right2 { top: 190px; left: 530px; grid-template-columns: repeat(2, 20px); }
        .desk.right3 { top: 280px; left: 530px; grid-template-columns: repeat(2, 20px); }
        .desk.right-bottom { top: 430px; left: 480px; }
        @media (max-width: 1200px) {
            .lab-container { width: 100%; max-width: 98vw; }
        }
        @media (max-width: 991.98px) {
            .lab-container { margin-bottom: 2rem; }
        }
    </style>
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
    // Set start time to current date/time (rounded to next hour)
    window.addEventListener('DOMContentLoaded', function() {
        const startInput = document.getElementById('start_time');
        if (startInput) {
            const now = new Date();
            now.setMinutes(0, 0, 0);
            now.setHours(now.getMinutes() > 0 ? now.getHours() + 1 : now.getHours());
            const pad = n => n.toString().padStart(2, '0');
            const local = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:00`;
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
</body>
</html>