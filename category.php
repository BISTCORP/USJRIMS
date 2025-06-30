<?php
session_start();

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';


if (!$isAjax) {
    include 'index/header.php';
    include 'index/navigation.php';
}

include 'config.php';


// Handle Delete Request (AJAX)
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    // Fetch category info for audit trail
    $cat_res = mysqli_query($conn, "SELECT category_id, category_name FROM category WHERE category_id = '$delete_id'");
    $cat_info = mysqli_fetch_assoc($cat_res);

    $deleteQuery = "DELETE FROM category WHERE category_id = '$delete_id'";
    if (mysqli_query($conn, $deleteQuery)) {
        // Audit trail notification for single delete
        if ($cat_info) {
            $user_id = $_SESSION['user_id'] ?? null;
            $notif_message = "Category deleted: ID {$cat_info['category_id']}: {$cat_info['category_name']}";
            $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at) VALUES (?, ?, 0, 'unread', NOW())";
            $notif_stmt = $conn->prepare($notif_query);
            $notif_stmt->bind_param('is', $user_id, $notif_message);
            $notif_stmt->execute();
            $notif_stmt->close();
        }
        echo json_encode(["status" => "success", "message" => "Category deleted successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error deleting record: " . mysqli_error($conn)]);
    }
    exit();
}

// Handle Update Request (AJAX)
if (isset($_POST['update_category'])) {
    $id = $_POST['id'];
    $category_name = $_POST['category_name'];
    $category_description = $_POST['category_description'];

    // Fetch old category info for audit trail
    $cat_res = mysqli_query($conn, "SELECT category_id, category_name, category_description FROM category WHERE category_id = '$id'");
    $cat_info = mysqli_fetch_assoc($cat_res);

    $updateQuery = "UPDATE category SET 
                    category_name='$category_name',
                    category_description='$category_description'
                    WHERE category_id='$id'";

    if (mysqli_query($conn, $updateQuery)) {
        // Audit trail notification for update
        $user_id = $_SESSION['user_id'] ?? null;
        $notif_message = "Category updated: ID $id, Name changed from '{$cat_info['category_name']}' to '$category_name', Description changed from '{$cat_info['category_description']}' to '$category_description'";
        $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at) VALUES (?, ?, 0, 'unread', NOW())";
        $notif_stmt = $conn->prepare($notif_query);
        $notif_stmt->bind_param('is', $user_id, $notif_message);
        $notif_stmt->execute();
        $notif_stmt->close();

        echo json_encode(["status" => "success", "message" => "Category updated successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error updating record: " . mysqli_error($conn)]);
    }
    exit();
}

// Handle Add Request (AJAX)
if (isset($_POST['add_category'])) {
    $category_name = $_POST['category_name'];
    $category_description = $_POST['category_description'];

    $addQuery = "INSERT INTO category (category_name, category_description) 
                 VALUES ('$category_name', '$category_description')";

    if (mysqli_query($conn, $addQuery)) {
        // Audit trail notification for add
        $user_id = $_SESSION['user_id'] ?? null;
        $new_id = mysqli_insert_id($conn);
        $notif_message = "Category added: ID $new_id, Name '$category_name'";
        $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at) VALUES (?, ?, 0, 'unread', NOW())";
        $notif_stmt = $conn->prepare($notif_query);
        $notif_stmt->bind_param('is', $user_id, $notif_message);
        $notif_stmt->execute();
        $notif_stmt->close();

        echo json_encode(["status" => "success", "message" => "Category added successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error adding record: " . mysqli_error($conn)]);
    }
    exit();
}

// Handle Multiple Delete Request (AJAX)
if (isset($_POST['delete_ids']) && is_array($_POST['delete_ids'])) {
    $ids = array_map('intval', $_POST['delete_ids']);
    $ids_str = implode(',', $ids);

    // Fetch category info for audit trail
    $cat_res = mysqli_query($conn, "SELECT category_id, category_name FROM category WHERE category_id IN ($ids_str)");
    $cat_infos = [];
    while ($row = mysqli_fetch_assoc($cat_res)) {
        $cat_infos[] = "ID {$row['category_id']}: {$row['category_name']}";
    }

    $deleteQuery = "DELETE FROM category WHERE category_id IN ($ids_str)";
    if (mysqli_query($conn, $deleteQuery)) {
        // Audit trail notification for multiple delete
        if (!empty($cat_infos)) {
            $user_id = $_SESSION['user_id'] ?? null;
            $notif_message = "Categories deleted: " . implode("; ", $cat_infos);
            $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at) VALUES (?, ?, 0, 'unread', NOW())";
            $notif_stmt = $conn->prepare($notif_query);
            $notif_stmt->bind_param('is', $user_id, $notif_message);
            $notif_stmt->execute();
            $notif_stmt->close();
        }
        echo json_encode(["status" => "success", "message" => "Selected categories deleted successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error deleting records: " . mysqli_error($conn)]);
    }
    exit();
}

