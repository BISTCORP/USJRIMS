<?php
session_start();
include 'config.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    echo "Invalid or missing token.";
    exit;
}

// Check token validity
$stmt = $conn->prepare("SELECT user_id, expires_at, used FROM password_resets WHERE token = ? LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    echo "Invalid or expired token.";
    exit;
}
$stmt->bind_result($user_id, $expires_at, $used);
$stmt->fetch();
$stmt->close();

if ($used || strtotime($expires_at) < time()) {
    echo "This reset link is expired or already used.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['password'] ?? '';
    if (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed, $user_id);
        $stmt->execute();
        $stmt->close();

        // Mark token as used
        $stmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->close();

        $_SESSION['login_success'] = true;
        $_SESSION['login_message'] = "Password reset successful! You can now log in.";
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        body { font-family: Arial; background: #f8fbf9; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .reset-box { background: #fff; padding: 32px 28px; border-radius: 14px; box-shadow: 0 8px 32px rgba(0,0,0,0.12); width: 350px; }
        .reset-box h2 { margin-bottom: 18px; color: #0f3a2a; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; }
        .form-group input { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; }
        .btn { width: 100%; background: #1a5f3f; color: #fff; border: none; border-radius: 8px; padding: 12px 0; font-size: 1.1rem; font-weight: 600; cursor: pointer; }
        .error { color: #e74c3c; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="reset-box">
        <h2>Reset Password</h2>
        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" name="password" id="password" required minlength="6" placeholder="Enter new password">
            </div>
            <button type="submit" class="btn">Reset Password</button>
        </form>
    </div>
</body>
</html>
