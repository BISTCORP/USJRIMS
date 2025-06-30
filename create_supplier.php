<?php
// Ensure this file only processes AJAX requests and returns JSON

include 'config.php';

// Prevent any output before JSON response
header('Content-Type: application/json');

// Handle Add Supplier Request
if (isset($_POST['add_supplier'])) {
    $supplier_name = $_POST['supplier_name'];
    $contact_info = $_POST['contact_info'];

    $addQuery = "INSERT INTO suppliers (supplier_name, contact_info) 
                 VALUES ('$supplier_name', '$contact_info')";

    if (mysqli_query($conn, $addQuery)) {
        echo json_encode([
            "status" => "success",
            "message" => "Supplier added successfully!"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Error adding record: " . mysqli_error($conn)
        ]);
    }
    exit();
}