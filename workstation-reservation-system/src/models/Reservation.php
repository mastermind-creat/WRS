<?php

class Reservation {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function createReservation($userId, $workstationId, $startTime, $endTime) {
        $query = "INSERT INTO reservations (user_id, workstation_id, start_time, end_time) VALUES (:user_id, :workstation_id, :start_time, :end_time)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':workstation_id', $workstationId);
        $stmt->bindParam(':start_time', $startTime);
        $stmt->bindParam(':end_time', $endTime);
        return $stmt->execute();
    }

    public function getReservationsByUser($userId) {
        $query = "SELECT * FROM reservations WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllReservations() {
        $query = "SELECT * FROM reservations";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cancelReservation($reservationId) {
        $query = "DELETE FROM reservations WHERE id = :reservation_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':reservation_id', $reservationId);
        return $stmt->execute();
    }

    public function approveReservation($reservationId) {
        // Approve reservation and set approved_at
        $query = "UPDATE reservations SET status = 'approved', approved_at = NOW() WHERE id = :reservation_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':reservation_id', $reservationId);
        $stmt->execute();
        // Set workstation to busy
        $wsQuery = "UPDATE workstations w JOIN reservations r ON w.id = r.workstation_id SET w.status = 'busy' WHERE r.id = :reservation_id";
        $wsStmt = $this->db->prepare($wsQuery);
        $wsStmt->bindParam(':reservation_id', $reservationId);
        $wsStmt->execute();
        return true;
    }

    public function rejectReservation($reservationId) {
        $query = "UPDATE reservations SET status = 'canceled' WHERE id = :reservation_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':reservation_id', $reservationId);
        return $stmt->execute();
    }

    public function restoreReservation($reservationId) {
        $query = "UPDATE reservations SET status = 'pending' WHERE id = :reservation_id AND status = 'canceled'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':reservation_id', $reservationId);
        return $stmt->execute();
    }

    public function updateReservationStatus($reservationId, $status) {
        $query = "UPDATE reservations SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $reservationId);
        return $stmt->execute();
    }

    // Call this periodically or on page load to reset expired workstations to idle
    public function resetExpiredWorkstations() {
        $now = date('Y-m-d H:i:s');
        // Only set to idle if status is reserved or busy and not containing admin-set statuses
        $query = "UPDATE workstations w SET w.status = 'idle' 
                  WHERE (
                    w.status = 'reserved' OR w.status = 'busy'
                  ) AND w.id NOT IN (
                    SELECT workstation_id FROM reservations WHERE status = 'approved' AND end_time > :now
                  )";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':now', $now);
        $stmt->execute();
    }

    public function isWorkstationAvailable($workstationId, $startTime, $endTime) {
        $query = "SELECT COUNT(*) FROM reservations WHERE workstation_id = :workstation_id AND (
                    (start_time < :end_time AND end_time > :start_time)
                  )";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':workstation_id', $workstationId);
        $stmt->bindParam(':start_time', $startTime);
        $stmt->bindParam(':end_time', $endTime);
        $stmt->execute();
        return $stmt->fetchColumn() == 0;
    }

    public function userHasActiveReservation($userId) {
        $now = date('Y-m-d H:i:s');
        $query = "SELECT COUNT(*) FROM reservations WHERE user_id = :user_id AND status = 'approved' AND end_time > :now";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':now', $now);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function countPendingReservations() {
        $query = "SELECT COUNT(*) FROM reservations WHERE status = 'pending'";
        $stmt = $this->db->query($query);
        return $stmt->fetchColumn();
    }
}