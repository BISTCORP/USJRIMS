<?php
header('Content-Type: application/json');
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = (int)$_POST['request_id'];
    $return_quantity = (int)$_POST['return_quantity'];
    $returned_by = trim($_POST['returned_by']);
    $received_by = trim($_POST['received_by'] ?? '');
    $client_name = trim($_POST['client_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
  
    // Get product_id, current quantity, and product name from request_item and inventory_products
    $stmt = $conn->prepare("SELECT ri.product_id, ri.quantity, ip.product_name 
                            FROM request_item ri 
                            JOIN inventory_products ip ON ri.product_id = ip.product_id 
                            WHERE ri.request_id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $stmt->bind_result($product_id, $current_quantity, $product_name);
    if (!$stmt->fetch()) {
        $stmt->close();
        echo json_encode(['status' => 'error', 'message' => 'Request not found.']);
        exit;
    }
    $stmt->close();

    if ($return_quantity > $current_quantity) {
        echo json_encode(['status' => 'error', 'message' => 'Return quantity exceeds current quantity.']);
        exit;
    }

    $conn->begin_transaction();
    try {
        // Update inventory
        $update = $conn->prepare("UPDATE inventory_products SET quantity_in_stock = quantity_in_stock + ? WHERE product_id = ?");
        $update->bind_param("ii", $return_quantity, $product_id);
        $update->execute();
        $update->close();

        // Update request_item (reduce quantity)
        $updateReq = $conn->prepare("UPDATE request_item SET quantity = quantity - ? WHERE request_id = ?");
        $updateReq->bind_param("ii", $return_quantity, $request_id);
        $updateReq->execute();
        $updateReq->close();

        // Insert into return_item table (now with received_by and checked_by)
        $insertReturn = $conn->prepare("INSERT INTO return_item 
            (product_id, product_name, current_quantity, return_quantity, returned_by, received_by, client_name, return_date, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
        $insertReturn->bind_param("isisssss", $product_id, $product_name, $current_quantity, $return_quantity, $returned_by, $received_by, $client_name, $description);
        $insertReturn->execute();
        $insertReturn->close();

        $conn->commit();
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>