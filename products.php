<?php
// First, check if this is an AJAX request - if it is, don't include headers
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Only include headers if not an AJAX request
if (!$isAjax) {
    include 'index/header.php';
    include 'index/navigation.php';
}

include 'config.php';

// Handle Delete Request (AJAX)
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Get product name before deleting
    $product_name = '';
    $name_query = mysqli_query($conn, "SELECT product_name FROM inventory_products WHERE product_id = '$delete_id'");
    if ($row = mysqli_fetch_assoc($name_query)) {
        $product_name = $row['product_name'];
    }

    $deleteQuery = "DELETE FROM inventory_products WHERE product_id = '$delete_id'";
    
    if (mysqli_query($conn, $deleteQuery)) {
        // Audit trail notification with product name
        $notif_message = "Product deleted: " . ($product_name ? $product_name . " (ID $delete_id)" : "ID $delete_id");
        $user_id = $_SESSION['user_id'] ?? null;
        $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at, product_id) VALUES (?, ?, 0, 'unread', NOW(), ?)";
        $notif_stmt = $conn->prepare($notif_query);
        $notif_stmt->bind_param('isi', $user_id, $notif_message, $delete_id);
        $notif_stmt->execute();
        $notif_stmt->close();

        echo json_encode(["status" => "success", "message" => "Product deleted successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error deleting record: " . mysqli_error($conn)]);
    }
    exit(); // Ensure the script stops here
}

