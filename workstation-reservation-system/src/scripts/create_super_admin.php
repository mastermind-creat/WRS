<?php
// Usage: Run this script from the browser or CLI to create a super admin user.
// Only the first super admin can be created through this script.

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

// Check if super admin already exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'super_admin'");
$stmt->execute();
$superAdminCount = $stmt->fetchColumn();

if ($superAdminCount > 0) {
    if (php_sapi_name() === 'cli') {
        echo "Super admin already exists. Cannot create another super admin through this script.\n";
        echo "Use the admin panel to create additional super admins.\n";
    } else {
        echo '<div style="max-width: 500px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">';
        echo '<h2 style="color: #dc3545;">Super Admin Already Exists</h2>';
        echo '<p>A super admin user already exists in the system. You cannot create another super admin through this script.</p>';
        echo '<p>Use the admin panel to create additional super admins if needed.</p>';
        echo '<a href="/WRS/workstation-reservation-system/src/views/auth/login.php" style="color: #007bff; text-decoration: none;">← Back to Login</a>';
        echo '</div>';
    }
    exit();
}

if (php_sapi_name() === 'cli') {
    // CLI mode
    $username = prompt('Enter super admin username: ');
    $email = prompt('Enter super admin email: ');
    $password = prompt('Enter super admin password: ');
} else {
    // Browser mode
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
    } else {
        // Show form
        echo '<!DOCTYPE html>';
        echo '<html lang="en">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>Create Super Admin</title>';
        echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">';
        echo '<style>';
        echo 'body { background: #f8f9fa; }';
        echo '.form-container { max-width: 500px; margin: 50px auto; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        echo '<div class="container">';
        echo '<div class="form-container">';
        echo '<div class="card shadow">';
        echo '<div class="card-header bg-primary text-white">';
        echo '<h3 class="mb-0"><i class="bi bi-shield-star me-2"></i>Create Super Admin</h3>';
        echo '</div>';
        echo '<div class="card-body">';
        echo '<div class="alert alert-info">';
        echo '<i class="bi bi-info-circle me-2"></i>';
        echo '<strong>Important:</strong> This will create the first super admin user. Only super admins can create other admins and super admins.';
        echo '</div>';
        echo '<form method="POST">';
        echo '<div class="mb-3">';
        echo '<label class="form-label">Username</label>';
        echo '<input type="text" class="form-control" name="username" required>';
        echo '</div>';
        echo '<div class="mb-3">';
        echo '<label class="form-label">Email</label>';
        echo '<input type="email" class="form-control" name="email" required>';
        echo '</div>';
        echo '<div class="mb-3">';
        echo '<label class="form-label">Password</label>';
        echo '<input type="password" class="form-control" name="password" required minlength="8">';
        echo '<div class="form-text">Minimum 8 characters</div>';
        echo '</div>';
        echo '<div class="d-grid">';
        echo '<button type="submit" class="btn btn-primary">Create Super Admin</button>';
        echo '</div>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
        exit();
    }
}

if (!empty($username) && !empty($email) && !empty($password)) {
    // Validate password length
    if (strlen($password) < 8) {
        if (php_sapi_name() === 'cli') {
            echo "Error: Password must be at least 8 characters long.\n";
        } else {
            echo '<div style="max-width: 500px; margin: 50px auto; padding: 20px; border: 1px solid #dc3545; border-radius: 8px; background: #f8d7da; color: #721c24;">';
            echo '<h3>Error</h3>';
            echo '<p>Password must be at least 8 characters long.</p>';
            echo '<a href="create_super_admin.php" style="color: #007bff;">← Try Again</a>';
            echo '</div>';
        }
        exit();
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'super_admin')");
    try {
        $stmt->execute([$username, $email, $hashed]);
        if (php_sapi_name() === 'cli') {
            echo "Super admin user created successfully!\n";
            echo "Username: $username\n";
            echo "Email: $email\n";
            echo "Role: super_admin\n";
        } else {
            echo '<div style="max-width: 500px; margin: 50px auto; padding: 20px; border: 1px solid #28a745; border-radius: 8px; background: #d4edda; color: #155724;">';
            echo '<h3 style="color: #155724;">Success!</h3>';
            echo '<p>Super admin user created successfully!</p>';
            echo '<p><strong>Username:</strong> ' . htmlspecialchars($username) . '</p>';
            echo '<p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>';
            echo '<p><strong>Role:</strong> super_admin</p>';
            echo '<a href="/WRS/workstation-reservation-system/src/views/auth/login.php" style="color: #007bff; text-decoration: none;">← Go to Login</a>';
            echo '</div>';
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
            echo '<div style="max-width: 500px; margin: 50px auto; padding: 20px; border: 1px solid #dc3545; border-radius: 8px; background: #f8d7da; color: #721c24;">';
            echo '<h3>Error</h3>';
            echo '<p>' . htmlspecialchars($errorMsg) . '</p>';
            echo '<a href="create_super_admin.php" style="color: #007bff;">← Try Again</a>';
            echo '</div>';
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo '<div style="max-width: 500px; margin: 50px auto; padding: 20px; border: 1px solid #dc3545; border-radius: 8px; background: #f8d7da; color: #721c24;">';
    echo '<h3>Error</h3>';
    echo '<p>All fields are required.</p>';
    echo '<a href="create_super_admin.php" style="color: #007bff;">← Try Again</a>';
    echo '</div>';
} 