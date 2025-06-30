<?php
header('Content-Type: application/json');
include 'config.php';

if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID.']);
    exit;
}

$product_id = intval($_POST['product_id']);
$stmt = $conn->prepare("SELECT product_id, product_name, product_description, unit_price, quantity_in_stock, image FROM inventory_products WHERE product_id = ? AND status = 'Available' LIMIT 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Check if image file exists
    $row['image'] = (!empty($row['image']) && file_exists($row['image'])) ? $row['image'] : 'images/no-image.png';
    echo json_encode(['status' => 'success', 'product' => $row]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Product not found or unavailable.']);
}

