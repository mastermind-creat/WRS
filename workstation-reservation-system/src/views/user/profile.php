<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: /WRS/workstation-reservation-system/src/views/auth/login.php');
    exit();
}
require_once '../../config/database.php';
require_once '../../models/User.php';
$userModel = new User($pdo);
$user = $userModel->getUserById($_SESSION['user_id']);
$message = '';
$error = '';
$avatarUrl = !empty($user['avatar']) ? '/WRS/uploads/avatars/' . $user['avatar'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=185a9d&color=fff&size=80';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username']);
    $newEmail = trim($_POST['email']);
    $newPassword = $_POST['password'];
    $avatarFile = $_FILES['avatar'] ?? null;
    $avatarFilename = $user['avatar'];

    // Unique username/email validation
    $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = :username OR email = :email) AND id != :id");
    $stmt->bindParam(':username', $newUsername);
    $stmt->bindParam(':email', $newEmail);
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    if ($stmt->fetch()) {
        $error = 'Username or email already taken.';
    } else {
        // Handle avatar upload
        if ($avatarFile && $avatarFile['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($avatarFile['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $avatarFilename = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
                $uploadDir = __DIR__ . '/../../../uploads/avatars/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                move_uploaded_file($avatarFile['tmp_name'], $uploadDir . $avatarFilename);
            } else {
                $error = 'Invalid avatar file type.';
            }
        }
        if (!$error) {
            // Update user
            if ($newPassword) {
                $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email, password = :password, avatar = :avatar WHERE id = :id");
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt->bindParam(':password', $hashed);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email, avatar = :avatar WHERE id = :id");
            }
            $stmt->bindParam(':username', $newUsername);
            $stmt->bindParam(':email', $newEmail);
            $stmt->bindParam(':avatar', $avatarFilename);
            $stmt->bindParam(':id', $_SESSION['user_id']);
            if ($stmt->execute()) {
                $message = 'Profile updated successfully!';
                $_SESSION['username'] = $newUsername;
                $user = $userModel->getUserById($_SESSION['user_id']);
                $avatarUrl = !empty($user['avatar']) ? '/WRS/uploads/avatars/' . $user['avatar'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=185a9d&color=fff&size=80';
            } else {
                $error = 'Failed to update profile.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Workstation Reservation System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%); }
        .profile-main { background: #fff; border-radius: 1rem; box-shadow: 0 2px 16px rgba(79,140,255,0.08); padding: 2rem 2rem 1rem 2rem; max-width: 500px; margin: 2rem auto; }
        .avatar { width: 80px; height: 80px; border-radius: 50%; box-shadow: 0 2px 8px rgba(0,0,0,0.10); object-fit: cover; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../layout/navbar.php'; ?>
    <div class="profile-main">
        <div class="text-center mb-4">
            <img src="<?php echo $avatarUrl; ?>" alt="Profile" class="avatar mb-2">
            <h3>Edit Profile</h3>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-info text-center"> <?php echo $message; ?> </div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger text-center"> <?php echo $error; ?> </div>
        <?php endif; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">New Password <span class="text-muted small">(leave blank to keep current)</span></label>
                <input type="password" class="form-control" id="password" name="password" autocomplete="new-password">
            </div>
            <div class="mb-3">
                <label for="avatar" class="form-label">Profile Picture</label>
                <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary w-100">Update Profile</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 