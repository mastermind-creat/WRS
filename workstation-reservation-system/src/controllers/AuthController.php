<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct($pdo) {
        $this->userModel = new User($pdo);
    }

    public function register($data) {
        // Validate input data
        // Hash the password
        // Create user account
        // Return success or error message
    }

    public function login($email, $password) {
        $user = $this->userModel->getUserByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] === 'admin') {
                header('Location: /WRS/workstation-reservation-system/src/views/admin/dashboard.php');
            } else {
                header('Location: /WRS/workstation-reservation-system/src/views/user/dashboard.php');
            }
            exit();
        }
        return false;
    }

    public function logout() {
        // Destroy user session
        // Redirect to login page
    }
}