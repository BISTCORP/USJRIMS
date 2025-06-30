<?php
session_start();
include 'config.php';
header('Content-Type: application/json');

if (isset($_POST['return_id'])) {
    $id = intval($_POST['return_id']);
    $res = mysqli_query($conn, "SELECT reservation_code, product_name, requested_by FROM returns WHERE return_id = $id");
    if ($row = mysqli_fetch_assoc($res)) {
        echo json_encode(['status' => 'success', 'data' => $row]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Not found']);
    }
    exit;
}

if (isset($_POST['delete_ids']) && is_array($_POST['delete_ids'])) {
    $ids = array_map('intval', $_POST['delete_ids']);
    $ids_str = implode(',', $ids);
    $result = [];
    $res = mysqli_query($conn, "SELECT reservation_code, product_name, requested_by FROM returns WHERE return_id IN ($ids_str)");
    while ($row = mysqli_fetch_assoc($res)) {
        $result[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $result]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