// Handle Batch Delete Request (AJAX)
if (isset($_POST['delete_ids'])) {
    if (is_array($_POST['delete_ids'])) {
        $ids = array_map('intval', $_POST['delete_ids']);
        $ids_str = implode(',', $ids);

        // Get product names before deleting
        $product_names = [];
        $name_query = mysqli_query($conn, "SELECT product_id, product_name FROM inventory_products WHERE product_id IN ($ids_str)");
        while ($row = mysqli_fetch_assoc($name_query)) {
            $product_names[] = $row['product_name'] . " (ID " . $row['product_id'] . ")";
        }
        $names_str = implode(', ', $product_names);

        $deleteQuery = "DELETE FROM inventory_products WHERE product_id IN ($ids_str)";
        if (mysqli_query($conn, $deleteQuery)) {
            // Audit trail notification for batch delete with product names
            $notif_message = "Products deleted: " . $names_str;
            $user_id = $_SESSION['user_id'] ?? null;
            $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at) VALUES (?, ?, 0, 'unread', NOW())";
            $notif_stmt = $conn->prepare($notif_query);
            $notif_stmt->bind_param('is', $user_id, $notif_message);
            $notif_stmt->execute();
            $notif_stmt->close();

            echo json_encode(["status" => "success", "message" => "Selected products deleted successfully!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error deleting records: " . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request."]);
    }
    exit(); 
}

// Update the Add Product handling code
if (isset($_POST['add_product'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_description = mysqli_real_escape_string($conn, $_POST['product_description']);
    $quantity_in_stock = intval($_POST['quantity_in_stock']);
    $reorder_level = intval($_POST['reorder_level']);
    $unit_price = floatval($_POST['unit_price']);
    $category_id = intval($_POST['category_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Generate a unique product_id (auto-increment alternative)
    do {
        $new_product_id = mt_rand(100000, 999999); // 6-digit random ID
        $check_id_query = mysqli_query($conn, "SELECT 1 FROM inventory_products WHERE product_id = '$new_product_id'");
    } while (mysqli_num_rows($check_id_query) > 0);

    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = uniqid() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $image = $targetFile;
        }
    }

    // Insert with explicit product_id
    $addQuery = "INSERT INTO inventory_products 
        (product_id, image, product_name, product_description, quantity_in_stock, reorder_level, unit_price, category_id, status, date_added, last_updated) 
        VALUES 
        ('$new_product_id', '$image', '$product_name', '$product_description', $quantity_in_stock, $reorder_level, $unit_price, $category_id, '$status', NOW(), NOW())";

    if (mysqli_query($conn, $addQuery)) {
        // $new_product_id is already set
        // Audit trail notification for add
        $notif_message = "Product added (batch): " . $product_name;
        $user_id = $_SESSION['user_id'] ?? null;
        $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at, product_id) VALUES (?, ?, 0, 'unread', NOW(), ?)";
        $notif_stmt = $conn->prepare($notif_query);
        $notif_stmt->bind_param('isi', $user_id, $notif_message, $new_product_id);
        $notif_stmt->execute();
        $notif_stmt->close();

        echo json_encode(["status" => "success", "message" => "Product added successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error adding record: " . mysqli_error($conn)]);
    }
    exit();
}

// Update the Edit Product handling code
if (isset($_POST['update_product'])) {
    $id = $_POST['id'];
    $product_name = $_POST['product_name'];
    $product_description = $_POST['product_description'];
    $quantity_in_stock = $_POST['quantity_in_stock'];
    $reorder_level = $_POST['reorder_level'];
    $unit_price = $_POST['unit_price'];
    $category_id = $_POST['category_id'];
    $status = $_POST['status'];

    // Handle image upload
    $imageUpdate = '';
    $new_image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = uniqid() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $imageUpdate = ", image='$targetFile'";
            $new_image_path = $targetFile;
        }
    }

    // Fetch old values for audit trail
    $old_row = null;
    $old_query = mysqli_query($conn, "SELECT * FROM inventory_products WHERE product_id = '$id'");
    if ($old_query && $row = mysqli_fetch_assoc($old_query)) {
        $old_row = $row;
    }

    $updateQuery = "UPDATE inventory_products SET 
                    product_name='$product_name',
                    product_description='$product_description',
                    quantity_in_stock='$quantity_in_stock',
                    reorder_level='$reorder_level',
                    unit_price='$unit_price',
                    category_id='$category_id',
                    status='$status',
                    last_updated=NOW()
                    $imageUpdate
                    WHERE product_id='$id'";

    if (mysqli_query($conn, $updateQuery)) {
        // Audit trail notification for all changes in one message
        $user_id = $_SESSION['user_id'] ?? null;
        $changes = [];
        if ($old_row) {
            if ($old_row['product_name'] !== $product_name) {
                $changes[] = "Name: '{$old_row['product_name']}' → '$product_name'";
            }
            if ($old_row['product_description'] !== $product_description) {
                $changes[] = "Description changed";
            }
            if ($old_row['quantity_in_stock'] != $quantity_in_stock) {
                $changes[] = "Stock: {$old_row['quantity_in_stock']} → $quantity_in_stock";
            }
            if ($old_row['reorder_level'] != $reorder_level) {
                $changes[] = "Reorder Level: {$old_row['reorder_level']} → $reorder_level";
            }
            if ($old_row['unit_price'] != $unit_price) {
                $changes[] = "Unit Price: {$old_row['unit_price']} → $unit_price";
            }
            if ($old_row['category_id'] != $category_id) {
                // Get old/new category names
                $old_cat_name = '';
                $new_cat_name = '';
                $catq = mysqli_query($conn, "SELECT category_name FROM category WHERE category_id = '{$old_row['category_id']}'");
                if ($catq && $catrow = mysqli_fetch_assoc($catq)) $old_cat_name = $catrow['category_name'];
                $catq2 = mysqli_query($conn, "SELECT category_name FROM category WHERE category_id = '$category_id'");
                if ($catq2 && $catrow2 = mysqli_fetch_assoc($catq2)) $new_cat_name = $catrow2['category_name'];
                $changes[] = "Category: '$old_cat_name' → '$new_cat_name'";
            }
            if ($old_row['status'] !== $status) {
                $changes[] = "Status: '{$old_row['status']}' → '$status'";
            }
            if (!empty($imageUpdate)) {
                $changes[] = "Image updated";
            }
        }
        if (!empty($changes)) {
            $notif_message = "Product updated (ID $id): " . implode("; ", $changes);
            $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at, product_id) VALUES (?, ?, 0, 'unread', NOW(), ?)";
            $notif_stmt = $conn->prepare($notif_query);
            $notif_stmt->bind_param('isi', $user_id, $notif_message, $id);
            $notif_stmt->execute();
            $notif_stmt->close();
        }

        echo json_encode(["status" => "success", "message" => "Product updated successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error updating record: " . mysqli_error($conn)]);
    }
    exit();
}

