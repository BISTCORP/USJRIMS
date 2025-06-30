<?php

include 'config.php';
session_start();

if (isset($_POST['add_product'])) {
    // Sanitize and validate inputs
    $product_name = trim($_POST['product_name'] ?? '');
    $product_description = trim($_POST['product_description'] ?? '');
    $quantity_in_stock = filter_var($_POST['quantity_in_stock'] ?? 0, FILTER_VALIDATE_INT);
    $reorder_level = filter_var($_POST['reorder_level'] ?? 0, FILTER_VALIDATE_INT);
    $unit_price = filter_var($_POST['unit_price'] ?? 0, FILTER_VALIDATE_FLOAT);
    $supplier_id = filter_var($_POST['supplier_id'] ?? 0, FILTER_VALIDATE_INT);
    $status = trim($_POST['status'] ?? '');

    if (!$product_name) {
        die("Product name is required.");
    }
    if ($quantity_in_stock === false) $quantity_in_stock = 0;
    if ($reorder_level === false) $reorder_level = 0;
    if ($unit_price === false) $unit_price = 0.00;
    if ($supplier_id === false) $supplier_id = null;
    if (!in_array($status, ['Available', 'Not Available'])) {
        $status = 'Available'; // default status
    }

    // Insert into inventory_products
    $stmt = $conn->prepare("INSERT INTO inventory_products (product_name, product_description, quantity_in_stock, reorder_level, unit_price, supplier_id, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssiidis", $product_name, $product_description, $quantity_in_stock, $reorder_level, $unit_price, $supplier_id, $status);

    if ($stmt->execute()) {
        $new_product_id = $stmt->insert_id;  // get inserted product ID

        // Prepare to insert notification (audit trail)
        $message = "Product added: " . $product_name;
        $user_id = $_SESSION['user_id'] ?? null;

        $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, status, created_at, product_id) VALUES (?, ?, 0, 'unread', NOW(), ?)");
        if ($notifStmt) {
            $notifStmt->bind_param("isi", $user_id, $message, $new_product_id);
            $notifStmt->execute();
            $notifStmt->close();
        }

        echo "Product added successfully!";
    } else {
        echo "Error inserting product: " . $stmt->error;
    }

    $stmt->close();
}
?>
