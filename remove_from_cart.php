<?php

session_start();
include 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = intval($_POST['cart_id']);
    $session_id = $_SESSION['cart_session_id'];

    $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND session_id = ?");
    $stmt->bind_param("is", $cart_id, $session_id);
    $stmt->execute();

    // Calculate new cart total and product count
    $total_stmt = $conn->prepare("SELECT SUM(c.quantity * p.unit_price) as cart_total, COUNT(*) as cart_items FROM cart c JOIN inventory_products p ON c.product_id = p.product_id WHERE c.session_id = ?");
    $total_stmt->bind_param("s", $session_id);
    $total_stmt->execute();
    $row = $total_stmt->get_result()->fetch_assoc();
    $cart_total = $row['cart_total'] ?? 0;
    $cart_items = $row['cart_items'] ?? 0;

    echo json_encode([
        'status' => 'success',
        'cart_total' => $cart_total,
        'cart_items' => $cart_items
    ]);
    exit;
}
echo json_encode(['status' => 'error', 'message' => 'Invalid request']);