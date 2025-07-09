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

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}