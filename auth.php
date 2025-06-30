<?php
session_start();
include 'config.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['login_success'] = false;
    $_SESSION['login_message'] = 'Please enter both email and password.';
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['login_success'] = true;
    $_SESSION['login_message'] = "You're now signed in. Welcome back!";

    // Add notification for the user
    $user_id = $_SESSION['user_id']; // Make sure this is set after successful login
    $message = "You have logged in successfully.";
    $status = 'unread';
    $is_read = 0;

    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $user_id, $message, $is_read, $status);
    $stmt->execute();
    $stmt->close();

    header("Location: dashboard.php");
    exit();
} else {
    $_SESSION['login_success'] = false;
    $_SESSION['login_message'] = "Invalid email or password.";
    header("Location: login.php");
    exit();
}
?>