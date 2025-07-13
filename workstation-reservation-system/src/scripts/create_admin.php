<?php
// Usage: Run this script from the browser or CLI to create an admin user.
// Only super admins can create admin users through this script.

// Fix path for both CLI and web execution
$scriptDir = dirname(__FILE__);
$configPath = $scriptDir . '/../config/database.php';
require_once $configPath;

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
    // Check if super admin exists (for CLI mode)
    if (php_sapi_name() === 'cli') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'super_admin'");
        $stmt->execute();
        $superAdminCount = $stmt->fetchColumn();
        
        if ($superAdminCount === 0) {
            echo "Error: No super admin exists. Please create a super admin first using create_super_admin.php\n";
            exit();
        }
    }
    
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'super_admin')");
    try {
        $stmt->execute([$username, $email, $hashed]);
        if (php_sapi_name() === 'cli') {
            echo "Admin user created successfully!\n";
        } else {
            echo "<p>Admin user created successfully!</p>";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $errorMsg = "Username or email already exists.";
        } else {
            $errorMsg = "Error: " . htmlspecialchars($e->getMessage());
        }
        
        if (php_sapi_name() === 'cli') {
            echo "Error: $errorMsg\n";
        } else {
            echo "<p style='color:red'>$errorMsg</p>";
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (php_sapi_name() === 'cli') {
        echo "Error: All fields are required.\n";
    } else {
        echo "<p style='color:red'>All fields are required.</p>";
    }
} 