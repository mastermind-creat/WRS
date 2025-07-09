<?php
require_once '../../config/database.php';
require_once '../../models/Reservation.php';
require_once '../../models/Workstation.php';

header('Content-Type: application/json');

$start = isset($_GET['start_time']) ? $_GET['start_time'] : null;
$end = isset($_GET['end_time']) ? $_GET['end_time'] : null;
if (!$start || !$end) {
    echo json_encode([]);
    exit;
}

$reservationModel = new Reservation($pdo);
$reservationModel->resetExpiredWorkstations();

// Get all workstations and return those with status 'busy'
$workstations = Workstation::getAllWorkstations($pdo);
$busy = [];
foreach ($workstations as $ws) {
    if ($ws['status'] === 'busy') {
        $busy[] = (string)$ws['id'];
    }
}
echo json_encode(array_values(array_unique($busy))); 