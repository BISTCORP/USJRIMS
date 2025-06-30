<?php
// mark_single_read.php
ob_start(); // Start output buffering to prevent header issues
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_clean(); // Clean any output that might have been generated

error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in production

include 'config.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Check if notification_id is provided
if (!isset($_POST['notification_id']) || !is_numeric($_POST['notification_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid notification ID']);
    exit;
}

// Check database connection
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$notification_id = intval($_POST['notification_id']);

try {
    // Verify the notification belongs to this user or is a global notification
    $verify_query = "SELECT id, user_id, is_read FROM notifications WHERE id = ? AND (user_id = ? OR user_id IS NULL)";
    $verify_stmt = $conn->prepare($verify_query);
    
    if (!$verify_stmt) {
        throw new Exception('Failed to prepare verification query: ' . $conn->error);
    }
    
    $verify_stmt->bind_param('ii', $notification_id, $user_id);
    
    if (!$verify_stmt->execute()) {
        throw new Exception('Failed to verify notification: ' . $verify_stmt->error);
    }
    
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Notification not found or access denied']);
        $verify_stmt->close();
        exit;
    }
    
    $notification = $verify_result->fetch_assoc();
    $verify_stmt->close();
    
    // Check if already read
    if ($notification['is_read'] == 1) {
        echo json_encode([
            'success' => true, 
            'message' => 'Notification already marked as read',
            'already_read' => true
        ]);
        exit;
    }
    
    // Update the notification as read
    $update_query = "UPDATE notifications SET is_read = 1, status = 'read' WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    
    if (!$update_stmt) {
        throw new Exception('Failed to prepare update query: ' . $conn->error);
    }
    
    $update_stmt->bind_param('i', $notification_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to update notification: ' . $update_stmt->error);
    }
    
    $affected_rows = $update_stmt->affected_rows;
    $update_stmt->close();
    
    if ($affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Notification marked as read successfully',
            'notification_id' => $notification_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'No changes made to notification'
        ]);
    }
    
} catch (Exception $e) {
    // Log error (in production, use proper logging)
    error_log("Mark single read error for user {$user_id}, notification {$notification_id}: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'error' => 'Failed to mark notification as read',
        'debug' => $e->getMessage() // Remove this in production
    ]);
}

$conn->close();
?>