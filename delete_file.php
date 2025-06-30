<?php
include 'config.php';

// Check if file ID is provided
if (!isset($_POST['file_id']) || empty($_POST['file_id'])) {
    echo 'File ID is required.';
    exit;
}

$file_id = (int) $_POST['file_id'];

// Fetch the file information
$query = "SELECT file_path FROM product_files WHERE file_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $file_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo 'File not found.';
    exit;
}

$file = $result->fetch_assoc();
$file_path = 'uploads/products/' . $file['file_path'];

// Delete the file from the server
if (file_exists($file_path)) {
    unlink($file_path);
}

// Delete the file record from the database
$delete_query = "DELETE FROM product_files WHERE file_id = ?";
$stmt = $conn->prepare($delete_query);
$stmt->bind_param("i", $file_id);

if ($stmt->execute()) {
    echo 'File deleted successfully.';
} else {
    echo 'Error deleting file.';
}
?>