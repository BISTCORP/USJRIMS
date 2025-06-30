<?php
//add_delivery.php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get shared fields
    $client_name = $_POST['client_name'];
    $address = $_POST['address'];
    $date = !empty($_POST['date']) ? $_POST['date'] : date('Y-m-d');
    $po_number = $_POST['po_number'];
    $contact_person = $_POST['contact_person'];
    $status = $_POST['status'];
    $prepared_by = $_POST['prepared_by'];
    $checked_by = $_POST['checked_by'];
    $approved_by = $_POST['approved_by'];
    $delivered_by = $_POST['delivered_by'];

    foreach ($_POST['product_id'] as $i => $product_id) {
        $qty = intval($_POST['qty'][$i]);
        $unit = $_POST['unit'][$i];
        $serial_number = isset($_POST['serial_number'][$i]) ? $_POST['serial_number'][$i] : ''; // <-- updated to per-product

        // Get product name for item_description and available quantity
        $product_query = $conn->prepare("SELECT product_name, quantity_in_stock FROM inventory_products WHERE product_id = ?");
        $product_query->bind_param("i", $product_id);
        $product_query->execute();
        $product_result = $product_query->get_result();
        $product_data = $product_result->fetch_assoc();
        $item_description = $product_data ? $product_data['product_name'] : '';
        $available_qty = $product_data ? intval($product_data['quantity_in_stock']) : 0;
        $product_query->close();

        // If status is Delivered, check if enough stock
        if ($status === 'Delivered' && $qty > $available_qty) {
            echo json_encode(["status" => "error", "message" => "Quantity for product ID $product_id exceeds available stock ($available_qty)"]);
            exit;
        }

        // Insert into delivery_receipt, store product_id in item_code
        $sql = "INSERT INTO delivery_receipt 
            (client_name, address, date, po_number, contact_person, status, qty, unit, item_code, item_description, serial_number, prepared_by, checked_by, approved_by, delivered_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssissssssss",
            $client_name,
            $address,
            $date,
            $po_number,
            $contact_person,
            $status,
            $qty,
            $unit,
            $product_id, // item_code is product_id for JOIN
            $item_description,
            $serial_number, // <-- per-product serial number
            $prepared_by,
            $checked_by,
            $approved_by,
            $delivered_by
        );
        $result = $stmt->execute();
        $stmt->close();

        // If insert successful, update inventory_products only if Delivered
        if ($result) {
            if ($status === 'Delivered') {
                $update = $conn->prepare("UPDATE inventory_products SET quantity_in_stock = quantity_in_stock - ? WHERE product_id = ?");
                $update->bind_param("ii", $qty, $product_id);
                $update->execute();
                $update->close();
            }
            // No inventory change for Pending or Cancelled
        } else {
            echo json_encode(["status" => "error", "message" => "Error adding record: " . $conn->error]);
            exit;
        }
    }
    echo json_encode(["status" => "success", "message" => "Record(s) added" . ($status === 'Delivered' ? " and inventory updated!" : "!")]);
}
?>