// AJAX endpoint to fetch product image by ID
if (isset($_GET['get_product_image']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $img = 'images/no-image.png';
    $q = mysqli_query($conn, "SELECT image FROM inventory_products WHERE product_id = $id");
    if ($row = mysqli_fetch_assoc($q)) {
        if (!empty($row['image'])) {
            $img = $row['image'];
        }
    }
    echo json_encode(['image' => $img]);
    exit();
}

// CATEGORY CRUD AUDIT TRAIL SECTION

// Add Category
if (isset($_POST['add_category'])) {
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $addCatQuery = "INSERT INTO category (category_name) VALUES ('$category_name')";
    if (mysqli_query($conn, $addCatQuery)) {
        // Audit trail notification
        $notif_message = "Category added: $category_name";
        $user_id = $_SESSION['user_id'] ?? null;
        $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at) VALUES (?, ?, 0, 'unread', NOW())";
        $notif_stmt = $conn->prepare($notif_query);
        $notif_stmt->bind_param('is', $user_id, $notif_message);
        $notif_stmt->execute();
        $notif_stmt->close();

        echo json_encode(["status" => "success", "message" => "Category added successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error adding category: " . mysqli_error($conn)]);
    }
    exit();
}

// Edit Category
if (isset($_POST['edit_category'])) {
    $category_id = intval($_POST['category_id']);
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);

    // Get old values for audit
    $old_row = null;
    $old_query = mysqli_query($conn, "SELECT category_name FROM category WHERE category_id = $category_id");
    if ($old_query && $row = mysqli_fetch_assoc($old_query)) {
        $old_row = $row;
    }

    $editCatQuery = "UPDATE category SET category_name = '$category_name' WHERE category_id = $category_id";
    if (mysqli_query($conn, $editCatQuery)) {
        // Audit trail notification for multiple changes
        $changes = [];
        if ($old_row) {
            if ($old_row['category_name'] !== $category_name) {
                $changes[] = "name: '{$old_row['category_name']}' → '$category_name'";
            }
        }
        if (!empty($changes)) {
            $notif_message = "Category updated (ID $category_id): " . implode("; ", $changes);
            $user_id = $_SESSION['user_id'] ?? null;
            $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at) VALUES (?, ?, 0, 'unread', NOW())";
            $notif_stmt = $conn->prepare($notif_query);
            $notif_stmt->bind_param('is', $user_id, $notif_message);
            $notif_stmt->execute();
            $notif_stmt->close();
        }

        echo json_encode(["status" => "success", "message" => "Category updated successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error updating category: " . mysqli_error($conn)]);
    }
    exit();
}

