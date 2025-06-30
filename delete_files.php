<?php
include 'config.php';

// Check if file IDs are provided
if (!isset($_POST['file_ids']) || empty($_POST['file_ids'])) {
    echo 'No files selected.';
    exit;
}

$file_ids = $_POST['file_ids'];

// Convert file IDs to a comma-separated string for the SQL query
$file_ids_placeholder = implode(',', array_fill(0, count($file_ids), '?'));

// Fetch file paths for the selected files
$query = "SELECT file_path FROM product_files WHERE file_id IN ($file_ids_placeholder)";
$stmt = $conn->prepare($query);
$stmt->bind_param(str_repeat('i', count($file_ids)), ...$file_ids);
$stmt->execute();
$result = $stmt->get_result();

while ($file = $result->fetch_assoc()) {
    $file_path = 'uploads/products/' . $file['file_path'];
    if (file_exists($file_path)) {
        unlink($file_path); // Delete the file from the server
    }
}

// Delete the file records from the database
$delete_query = "DELETE FROM product_files WHERE file_id IN ($file_ids_placeholder)";
$stmt = $conn->prepare($delete_query);
$stmt->bind_param(str_repeat('i', count($file_ids)), ...$file_ids);

if ($stmt->execute()) {
    echo 'Selected files deleted successfully.';
} else {
    echo 'Error deleting files.';
}
?>