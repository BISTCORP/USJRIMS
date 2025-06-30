<?php
ob_clean();
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';
header('Content-Type: application/json');

// Check DB connection
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

$affected1 = 0;
$affected2 = 0;
$error1 = '';
$error2 = '';
$success1 = true;
$success2 = true;

// If user is logged in, update their notifications
if ($user_id !== null) {
    $query1 = "UPDATE notifications SET is_read = 1, status = 'read' WHERE is_read = 0 AND user_id = ?";
    $stmt1 = $conn->prepare($query1);
    if (!$stmt1) {
        echo json_encode(['success' => false, 'error' => 'Prepare1: ' . $conn->error]);
        exit;
    }
    $stmt1->bind_param('i', $user_id);
    $success1 = $stmt1->execute();
    $affected1 = $stmt1->affected_rows;
    $error1 = $stmt1->error . ' | Query1: ' . $query1 . ' | UserID: ' . $user_id;
    $stmt1->close();
}

// Always update global notifications (user_id IS NULL)
$query2 = "UPDATE notifications SET is_read = 1, status = 'read' WHERE is_read = 0 AND user_id IS NULL";
$stmt2 = $conn->prepare($query2);
if (!$stmt2) {
    echo json_encode(['success' => false, 'error' => 'Prepare2: ' . $conn->error]);
    exit;
}
$success2 = $stmt2->execute();
$affected2 = $stmt2->affected_rows;
$error2 = $stmt2->error . ' | Query2: ' . $query2;
$stmt2->close();

if ($success1 && $success2) {
    echo json_encode([
        'success' => true,
        'affected_rows' => ($affected1 + $affected2),
        'details' => [
            'affected_user' => $affected1,
            'affected_global' => $affected2
        ]
    ]);
    exit;
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to update notifications',
        'error1' => $error1,
        'error2' => $error2
    ]);
    exit;
}

// Fallback for any unexpected output