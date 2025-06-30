<?php

session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_POST['cart_ids']) || !is_array($_POST['cart_ids'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);
    exit;
}

$cart_ids = array_map('intval', $_POST['cart_ids']);
$session_id = $_SESSION['cart_session_id'];
$placeholders = str_repeat('?,', count($cart_ids) - 1) . '?';

$stmt = $conn->prepare("DELETE FROM cart WHERE cart_id IN ($placeholders) AND session_id = ?");
$params = array_merge($cart_ids, [$session_id]);
$types = str_repeat('i', count($cart_ids)) . 's';
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    // Get updated cart total
    $stmt = $conn->prepare("SELECT COUNT(*) as items, SUM(quantity * unit_price) as total 
                           FROM cart c 
                           JOIN inventory_products p ON c.product_id = p.product_id 
                           WHERE session_id = ?");
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Items removed successfully',
        'cart_total' => $result['total'] ?? 0,
        'cart_items' => $result['items'] ?? 0
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to remove items'
    ]);
}