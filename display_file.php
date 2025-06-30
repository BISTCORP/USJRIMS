<?php

include 'config.php';

// Check if product ID is provided
if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
    echo '<div class="alert alert-danger">Product ID is required.</div>';
    exit;
}

// Set product ID
$product_id = (int) $_POST['product_id'];

// Fetch files for the product
$query = "SELECT * FROM product_files WHERE product_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="alert alert-warning">No files found for this product.</div>';
    exit;
}

// Card header with delete and download icons
echo '<div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Files</h5>
            <div>
                <button id="download-files-btn" class="btn btn-primary btn-sm" style="display: none;">
                    <i class="fas fa-download"></i>
                </button>
                <button id="delete-files-btn" class="btn btn-danger btn-sm" style="display: none;">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
      </div>';

// Generate HTML for file list
echo '<div class="file-list d-flex flex-wrap">';
while ($file = $result->fetch_assoc()) {
    $file_path = 'uploads/products/' . $file['file_path'];
    $file_extension = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));

    echo '<div class="file-item card m-2" style="width: 200px; position: relative;">';
    echo '<input type="checkbox" class="file-checkbox" data-id="' . $file['file_id'] . '" data-path="' . htmlspecialchars($file_path) . '" style="position: absolute; top: 10px; right: 10px;">';
    if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'])) {
        // Display image with click-to-view functionality
        echo '<img src="' . htmlspecialchars($file_path) . '" alt="' . htmlspecialchars($file['file_name']) . '" class="card-img-top view-image" style="height: 150px; object-fit: cover; cursor: pointer;" data-path="' . htmlspecialchars($file_path) . '">';
    } elseif ($file_extension === 'pdf') {
        // Display PDF
        echo '<iframe src="' . htmlspecialchars($file_path) . '" class="card-img-top" style="height: 150px;" frameborder="0"></iframe>';
    } else {
        // Display as a downloadable file
        echo '<div class="card-body">';
        echo '<p class="card-text">' . htmlspecialchars($file['file_name']) . '</p>';
        echo '<a href="' . htmlspecialchars($file_path) . '" download class="btn btn-primary btn-sm">Download</a>';
        echo '</div>';
    }
    echo '</div>';
}
echo '</div>';
?>