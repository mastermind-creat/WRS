<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /WRS/workstation-reservation-system/src/views/auth/login.php');
    exit();
}
require_once '../../config/database.php';
require_once '../../models/Reservation.php';
require_once '../../models/Workstation.php';
require_once '../../controllers/ReservationController.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: reservations.php');
    exit();
}
$reservationId = (int)$_GET['id'];
$reservationModel = new Reservation($pdo);
$workstationModel = new Workstation(null, null, null, null);
$reservationController = new ReservationController($reservationModel, $workstationModel);
$reservationController->rejectReservation($reservationId);
header('Location: reservations.php?msg=rejected');
exit(); 