<?php

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    // Get delivery info before deleting
    $getOld = $conn->prepare("SELECT qty, status, item_code FROM delivery_receipt WHERE id=?");
    $getOld->bind_param("i", $_POST['delete_id']);
    $getOld->execute();
    $oldResult = $getOld->get_result();
    $oldData = $oldResult->fetch_assoc();
    $getOld->close();

    $qty = $oldData ? intval($oldData['qty']) : 0;
    $status = $oldData ? $oldData['status'] : '';
    $product_id = $oldData ? intval($oldData['item_code']) : 0;

    // Restore inventory only if status is Pending or Cancelled
    if ($product_id > 0 && ($status === 'Pending' || $status === 'Cancelled')) {
        $restore = $conn->prepare("UPDATE inventory_products SET quantity_in_stock = quantity_in_stock + ? WHERE product_id = ?");
        $restore->bind_param("ii", $qty, $product_id);
        $restore->execute();
        $restore->close();
    }
    // If Delivered, do not restore inventory

    $sql = "DELETE FROM delivery_receipt WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_POST['delete_id']);
    $result = $stmt->execute();
    $stmt->close();

    if ($result) {
        echo json_encode(["status" => "success", "message" => "Record deleted successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error deleting record: " . $conn->error]);
    }
}
?>