// If we get to this point, it's a regular page load (not AJAX)
// Continue with the rest of the HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Category Management</title>
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
</head>
<body>
    <div class="page-container">
        <div class="main-content">
            <div class="section__content section__content--p30">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Category List</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 action-buttons">
                                <!-- Delete Selected Button (shown only when boxes are checked) -->
                                <button id="deleteSelectedCategoriesBtn" class="btn btn-danger mr-2" style="display:none;">
                                    <i class="fas fa-trash"></i> Delete Selected
                                </button>
                                <!-- Add Category Button -->
                                <button class="btn btn-primary" data-toggle="modal" data-target="#addCategoryModal">
                                    <i class="fas fa-plus"></i> Add Category
                                </button>
                            </div>
                            <div class="table-responsive m-b-50">
                                <table class="table table-bordered" id="categoryTable">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="selectAllCategories"></th>
                                            <th>ID</th>
                                            <th>Category Name</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $query = "SELECT * FROM category ORDER BY category_id ASC";
                                    $result = mysqli_query($conn, $query);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>
                                        <td><input type='checkbox' class='category-row-checkbox' value='{$row['category_id']}'></td>
                                        <td>{$row['category_id']}</td>
                                        <td>{$row['category_name']}</td>
                                        <td>{$row['category_description']}</td>
                                        <td>
                                            <div class='btn-group' role='group'>
                                                <button class='btn btn-warning btn-sm editBtn' data-toggle='modal' 
                                                    data-target='#editCategoryModal'
                                                    data-id='{$row['category_id']}'
                                                    data-category_name='{$row['category_name']}'
                                                    data-category_description='{$row['category_description']}'>
                                                   <i class='fas fa-edit'></i>
                                                </button>
                                                <button class='btn btn-danger btn-sm deleteBtn' data-id='{$row['category_id']}'>
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

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="addCategoryForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Category</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Category Name</label>
                            <input type="text" name="category_name" class="form-control" required placeholder="Enter category name">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="category_description" class="form-control" required placeholder="Enter category description"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Add Category</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="editCategoryForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Category</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="form-group">
                            <label>Category Name</label>
                            <input type="text" name="category_name" id="edit_category_name" class="form-control" required placeholder="Enter category name">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="category_description" id="edit_category_description" class="form-control" required placeholder="Enter category description"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Update</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
    var table = $('#categoryTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "searching": true,
        "lengthChange": true
    });

    // Load data into Edit Modal
    $(document).on('click', '.editBtn', function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_category_name').val($(this).data('category_name'));
        $('#edit_category_description').val($(this).data('category_description'));
    });

    // Add Category via AJAX
    $('#addCategoryForm').submit(function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Adding Category',
            text: 'Please wait...',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        $.post("category.php", $(this).serialize() + "&add_category=1", function(response) {
            try {
                const res = JSON.parse(response);
                if (res.status === "success") {
                    Swal.fire({
                        title: 'Category Added',
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
    });

    // Update Category via AJAX
    $('#editCategoryForm').submit(function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Updating Category',
            text: 'Please wait...',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        $.post("category.php", $(this).serialize() + "&update_category=1", function(response) {
            try {
                const res = JSON.parse(response);
                if (res.status === "success") {
                    Swal.fire({
                        title: 'Category Updated',
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
    });

    // Delete Category via AJAX
    $(document).on('click', '.deleteBtn', function() {
        const deleteId = $(this).data('id');
        
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
                    title: 'Deleting Category',
                    text: 'Please wait...',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.post("category.php", { delete_id: deleteId }, function(response) {
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

    // Show/hide Delete Selected button
    function toggleDeleteSelectedCategoriesBtn() {
        if ($('.category-row-checkbox:checked').length > 0) {
            $('#deleteSelectedCategoriesBtn').show();
        } else {
            $('#deleteSelectedCategoriesBtn').hide();
        }
    }

    $(document).on('change', '.category-row-checkbox', function() {
        toggleDeleteSelectedCategoriesBtn();
    });

    $('#selectAllCategories').on('change', function() {
        $('.category-row-checkbox').prop('checked', this.checked);
        toggleDeleteSelectedCategoriesBtn();
    });

    // Uncheck "select all" if any box is unchecked
    $(document).on('change', '.category-row-checkbox', function() {
        if (!this.checked) {
            $('#selectAllCategories').prop('checked', false);
        }
    });

    // Batch delete logic
    $('#deleteSelectedCategoriesBtn').on('click', function() {
        var ids = $('.category-row-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (ids.length === 0) return;

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to delete " + ids.length + " category(ies). This cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete selected!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting Categories',
                    text: 'Please wait...',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.post("category.php", { delete_ids: ids }, function(response) {
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
});
</script>
</body>
</html>