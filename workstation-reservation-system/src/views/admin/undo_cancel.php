<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/Reservation.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /WRS/workstation-reservation-system/src/views/auth/login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: reservations.php');
    exit();
}

$reservationModel = new Reservation($pdo);
$reservationId = $_GET['id'];

// Restore the reservation to pending status
if ($reservationModel->updateReservationStatus($reservationId, 'pending')) {
    header('Location: reservations.php?msg=restored');
} else {
    header('Location: reservations.php?error=restore_failed');
}
exit();
?> 