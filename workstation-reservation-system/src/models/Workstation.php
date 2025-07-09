<?php

class Workstation {
    private $id;
    private $name;
    private $status; // Available or Reserved
    private $location;

    public function __construct($id, $name, $status, $location) {
        $this->id = $id;
        $this->name = $name;
        $this->status = $status;
        $this->location = $location;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getLocation() {
        return $this->location;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public static function getAllWorkstations($dbConnection) {
        $query = "SELECT * FROM workstations";
        $stmt = $dbConnection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAvailableWorkstations($dbConnection) {
        $query = "SELECT * FROM workstations WHERE is_available = 1";
        $stmt = $dbConnection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function reserveWorkstation($dbConnection, $id) {
        $query = "UPDATE workstations SET status = 'Reserved' WHERE id = :id";
        $stmt = $dbConnection->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public static function releaseWorkstation($dbConnection, $id) {
        $query = "UPDATE workstations SET status = 'Available' WHERE id = :id";
        $stmt = $dbConnection->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public static function getWorkstationById($dbConnection, $id) {
        $query = "SELECT * FROM workstations WHERE id = :id";
        $stmt = $dbConnection->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}