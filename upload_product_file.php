<?php
// upload_product_file.php

include 'config.php';

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int) $_POST['product_id'];
    
    // Check if product exists
    $product_query = "SELECT product_id FROM inventory_products WHERE product_id = ?";
    $stmt = $conn->prepare($product_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product_result = $stmt->get_result();
    
    if ($product_result->num_rows === 0) {
        echo "Invalid product ID";
        exit;
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error = isset($_FILES['file']) ? uploadErrorMessage($_FILES['file']['error']) : "No file uploaded";
        echo $error;
        exit;
    }
    
    // Get file information
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_size = $_FILES['file']['size'];
    $file_type = $_FILES['file']['type'];
    
    // Validate file size (10MB max)
    $max_size = 10 * 1024 * 1024; // 10MB in bytes
    if ($file_size > $max_size) {
        echo "File is too large. Maximum size is 10MB";
        exit;
    }
    
    // Get file extension
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Validate file extension
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'];
    if (!in_array($file_extension, $allowed_extensions)) {
        echo "Invalid file type. Allowed types: " . implode(", ", $allowed_extensions);
        exit;
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = 'uploads/products/';
    $year_dir = $upload_dir . date('Y') . '/';
    $month_dir = $year_dir . date('m') . '/';
    $product_dir = $month_dir . $product_id . '/';
    
    // Create directories if they don't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    if (!is_dir($year_dir)) {
        mkdir($year_dir, 0755, true);
    }
    if (!is_dir($month_dir)) {
        mkdir($month_dir, 0755, true);
    }
    if (!is_dir($product_dir)) {
        mkdir($product_dir, 0755, true);
    }
    
    // Generate unique filename
    $new_filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '', $file_name);
    $file_path = date('Y') . '/' . date('m') . '/' . $product_id . '/' . $new_filename;
    $full_path = $upload_dir . $file_path;
    
    // Move uploaded file to destination
    if (move_uploaded_file($file_tmp, $full_path)) {
        // Insert file information into database
        $file_query = "INSERT INTO product_files (product_id, file_name, file_path, file_type, file_size, upload_date) 
                       VALUES (?, ?, ?, ?, ?, NOW())";
        $file_stmt = $conn->prepare($file_query);
        $file_stmt->bind_param("isssi", $product_id, $file_name, $file_path, $file_type, $file_size);
        
        if ($file_stmt->execute()) {
            echo "File uploaded successfully";
        } else {
            echo "Error saving file information to database: " . $conn->error;
            // Delete uploaded file since database entry failed
            unlink($full_path);
        }
    } else {
        echo "Error uploading file";
    }
    exit;
}

// Function to get upload error message
function uploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return "The uploaded file exceeds the upload_max_filesize directive in php.ini";
        case UPLOAD_ERR_FORM_SIZE:
            return "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form";
        case UPLOAD_ERR_PARTIAL:
            return "The uploaded file was only partially uploaded";
        case UPLOAD_ERR_NO_FILE:
            return "No file was uploaded";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Missing a temporary folder";
        case UPLOAD_ERR_CANT_WRITE:
            return "Failed to write file to disk";
        case UPLOAD_ERR_EXTENSION:
            return "A PHP extension stopped the file upload";
        default:
            return "Unknown upload error";
    }
}

// If not a POST request or no product_id, output the HTML form
if (!$isAjax) {
    include 'index/header.php';
    include 'index/navigation.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Product File</title>
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="page-container">
        <div class="main-content">
            <div class="section__content section__content--p30">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Upload Product File</h4>
                        </div>
                        <div class="card-body">
                            <form action="upload_product_file.php" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="product_id">Select Product</label>
                                    <select name="product_id" id="product_id" class="form-control" required>
                                        <option value="">-- Select Product --</option>
                                        <?php
                                        $products_query = "SELECT product_id, product_name FROM inventory_products ORDER BY product_name";
                                        $products_result = $conn->query($products_query);
                                        
                                        while ($product = $products_result->fetch_assoc()) {
                                            echo '<option value="' . $product['product_id'] . '">' . htmlspecialchars($product['product_name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="file">Select File</label>
                                    <input type="file" name="file" id="file" class="form-control-file" required>
                                    <small class="form-text text-muted">
                                        Allowed file types: JPG, JPEG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, CSV<br>
                                        Maximum file size: 10MB
                                    </small>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Upload File</button>
                                <a href="products.php" class="btn btn-secondary">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/jquery-3.2.1.min.js"></script>
    <script src="vendor/bootstrap-4.1/popper.min.js"></script>
    <script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
</body>
</html>
<?php
}
?>