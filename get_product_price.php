<?php
// filepath: c:\xampp1\htdocs\Inventory\get_product_price.php
include 'config.php';
if (isset($_POST['product_id'])) {
    $pid = intval($_POST['product_id']);
    $result = $conn->query("SELECT unit_price FROM inventory_products WHERE product_id = $pid");
    if ($row = $result->fetch_assoc()) {
        echo $row['unit_price'];
    } else {
        echo 0;
    }
}
?>