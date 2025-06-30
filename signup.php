<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = trim($_POST['role'] ?? '');

    // Basic validation
    if ($username === '' || $email === '' || $password === '' || $role === '') {
        $_SESSION['login_success'] = false;
        $_SESSION['login_message'] = "All fields are required.";
        header("Location: login.php");
        exit;
    }

    // Check if email or username already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $_SESSION['login_success'] = false;
        $_SESSION['login_message'] = "Email or username already registered.";
        $stmt->close();
        header("Location: login.php");
        exit;
    }
    $stmt->close();

    // Only allow valid roles
    $allowed_roles = ['Admin', 'Viewer', 'Updater'];
    if (!in_array($role, $allowed_roles)) {
        $_SESSION['login_success'] = false;
        $_SESSION['login_message'] = "Invalid role selected.";
        header("Location: login.php");
        exit;
    }

    // Hash password and insert user
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $username, $email, $hashed, $role);
     if ($stmt->execute()) {
        $_SESSION['login_success'] = true;
        $_SESSION['login_message'] = "Registration successful! You can now log in.";
    } else {
        $_SESSION['login_success'] = false;
        $_SESSION['login_message'] = "Registration failed. Please try again. Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: login.php");
    exit;
}
?>
