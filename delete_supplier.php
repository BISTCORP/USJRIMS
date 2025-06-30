<?php
// delete_supplier.php - Handles supplier deletion

// Include database connection
include 'config.php';

// Check connection
if (mysqli_connect_errno()) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . mysqli_connect_error()
    ]);
    exit;
}

// Check if supplier_id is provided
if (!isset($_POST['supplier_id']) || empty($_POST['supplier_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Supplier ID is required"
    ]);
    exit;
}

$supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);

// Check if supplier exists
$check_query = "SELECT * FROM suppliers WHERE supplier_id = '$supplier_id'";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Supplier not found"
    ]);
    exit;
}

// Check if there are any dependencies before deleting
// For example, check if there are products linked to this supplier
// Modify this section according to your database structure
/*
$dep_check_query = "SELECT * FROM products WHERE supplier_id = '$supplier_id'";
$dep_check_result = mysqli_query($conn, $dep_check_query);

if (mysqli_num_rows($dep_check_result) > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Cannot delete supplier because it has associated products"
    ]);
    exit;
}
*/

// Prepare and execute deletion query
$query = "DELETE FROM suppliers WHERE supplier_id = '$supplier_id'";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([
        "status" => "error",
        "message" => "Error deleting supplier: " . mysqli_error($conn)
    ]);
    exit;
}

// Check if any rows were affected
if (mysqli_affected_rows($conn) === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Supplier could not be deleted"
    ]);
    exit;
}

// Close connection
mysqli_close($conn);

// Return success response
echo json_encode([
    "status" => "success",
    "message" => "Supplier deleted successfully"
]);