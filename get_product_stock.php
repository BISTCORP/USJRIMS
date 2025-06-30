<?php

include 'config.php';
if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    $result = $conn->query("SELECT quantity_in_stock FROM inventory_products WHERE product_id = $product_id");
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['quantity_in_stock' => $row['quantity_in_stock']]);
    } else {
        echo json_encode(['quantity_in_stock' => 0]);
    }
}
?>