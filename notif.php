<?php
session_start();
include 'config.php'; // Database connection

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

// Check if the notification_id is provided in the POST request
if (isset($_POST['notification_id'])) {
    $notification_id = intval($_POST['notification_id']); // Sanitize input
    $user_id = $_SESSION['user_id'];

    // Prepare the SQL query to update the notification status
    $update_query = "UPDATE notifications SET is_read = 1, status = 'read' WHERE id = ? AND (user_id = ? OR user_id IS NULL)";
    $update_stmt = $conn->prepare($update_query); // Make sure to use the correct connection variable
    $update_stmt->bind_param('ii', $notification_id, $user_id);
    $update_stmt->execute();

    // Check if any rows were affected
    if ($update_stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }

    // Close the prepared statement
    $update_stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>