// Delete Category
if (isset($_POST['delete_category'])) {
    $category_id = intval($_POST['category_id']);
    // Get name for audit
    $cat_name = '';
    $cat_query = mysqli_query($conn, "SELECT category_name FROM category WHERE category_id = $category_id");
    if ($row = mysqli_fetch_assoc($cat_query)) {
        $cat_name = $row['category_name'];
    }
    $delCatQuery = "DELETE FROM category WHERE category_id = $category_id";
    if (mysqli_query($conn, $delCatQuery)) {
        // Audit trail notification
        $notif_message = "Category deleted: $cat_name (ID $category_id)";
        $user_id = $_SESSION['user_id'] ?? null;
        $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at) VALUES (?, ?, 0, 'unread', NOW())";
        $notif_stmt = $conn->prepare($notif_query);
        $notif_stmt->bind_param('is', $user_id, $notif_message);
        $notif_stmt->execute();
        $notif_stmt->close();

        echo json_encode(["status" => "success", "message" => "Category deleted successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error deleting category: " . mysqli_error($conn)]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Project Management</title>
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }

        /* Status Badge Styles */
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 0.25rem;
            text-align: center;
            width: 100%;
        }
        .status-available {
            background-color: #28a745;
            color: white;
        }
        .status-not-available {
            background-color: #dc3545;
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="page-container">
        <div class="main-content">
            <div class="section__content section__content--p30">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Manage Inventory Products</h4>
                        </div>
                        <div class="card-body">
                            <!-- Action Buttons and Filters -->
                           <!-- Action Buttons and Filters -->
<div class="mb-3 action-buttons">
    <!-- Add Product Button -->
    <button class="btn btn-primary" data-toggle="modal" data-target="#addProductModal">
        <i class="fas fa-plus"></i> Add Product
    </button>

    <!-- Print Button -->
    <button class="btn btn-primary no-print" id="printBtn">
        <i class="fas fa-print"></i> Print
    </button>

    <!-- Export CSV Button -->
    <a href="export_inventory_csv.php" class="btn btn-success">
        <i class="fas fa-file-csv"></i> Export CSV
    </a>

    <!-- Status Filter -->
    <select id="statusFilter" class="form-control" style="width: 200px; margin-left: 10px;">
        <option value="">All Status</option>
        <option value="Available">Available</option>
        <option value="Not Available">Not Available</option>
    </select>
</div>

                            <!-- Product Table -->
                            <div class="table-responsive m-b-50">
                                <table class="table table-bordered" id="productTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Image</th>
                                            <th>Product Name</th>
                                            <th>Description</th>
                                            <th>Quantity in Stock</th>
                                            <th>Reorder Level</th>
                                            <th>Unit Price</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Date Added</th>
                                            <th>Last Updated</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    // Update the SELECT query
                                    $query = "SELECT ip.*, c.category_name 
                                              FROM inventory_products ip 
                                              LEFT JOIN category c ON ip.category_id = c.category_id 
                                              ORDER BY ip.product_id ASC";
                                    $result = mysqli_query($conn, $query);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        // Create status badge based on product status
                                        $status = $row['status'];
                                        $statusBadgeClass = 'status-badge ';
                                        switch ($status) {
                                            case 'Available':
                                                $statusBadgeClass .= 'status-available';
                                                break;
                                            case 'Not Available':
                                                $statusBadgeClass .= 'status-not-available';
                                                break;
                                            default:
                                                $statusBadgeClass .= 'status-available';
                                        }

                                        // Show image if exists, else show placeholder
                                        $imgSrc = !empty($row['image']) ? htmlspecialchars($row['image']) : 'img/no-image.png';

                                        echo "<tr>
                                        <td>{$row['product_id']}</td>
                                        <td><img src='{$imgSrc}' alt='Product Image' style='width:50px;height:50px;object-fit:cover;'></td>
                                        <td>" . nl2br(wordwrap_product_name($row['product_name'], 3)) . "</td>
                                        <td>" . nl2br(wordwrap_limit_words($row['product_description'], 6)) . "</td>
                                        <td>{$row['quantity_in_stock']}</td>
                                        <td>{$row['reorder_level']}</td>
                                        <td>₱" . number_format($row['unit_price'], 2) . "</td>
                                        <td>" . htmlspecialchars($row['category_name']) . "</td> <!-- <-- Show category name -->
                                        <td><span class='{$statusBadgeClass}'>{$status}</span></td>
                                        <td>{$row['date_added']}</td>
                                        <td>{$row['last_updated']}</td>
                                        <td>
                                            <div class='btn-group' role='group'>
                                                <button class='btn btn-warning btn-sm editBtn' data-toggle='modal' 
                                                    data-target='#editProductModal'
                                                    data-id='{$row['product_id']}'
                                                    data-product_name='{$row['product_name']}'
                                                    data-product_description='{$row['product_description']}'
                                                    data-quantity_in_stock='{$row['quantity_in_stock']}'
                                                    data-reorder_level='{$row['reorder_level']}'
                                                    data-unit_price='{$row['unit_price']}'
                                                    data-category_id='{$row['category_id']}'
                                                    data-status='{$row['status']}'>
                                                   <i class='fas fa-edit'></i>
                                                </button>
                                                <button class='btn btn-danger btn-sm deleteBtn' data-id='{$row['product_id']}'>
                                                    <i class='fas fa-trash'></i>
                                                </button>
                                            </div>
                                        </td>
                                        </tr>";
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>

                           
                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="addProductForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Product</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Product Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label>Product Name <span style="color:red">*</span></label>
                            <input type="text" name="product_name" class="form-control" required placeholder="Enter product name">
                        </div>
                        <div class="form-group">
                            <label>Description <span style="color:red">*</span></label>
                            <textarea name="product_description" class="form-control" required placeholder="Enter product description"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Quantity in Stock <span style="color:red">*</span></label>
                            <input type="number" name="quantity_in_stock" class="form-control" required placeholder="Enter initial stock quantity">
                        </div>
                        <div class="form-group">
                            <label>Reorder Level <span style="color:red">*</span></label>
                            <input type="number" name="reorder_level" class="form-control" required placeholder="Enter reorder level (minimum stock)">
                        </div>
                       <div class="alert alert-info py-2" style="font-size: 0.97em;">
  <strong>Note:</strong> 
  <span>
    <b>Reorder Level</b> is the minimum number of items that should be kept in stock. 
    If the available stock is equal to or lower than this level, 
    the system will not allow further deductions to prevent stock from running out.
  </span>
</div>

                        <div class="form-group">
                            <label>Unit Price <span style="color:red">*</span></label>
                            <input type="number" step="0.01" name="unit_price" class="form-control" required placeholder="Enter unit price (e.g. 100.00)">
                        </div>
                        <div class="form-group">
                            <label for="category_id">Category <span style="color:red">*</span></label>
                            <select name="category_id" id="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php
                                $cat_query = "SELECT category_id, category_name FROM category ORDER BY category_name";
                                $cat_result = mysqli_query($conn, $cat_query);
                                while ($cat = mysqli_fetch_assoc($cat_result)) {
                                    echo "<option value='{$cat['category_id']}'>{$cat['category_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status <span style="color:red">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="Available">Available</option>
                                <option value="Not Available">Not Available</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Add Product</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 600px;">
            <form id="editProductForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Product</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="form-group">
                            <label>Current Image</label>
                            <div id="edit_image_preview" style="min-height:80px;">
                                <img src="img/no-image.png" style="width:80px;height:80px;object-fit:cover;border:1px solid #eee;border-radius:8px;" id="edit_image_tag" alt="Product Image">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Change Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label>Product Name <span style="color:red">*</span></label>
                            <input type="text" name="product_name" id="edit_product_name" class="form-control" required placeholder="Enter product name">
                        </div>
                        <div class="form-group">
                            <label>Description <span style="color:red">*</span></label>
                            <textarea name="product_description" id="edit_product_description" class="form-control" required placeholder="Enter product description"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Quantity in Stock <span style="color:red">*</span></label>
                            <input type="number" name="quantity_in_stock" id="edit_quantity_in_stock" class="form-control" required placeholder="Enter current stock quantity">
                        </div>
                        <div class="form-group">
                            <label>Reorder Level <span style="color:red">*</span></label>
                            <input type="number" name="reorder_level" id="edit_reorder_level" class="form-control" required placeholder="Enter reorder level (minimum stock)">
                        </div>
                        <div class="alert alert-info py-2" style="font-size: 0.97em;">
  <strong>Note:</strong> 
  <span>
    <b>Reorder Level</b> is the minimum number of items that should be kept in stock. 
    If the available stock is equal to or lower than this level, 
    the system will not allow further deductions to prevent stock from running out.
  </span>
</div>

                        <div class="form-group">
                            <label>Unit Price <span style="color:red">*</span></label>
                            <input type="number" step="0.01" name="unit_price" id="edit_unit_price" class="form-control" required placeholder="Enter unit price (e.g. 100.00)">
                        </div>
                        <div class="form-group">
                            <label for="category_id">Category <span style="color:red">*</span></label>
                            <select name="category_id" id="edit_category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php
                                $cat_query = "SELECT category_id, category_name FROM category ORDER BY category_name";
                                $cat_result = mysqli_query($conn, $cat_query);
                                while ($cat = mysqli_fetch_assoc($cat_result)) {
                                    echo "<option value='{$cat['category_id']}'>{$cat['category_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status <span style="color:red">*</span></label>
                            <select name="status" id="edit_status" class="form-control" required>
                                <option value="Available">Available</option>
                                <option value="Not Available">Not Available</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Update</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script src="vendor/jquery-3.2.1.min.js"></script>
    <script src="vendor/bootstrap-4.1/popper.min.js"></script>
    <script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
$(document).ready(function() {
    // DataTable Initialization
    var table = $('#productTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "searching": true
    });

    // Add checkboxes to each row and a select all checkbox in the table header
    if ($('#productTable thead th.select-all-checkbox').length === 0) {
        $('#productTable thead tr').prepend('<th class="select-all-checkbox"><input type="checkbox" id="selectAllProducts"></th>');
        $('#productTable tbody tr').each(function() {
            var id = $(this).find('td').eq(0).text();
            $(this).prepend('<td><input type="checkbox" class="product-row-checkbox" value="' + id + '"></td>');
        });
    }

    // Add Delete Selected button if not present
    if ($('#deleteSelectedProductsBtn').length === 0) {
        // Move Delete Selected button before Add Product button
        $('.action-buttons').prepend('<button id="deleteSelectedProductsBtn" class="btn btn-danger mr-2" style="display:none;"><i class="fas fa-trash"></i> Delete Selected</button>');
    }

    // Show/hide Delete Selected button
    function toggleDeleteSelectedProductsBtn() {
        if ($('.product-row-checkbox:checked').length > 0) {
            $('#deleteSelectedProductsBtn').show();
        } else {
            $('#deleteSelectedProductsBtn').hide();
        }
    }

    $(document).on('change', '.product-row-checkbox', function() {
        toggleDeleteSelectedProductsBtn();
    });

    $('#selectAllProducts').on('change', function() {
        $('.product-row-checkbox').prop('checked', this.checked);
        toggleDeleteSelectedProductsBtn();
    });

    // Uncheck "select all" if any box is unchecked
    $(document).on('change', '.product-row-checkbox', function() {
        if (!this.checked) {
            $('#selectAllProducts').prop('checked', false);
        }
    });

    // Batch delete logic
    $('#deleteSelectedProductsBtn').on('click', function() {
        var ids = $('.product-row-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (ids.length === 0) return;

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to delete " + ids.length + " product(s). This cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete selected!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting Products',
                    text: 'Please wait...',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.post("products.php", { delete_ids: ids }, function(response) {
                    try {
                        const res = JSON.parse(response);
                        if (res.status === "success") {
                            Swal.fire({
                                title: 'Deleted!',
                                text: res.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: res.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    } catch (e) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Successfully deleted!',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => location.reload());
                    }
                });
            }
        });
    });

    // Print functionality
    document.getElementById("printBtn").addEventListener("click", function() {
        var status = document.getElementById("statusFilter").value;
        window.open("inventory_reports.php?status=" + encodeURIComponent(status), "_blank");
    });

    // Load data into Edit Modal
    $(document).on('click', '.editBtn', function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_product_name').val($(this).data('product_name'));
        $('#edit_product_description').val($(this).data('product_description'));
        $('#edit_quantity_in_stock').val($(this).data('quantity_in_stock'));
        $('#edit_reorder_level').val($(this).data('reorder_level'));
        $('#edit_unit_price').val($(this).data('unit_price'));
        $('#edit_category_id').val($(this).data('category_id'));
        $('#edit_status').val($(this).data('status'));

        var productId = $(this).data('id');
        // Fetch image from server
        $.get('products.php', { get_product_image: 1, id: productId }, function(resp) {
            var img = 'img/no-image.png';
            try {
                var data = JSON.parse(resp);
                if (data.image) img = data.image;
            } catch(e) {}
            $('#edit_image_preview').html('<img src="' + img + '" style="width:80px;height:80px;object-fit:cover;border:1px solid #eee;border-radius:8px;" id="edit_image_tag" alt="Product Image">');
        });
    });

    // Add Product via AJAX (with image)
    $('#addProductForm').submit(function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Adding Product',
            text: 'Please wait...',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => { Swal.showLoading(); }
        });

        var formData = new FormData(this);
        formData.append('add_product', 1);

        $.ajax({
            url: "products.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    const res = JSON.parse(response);
                    if (res.status === "success") {
                        Swal.fire({ icon: 'success', title: 'Success', text: res.message }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                    }
                } catch (e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Unexpected error.' });
                }
            }
        });
    });

    // Load data into Edit Modal
    $(document).on('click', '.editBtn', function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_product_name').val($(this).data('product_name'));
        $('#edit_product_description').val($(this).data('product_description'));
        $('#edit_quantity_in_stock').val($(this).data('quantity_in_stock'));
        $('#edit_reorder_level').val($(this).data('reorder_level'));
        $('#edit_unit_price').val($(this).data('unit_price'));
        $('#edit_category_id').val($(this).data('category_id'));
        $('#edit_status').val($(this).data('status'));

        var productId = $(this).data('id');
        // Fetch image from server
        $.get('products.php', { get_product_image: 1, id: productId }, function(resp) {
            var img = 'img/no-image.png';
            try {
                var data = JSON.parse(resp);
                if (data.image) img = data.image;
            } catch(e) {}
            $('#edit_image_preview').html('<img src="' + img + '" style="width:80px;height:80px;object-fit:cover;border:1px solid #eee;border-radius:8px;" id="edit_image_tag" alt="Product Image">');
        });
    });

    // Update Product via AJAX (with image)
    $('#editProductForm').submit(function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Updating Product',
            text: 'Please wait...',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => { Swal.showLoading(); }
        });

        var formData = new FormData(this);
        formData.append('update_product', 1);

        $.ajax({
            url: "products.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    const res = JSON.parse(response);
                    if (res.status === "success") {
                        Swal.fire({ icon: 'success', title: 'Success', text: res.message }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                    }
                } catch (e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Unexpected error.' });
                }
            }
        });
    });

    // Delete Product via AJAX
    $(document).on('click', '.deleteBtn', function() {
        var deleteId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting Product',
                    text: 'Please wait...',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.post("products.php", { delete_id: deleteId }, function(response) {
                    try {
                        const res = JSON.parse(response);
                        if (res.status === "success") {
                            Swal.fire({
                                title: 'Deleted!',
                                text: res.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: res.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    } catch (e) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Invalid response from server',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        console.error("Response error:", response);
                    }
                });
            }
        });
    });
});
    </script>
</body>
</html>
<?php
function wordwrap_limit_words($text, $words_per_line = 6) {
    $words = explode(' ', $text);
    $lines = [];
    for ($i = 0; $i < count($words); $i += $words_per_line) {
        $lines[] = implode(' ', array_slice($words, $i, $words_per_line));
    }
    return implode("\n", $lines);
}

// Add this function for product name (3 words per line)
function wordwrap_product_name($text, $words_per_line = 3) {
    $words = explode(' ', $text);
    $lines = [];
    for ($i = 0; $i < count($words); $i += $words_per_line) {
        $lines[] = implode(' ', array_slice($words, $i, $words_per_line));
    }
    return implode("\n", $lines);
}
?>
</body>
</html>