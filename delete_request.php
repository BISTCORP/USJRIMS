<?php
// delete_request.php
require_once 'config.php';
require_once 'request_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response("error", "Invalid request method");
}

if (!isset($_POST['delete_id'])) {
    json_response("error", "Invalid action");
}

$delete_id = intval($_POST['delete_id']);
if ($delete_id <= 0) {
    json_response("error", "Invalid request ID");
}

// Get request data before deletion
$request_data = get_request_by_id($conn, $delete_id);
if (!$request_data) {
    json_response("error", "Request not found");
}

$conn->begin_transaction();

try {
    // Delete request
    $stmt = $conn->prepare("DELETE FROM request_form WHERE request_id=?");
    $stmt->bind_param("i", $delete_id);
    if (!$stmt->execute()) {
        $conn->rollback();
        json_response("error", "Error deleting request: " . $stmt->error);
    }
    $stmt->close();

    // Restore stock quantity
    $stmt = $conn->prepare("UPDATE inventory_products SET quantity_in_stock = quantity_in_stock + ? WHERE product_id = ?");
    $stmt->bind_param("ii", $request_data['quantity'], $request_data['product_id']);
    if (!$stmt->execute()) {
        $conn->rollback();
        json_response("error", "Error restoring stock: " . $stmt->error);
    }
    $stmt->close();

    $conn->commit();
    json_response("success", "Request deleted and stock restored!");

} catch (Exception $e) {
    $conn->rollback();
    json_response("error", "Database error: " . $e->getMessage());
}
?>
