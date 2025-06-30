<?php

include 'config.php';
session_start();

if (isset($_POST['update_product'])) {
    $id = $_POST['id'];
    $product_name = $_POST['product_name'];
    $product_description = $_POST['product_description'];
    $quantity_in_stock = $_POST['quantity_in_stock'];
    $reorder_level = $_POST['reorder_level'];
    $unit_price = $_POST['unit_price'];
    $supplier_id = $_POST['supplier_id'];
    $status = $_POST['status'];

    $query = "UPDATE inventory_products SET 
              product_name=?,
              product_description=?,
              quantity_in_stock=?,
              reorder_level=?,
              unit_price=?,
              supplier_id=?,
              status=?
              WHERE product_id=?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "ssiidisi",
        $product_name,
        $product_description,
        $quantity_in_stock,
        $reorder_level,
        $unit_price,
        $supplier_id,
        $status,
        $id
    );

    if ($stmt->execute()) {
        // Insert notification after the product is updated (audit trail)
        $message = "Product updated: " . $product_name;
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, status, created_at, product_id) VALUES (?, ?, 0, 'unread', NOW(), ?)");
        if ($notifStmt) {
            $notifStmt->bind_param("isi", $user_id, $message, $id);
            $notifStmt->execute();
            $notifStmt->close();
        }
        echo "Product updated successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>