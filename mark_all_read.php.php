<?php
// mark_all_read.php
ob_clean();
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in production

include 'config.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Check database connection
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$total_affected = 0;

try {
    // Start transaction for data consistency
    $conn->autocommit(false);
    
    // Update user-specific notifications
    $query1 = "UPDATE notifications SET is_read = 1, status = 'read' WHERE is_read = 0 AND user_id = ?";
    $stmt1 = $conn->prepare($query1);
    
    if (!$stmt1) {
        throw new Exception('Failed to prepare user notification query: ' . $conn->error);
    }
    
    $stmt1->bind_param('i', $user_id);
    
    if (!$stmt1->execute()) {
        throw new Exception('Failed to update user notifications: ' . $stmt1->error);
    }
    
    $affected_user = $stmt1->affected_rows;
    $stmt1->close();
    
    // Update global notifications (admin notifications for all users)
    $query2 = "UPDATE notifications SET is_read = 1, status = 'read' WHERE is_read = 0 AND user_id IS NULL";
    $stmt2 = $conn->prepare($query2);
    
    if (!$stmt2) {
        throw new Exception('Failed to prepare global notification query: ' . $conn->error);
    }
    
    if (!$stmt2->execute()) {
        throw new Exception('Failed to update global notifications: ' . $stmt2->error);
    }
    
    $affected_global = $stmt2->affected_rows;
    $stmt2->close();
    
    // Commit transaction
    $conn->commit();
    $conn->autocommit(true);
    
    $total_affected = $affected_user + $affected_global;
    
    // Return success response
    echo json_encode([
        'success' => true,
        'affected_rows' => $total_affected,
        'details' => [
            'user_notifications' => $affected_user,
            'global_notifications' => $affected_global
        ],
        'message' => $total_affected > 0 ? 'All notifications marked as read successfully' : 'No unread notifications found'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $conn->autocommit(true);
    
    // Log error (in production, use proper logging)
    error_log("Mark all read error for user {$user_id}: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'error' => 'Failed to mark notifications as read',
        'debug' => $e->getMessage() // Remove this in production
    ]);
}

$conn->close();
?>