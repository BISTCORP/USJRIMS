<?php
session_start();
include 'config.php';
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
$message = isset($_POST['message']) ? $_POST['message'] : '';

if ($message) {
    $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at) VALUES (?, ?, 0, 'unread', NOW())";
    $notif_stmt = $conn->prepare($notif_query);
    $notif_stmt->bind_param('is', $user_id, $message);
    $notif_stmt->execute();
    $notif_stmt->close();
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No message']);
}
