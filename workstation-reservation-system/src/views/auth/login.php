<?php
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin') {
        header("Location: /WRS/workstation-reservation-system/src/views/admin/dashboard.php");
        exit();
    } elseif ($_SESSION['role'] === 'user') {
        header("Location: /WRS/workstation-reservation-system/src/views/user/dashboard.php");
        exit();
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../config/database.php';
    require_once '../../controllers/AuthController.php';
    $authController = new AuthController($pdo);

    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($authController->login($email, $password)) {
        header("Location: /WRS/workstation-reservation-system/src/views/user/dashboard.php");
        exit();
    } else {
        $error = 'Invalid email or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Workstation Reservation System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/workstation-reservation-system/src/public/css/style.css">
</head>
<body class="bg-light">
<?php include __DIR__ . '/../layout/navbar.php'; ?>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
            <div class="text-center mb-4">
                <i class="bi bi-person-circle" style="font-size: 3rem;"></i>
                <h2 class="mt-2">Login</h2>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <p class="mt-3 text-center">Don't have an account? <a href="/WRS/workstation-reservation-system/src/views/auth/register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>