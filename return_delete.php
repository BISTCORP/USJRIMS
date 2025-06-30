<?php

include 'config.php';
header('Content-Type: application/json');

// Single delete
if (isset($_POST['return_id'])) {
    $id = intval($_POST['return_id']);
    $stmt = $conn->prepare("DELETE FROM returns WHERE return_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete.']);
    }
    exit;
}

// Multiple delete
if (isset($_POST['delete_ids']) && is_array($_POST['delete_ids'])) {
    $ids = $_POST['delete_ids'];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $conn->prepare("DELETE FROM returns WHERE return_id IN ($placeholders)");
    $int_ids = array_map('intval', $ids);
    $stmt->bind_param($types, ...$int_ids);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Selected return logs deleted successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete selected return logs.']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
exit;
?>