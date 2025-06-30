<?php
session_start();
include 'config.php';

// Helper to insert notification
function insert_audit_notification($conn, $user_id, $message) {
    $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at) VALUES (?, ?, 0, 'unread', NOW())";
    $notif_stmt = $conn->prepare($notif_query);
    $notif_stmt->bind_param('is', $user_id, $message);
    $notif_stmt->execute();
    $notif_stmt->close();
}

$user_id = $_SESSION['user_id'] ?? null;

// Single delete
if (isset($_POST['return_id'])) {
    $return_id = intval($_POST['return_id']);
    $res = mysqli_query($conn, "SELECT reservation_code, product_name, requested_by FROM returns WHERE return_id = $return_id");
    if ($row = mysqli_fetch_assoc($res)) {
        $msg = "Return log deleted: Code {$row['reservation_code']}, Product: {$row['product_name']}, Requested by: {$row['requested_by']}";
        insert_audit_notification($conn, $user_id, $msg);
    }
    echo json_encode(['status' => 'success']);
    exit();
}

// Multiple delete
if (isset($_POST['delete_ids']) && is_array($_POST['delete_ids'])) {
    $ids = array_map('intval', $_POST['delete_ids']);
    $ids_str = implode(',', $ids);
    $res = mysqli_query($conn, "SELECT reservation_code, product_name, requested_by FROM returns WHERE return_id IN ($ids_str)");
    $msgs = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $msgs[] = "Code {$row['reservation_code']}, Product: {$row['product_name']}, Requested by: {$row['requested_by']}";
    }
    if (!empty($msgs)) {
        $msg = "Return logs deleted: " . implode("; ", $msgs);
        insert_audit_notification($conn, $user_id, $msg);
    }
    echo json_encode(['status' => 'success']);
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
