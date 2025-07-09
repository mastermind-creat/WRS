<?php
// Usage: Run this script from the browser or CLI to create an admin user.
require_once '../config/database.php';

function prompt($prompt) {
    if (php_sapi_name() === 'cli') {
        echo $prompt;
        return trim(fgets(STDIN));
    } else {
        return isset($_POST[$prompt]) ? trim($_POST[$prompt]) : '';
    }
}

if (php_sapi_name() === 'cli') {
    // CLI mode
    $username = prompt('Enter admin username: ');
    $email = prompt('Enter admin email: ');
    $password = prompt('Enter admin password: ');
} else {
    // Browser mode
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
    } else {
        // Show form
        echo '<form method="POST"><h2>Create Admin User</h2>';
        echo '<label>Username: <input name="username" required></label><br><br>';
        echo '<label>Email: <input name="email" type="email" required></label><br><br>';
        echo '<label>Password: <input name="password" type="password" required></label><br><br>';
        echo '<button type="submit">Create Admin</button>';
        echo '</form>';
        exit();
    }
}

if (!empty($username) && !empty($email) && !empty($password)) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
    try {
        $stmt->execute([$username, $email, $hashed]);
        echo "<p>Admin user created successfully!</p>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "<p style='color:red'>Username or email already exists.</p>";
        } else {
            echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<p style='color:red'>All fields are required.</p>";
} 