<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/Reservation.php';
require_once '../../models/Workstation.php';
require_once '../../controllers/ReservationController.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /WRS/workstation-reservation-system/src/views/auth/login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: reservations.php');
    exit();
}

$reservationModel = new Reservation($pdo);
$workstationModel = new Workstation(null, null, null, null);
$reservationController = new ReservationController($reservationModel, $workstationModel);
$reservationController->approveReservation($_GET['id']);

header('Location: reservations.php?msg=approved');
exit; 