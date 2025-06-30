<?php
include 'config.php';
header('Content-Type: application/json');

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$unit_cost = 0;

if ($product_id > 0) {
    $stmt = $conn->prepare("SELECT unit_price FROM inventory_products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($unit_cost);
    $stmt->fetch();
    $stmt->close();
}

echo json_encode(['unit_cost' => $unit_cost]);
?>