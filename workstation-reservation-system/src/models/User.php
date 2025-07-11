<?php

class User {
    private $db;
    private $table = 'users';

    public function __construct($database = null) {
        $this->db = $database;
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO " . $this->table . " (username, email, password, role) VALUES (:username, :email, :password, :role)");
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        $stmt->bindParam(':role', $data['role']);
        return $stmt->execute();
    }

    public function validate($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM " . $this->table . " WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM " . $this->table . " WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers() {
        $stmt = $this->db->prepare("SELECT * FROM " . $this->table);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM " . $this->table . " WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM " . $this->table . " WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateRole($userId, $role) {
        $stmt = $this->db->prepare("UPDATE " . $this->table . " SET role = :role WHERE id = :id");
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }

    public function updateUser($userId, $username, $email, $role) {
        $stmt = $this->db->prepare("UPDATE " . $this->table . " SET username = :username, email = :email, role = :role WHERE id = :id");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }

    public function delete($userId) {
        $stmt = $this->db->prepare("DELETE FROM " . $this->table . " WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }
}