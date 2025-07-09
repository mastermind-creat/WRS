<?php

class ReservationController {
    private $reservationModel;
    private $workstationModel;

    public function __construct($reservationModel, $workstationModel) {
        $this->reservationModel = $reservationModel;
        $this->workstationModel = $workstationModel;
    }

    public function viewAvailableWorkstations() {
        $availableWorkstations = $this->workstationModel->getAvailableWorkstations();
        include '../views/user/reserve.php';
    }

    public function reserveWorkstation($userId, $workstationId, $startTime, $endTime) {
        if ($this->reservationModel->isWorkstationAvailable($workstationId, $startTime, $endTime)) {
            $this->reservationModel->createReservation($userId, $workstationId, $startTime, $endTime);
            return ['status' => 'success', 'message' => 'Reservation successful.'];
        } else {
            return ['status' => 'error', 'message' => 'Workstation is already booked for the selected time.'];
        }
    }

    public function cancelReservation($reservationId) {
        $this->reservationModel->deleteReservation($reservationId);
        return ['status' => 'success', 'message' => 'Reservation cancelled.'];
    }

    public function getUserReservations($userId) {
        return $this->reservationModel->getReservationsByUser($userId);
    }

    public function getAdminReservations() {
        return $this->reservationModel->getAllReservations();
    }

    public function approveReservation($reservationId) {
        $this->reservationModel->approveReservation($reservationId);
        return ['status' => 'success', 'message' => 'Reservation approved.'];
    }

    public function rejectReservation($reservationId) {
        $this->reservationModel->rejectReservation($reservationId);
        return ['status' => 'success', 'message' => 'Reservation rejected.'];
    }
}