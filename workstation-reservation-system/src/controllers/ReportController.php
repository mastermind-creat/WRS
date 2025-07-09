<?php

class ReportController
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function generateReservationReport($startDate, $endDate)
    {
        $query = "SELECT COUNT(*) as total_reservations, 
                         DATE(start_time) as date 
                  FROM reservations 
                  WHERE start_time BETWEEN :start_date AND :end_date 
                  GROUP BY DATE(start_time)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generateUserActivityReport($startDate, $endDate)
    {
        $query = "SELECT user_id, COUNT(*) as activity_count 
                  FROM reservations 
                  WHERE start_time BETWEEN :start_date AND :end_date 
                  GROUP BY user_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Attach user names
        require_once __DIR__ . '/../models/User.php';
        $userModel = new User($this->db);
        foreach ($results as &$row) {
            $user = $userModel->getUserById($row['user_id']);
            $row['username'] = $user ? $user['username'] : 'Unknown';
        }
        return $results;
    }

    public function generateWorkstationUsageReport($startDate, $endDate)
    {
        $query = "SELECT workstation_id, COUNT(*) as usage_count 
                  FROM reservations 
                  WHERE start_time BETWEEN :start_date AND :end_date 
                  GROUP BY workstation_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Attach workstation names
        require_once __DIR__ . '/../models/Workstation.php';
        foreach ($results as &$row) {
            $ws = Workstation::getWorkstationById($this->db, $row['workstation_id']);
            $row['workstation_name'] = $ws ? $ws['name'] : 'Unknown';
        }
        return $results;
    }
}