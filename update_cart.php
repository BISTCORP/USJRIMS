<?php
session_start();
include 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = intval($_POST['cart_id']);
    $change = intval($_POST['change']);

    $stmt = $conn->prepare("SELECT c.quantity, c.product_id, p.quantity_in_stock, p.unit_price FROM cart c JOIN inventory_products p ON c.product_id = p.product_id WHERE c.cart_id = ?");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        $new_qty = $result['quantity'] + $change;
        if ($new_qty < 1 || $new_qty > $result['quantity_in_stock']) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid quantity']);
            exit;
        }
        $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        $update->bind_param("ii", $new_qty, $cart_id);
        $update->execute();

        $new_subtotal = $new_qty * $result['unit_price'];

        // Calculate new cart total and product count
        $session_id = $_SESSION['cart_session_id'];
        $total_stmt = $conn->prepare("SELECT SUM(c.quantity * p.unit_price) as cart_total, COUNT(*) as cart_items FROM cart c JOIN inventory_products p ON c.product_id = p.product_id WHERE c.session_id = ?");
        $total_stmt->bind_param("s", $session_id);
        $total_stmt->execute();
        $row = $total_stmt->get_result()->fetch_assoc();
        $cart_total = $row['cart_total'] ?? 0;
        $cart_items = $row['cart_items'] ?? 0;

        echo json_encode([
            'status' => 'success',
            'new_qty' => $new_qty,
            'new_subtotal' => $new_subtotal,
            'cart_total' => $cart_total,
            'cart_items' => $cart_items,
            'max_qty' => $result['quantity_in_stock']
        ]);
        exit;
    }
}
echo json_encode(['status' => 'error', 'message' => 'Invalid request']);