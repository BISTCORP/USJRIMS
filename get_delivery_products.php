<?php
include 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$products = [];

if ($id > 0) {
    $sql = "SELECT 
                dr.item_code AS product_id,
                ip.product_name,
                ip.status AS product_status,
                ip.quantity_in_stock AS product_qty,
                dr.qty,
                dr.unit,
                dr.serial_number
            FROM delivery_receipt dr
            JOIN inventory_products ip ON dr.item_code = ip.product_id
            WHERE dr.client_name = (
                SELECT client_name FROM delivery_receipt WHERE id = ?
            ) AND dr.po_number = (
                SELECT po_number FROM delivery_receipt WHERE id = ?
            ) AND dr.date = (
                SELECT date FROM delivery_receipt WHERE id = ?
            )";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $id, $id, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'product_id' => $row['product_id'],
            'product_status' => $row['product_status'],
            'product_qty' => $row['product_qty'],
            'qty' => $row['qty'],
            'unit' => $row['unit'],
            'serial_number' => $row['serial_number']
        ];
    }
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($products);