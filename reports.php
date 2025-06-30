<?php
session_start();
// Move all AJAX handlers to the very top, before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'config.php';
    header('Content-Type: application/json');

    // Handle Delete Request
    if (isset($_POST['delete_id'])) {
        $delete_id = mysqli_real_escape_string($conn, $_POST['delete_id']);
        // Fetch report info for audit trail
        $info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT product_name FROM damage_reports WHERE damage_id = '$delete_id'"));
        $deleteQuery = "DELETE FROM damage_reports WHERE damage_id = '$delete_id'";
        if (mysqli_query($conn, $deleteQuery)) {
            // Audit trail notification for delete
            $user_id = $_SESSION['user_id'] ?? null;
            $notif_message = "Damage report deleted: ID $delete_id" . ($info ? ", Product: {$info['product_name']}" : "");
            $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at) VALUES (?, ?, 0, 'unread', NOW())";
            $notif_stmt = $conn->prepare($notif_query);
            $notif_stmt->bind_param('is', $user_id, $notif_message);
            $notif_stmt->execute();
            $notif_stmt->close();

            echo json_encode(["status" => "success", "message" => "Report deleted successfully!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error deleting record: " . mysqli_error($conn)]);
        }
        exit();
    }

    // Handle Update Request
    if (isset($_POST['update_report'])) {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
        $requested_by = mysqli_real_escape_string($conn, $_POST['requested_by']);
        $number = mysqli_real_escape_string($conn, $_POST['number']);
        $section = mysqli_real_escape_string($conn, $_POST['section']);
        $quantity_damaged = mysqli_real_escape_string($conn, $_POST['quantity_damaged']);
        $damage_description = mysqli_real_escape_string($conn, $_POST['damage_description']);
        $action_taken = mysqli_real_escape_string($conn, $_POST['action_taken']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
        $checked_by = mysqli_real_escape_string($conn, $_POST['checked_by']);
        $date_resolved = !empty($_POST['date_resolved']) ? "'" . mysqli_real_escape_string($conn, $_POST['date_resolved']) . "'" : "NULL";

        // Fetch old report info for audit trail
        $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT product_name, status FROM damage_reports WHERE damage_id = '$id'"));

        $updateQuery = "UPDATE damage_reports SET 
            product_name='$product_name',
            requested_by='$requested_by',
            number='$number',
            section='$section',
            quantity_damaged='$quantity_damaged',
            damage_description='$damage_description',
            action_taken='$action_taken',
            status='$status',
            remarks='$remarks',
            checked_by='$checked_by',
            date_resolved=$date_resolved
            WHERE damage_id='$id'";

        if (mysqli_query($conn, $updateQuery)) {
            // Audit trail notification for update
            $user_id = $_SESSION['user_id'] ?? null;
            $notif_message = "Damage report updated: ID $id, Product: $product_name";
            if ($old && $old['status'] !== $status) {
                $notif_message .= " (Status changed from '{$old['status']}' to '$status')";
            }
            $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at) VALUES (?, ?, 0, 'unread', NOW())";
            $notif_stmt = $conn->prepare($notif_query);
            $notif_stmt->bind_param('is', $user_id, $notif_message);
            $notif_stmt->execute();
            $notif_stmt->close();

            echo json_encode(["status" => "success", "message" => "Report updated successfully!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error updating record: " . mysqli_error($conn)]);
        }
        exit();
    }

    // Handle Add Request
    if (isset($_POST['add_report'])) {
        // Handle multiple product names
        $product_names = isset($_POST['product_name']) ? $_POST['product_name'] : [];
        if (is_array($product_names)) {
            $product_name = mysqli_real_escape_string($conn, implode(', ', array_map('trim', $product_names)));
        } else {
            $product_name = mysqli_real_escape_string($conn, trim($product_names));
        }
        $requested_by = mysqli_real_escape_string($conn, $_POST['requested_by']);
        $number = mysqli_real_escape_string($conn, $_POST['number']);
        $section = mysqli_real_escape_string($conn, $_POST['section']);
        $quantity_damaged = mysqli_real_escape_string($conn, $_POST['quantity_damaged']);
        $damage_description = mysqli_real_escape_string($conn, $_POST['damage_description']);
        $action_taken = mysqli_real_escape_string($conn, $_POST['action_taken']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
        $checked_by = mysqli_real_escape_string($conn, $_POST['checked_by']);
        $date_resolved = !empty($_POST['date_resolved']) ? "'" . mysqli_real_escape_string($conn, $_POST['date_resolved']) . "'" : "NULL";

        $addQuery = "INSERT INTO damage_reports 
            (product_name, requested_by, number, section, quantity_damaged, damage_description, action_taken, status, remarks, checked_by, date_reported, date_resolved) 
            VALUES 
            ('$product_name', '$requested_by', '$number', '$section', '$quantity_damaged', '$damage_description', '$action_taken', '$status', '$remarks', '$checked_by', NOW(), $date_resolved)";

        if (mysqli_query($conn, $addQuery)) {
            // Audit trail notification for add
            $user_id = $_SESSION['user_id'] ?? null;
            $new_id = mysqli_insert_id($conn);
            $notif_message = "Damage report added: ID $new_id, Product: $product_name";
            $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at) VALUES (?, ?, 0, 'unread', NOW())";
            $notif_stmt = $conn->prepare($notif_query);
            $notif_stmt->bind_param('is', $user_id, $notif_message);
            $notif_stmt->execute();
            $notif_stmt->close();

            echo json_encode(["status" => "success", "message" => "Report added successfully!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error adding record: " . mysqli_error($conn)]);
        }
        exit();
    }

    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit();
}

// Regular page load - include headers and other files
include 'index/header.php';
include 'index/navigation.php';
include 'config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Damage Reports Management</title>
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<div class="page-container">
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Damage Reports</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <button class="btn btn-primary" data-toggle="modal" data-target="#addReportModal">
                                <i class="fas fa-plus"></i> Add Report
                            </button>
                        </div>
                        <div class="table-responsive m-b-50">
                            <table class="table table-bordered" id="reportTable">
                                <thead>
                                    <tr>
                                        <th>Damage ID</th>
                                        <th>Product Name</th>
                                        <th>Requested By</th>
                                        <th>Number</th>
                                        <th>Section</th>
                                        <th>Quantity Damaged</th>
                                        <th>Description</th>
                                        <th>Action Taken</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                        <th>Date Reported</th>
                                        <th>Date Resolved</th>
                                        <th>Checked By</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $query = "SELECT * FROM damage_reports ORDER BY damage_id DESC";
                                $result = mysqli_query($conn, $query);
                                if ($result) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $damage_id = htmlspecialchars($row['damage_id']);
                                        $product_name = htmlspecialchars($row['product_name']);
                                        $requested_by = htmlspecialchars($row['requested_by']);
                                        $number = htmlspecialchars($row['number']);
                                        $section = htmlspecialchars($row['section']);
                                        $quantity_damaged = htmlspecialchars($row['quantity_damaged']);
                                        $damage_description = htmlspecialchars($row['damage_description']);
                                        $action_taken = htmlspecialchars($row['action_taken']);
                                        $status = htmlspecialchars($row['status']);
                                        $remarks = htmlspecialchars($row['remarks']);
                                        $date_reported = htmlspecialchars($row['date_reported']);
                                        $date_resolved = htmlspecialchars($row['date_resolved']);
                                        $checked_by = htmlspecialchars($row['checked_by']);

                                        echo "<tr>
                                            <td>$damage_id</td>
                                            <td>$product_name</td>
                                            <td>$requested_by</td>
                                            <td>$number</td>
                                            <td>$section</td>
                                            <td>$quantity_damaged</td>
                                            <td>$damage_description</td>
                                            <td>$action_taken</td>
                                            <td>$status</td>
                                            <td>$remarks</td>
                                            <td>$date_reported</td>
                                            <td>$date_resolved</td>
                                            <td>$checked_by</td>
                                            <td>
                                                <div class='btn-group' role='group'>
                                                    <button class='btn btn-sm btn-warning editBtn' title='Edit'
                                                        data-id='$damage_id'
                                                        data-product_name='$product_name'
                                                        data-requested_by='$requested_by'
                                                        data-number='$number'
                                                        data-section='$section'
                                                        data-quantity_damaged='$quantity_damaged'
                                                        data-damage_description=\"$damage_description\"
                                                        data-action_taken=\"$action_taken\"
                                                        data-status='$status'
                                                        data-remarks=\"$remarks\"
                                                        data-date_resolved='$date_resolved'
                                                        data-checked_by='$checked_by'
                                                    >
                                                        <i class='fas fa-edit'></i>
                                                    </button>
                                                    <button class='btn btn-sm btn-danger deleteBtn' data-id='$damage_id' title='Delete'>
                                                        <i class='fas fa-trash-alt'></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='14'>Error loading data: " . mysqli_error($conn) . "</td></tr>";
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

<!-- Add Report Modal -->
<div class="modal fade" id="addReportModal" tabindex="-1" role="dialog" aria-labelledby="addReportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="addReportForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addReportModalLabel">Add Damage Report</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="product_name">Product Name</label>
                        <select name="product_name[]" id="product_name" class="form-control" multiple required>
                            <?php
                            $productQuery = "SELECT product_name FROM inventory_products ORDER BY product_name ASC";
                            $productResult = mysqli_query($conn, $productQuery);
                            if ($productResult) {
                                while ($prod = mysqli_fetch_assoc($productResult)) {
                                    $pname = htmlspecialchars($prod['product_name']);
                                    echo "<option value=\"$pname\">$pname</option>";
                                }
                            }
                            ?>
                        </select>
                        <small class="form-text text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple products.</small>
                    </div>
                    <div class="form-group">
                        <label for="requested_by">Requested By</label>
                        <input type="text" name="requested_by" id="requested_by" class="form-control" required placeholder="Enter requester name">
                    </div>
                    <div class="form-group">
                        <label for="number">Number</label>
                        <input type="number" name="number" id="number" class="form-control" placeholder="Enter number" min="0" step="1" required>
                    </div>
                    <div class="form-group">
                        <label for="section">Section</label>
                        <input type="text" name="section" id="section" class="form-control" placeholder="Enter section">
                    </div>
                    <div class="form-group">
                        <label for="quantity_damaged">Quantity Damaged</label>
                        <input type="number" name="quantity_damaged" id="quantity_damaged" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="form-group">
                        <label for="damage_description">Damage Description</label>
                        <textarea name="damage_description" id="damage_description" class="form-control" placeholder="Describe the damage"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="action_taken">Action Taken</label>
                        <input type="text" name="action_taken" id="action_taken" class="form-control" placeholder="Action taken">
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="Pending" selected>Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved">Resolved</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="remarks">Remarks</label>
                        <textarea name="remarks" id="remarks" class="form-control" placeholder="Remarks"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="date_resolved">Date Resolved</label>
                        <input type="datetime-local" name="date_resolved" id="date_resolved" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="checked_by">Checked By</label>
                        <input type="text" name="checked_by" id="checked_by" class="form-control" placeholder="Enter checked by">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add Report</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Report Modal -->
<div class="modal fade" id="editReportModal" tabindex="-1" role="dialog" aria-labelledby="editReportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editReportForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editReportModalLabel">Edit Damage Report</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label for="edit_product_name">Product Name</label>
                        <input type="text" name="product_name" id="edit_product_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_requested_by">Requested By</label>
                        <input type="text" name="requested_by" id="edit_requested_by" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_number">Number</label>
                        <input type="number" name="number" id="edit_number" class="form-control" placeholder="Enter number" min="0" step="1" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_section">Section</label>
                        <input type="text" name="section" id="edit_section" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_quantity_damaged">Quantity Damaged</label>
                        <input type="number" name="quantity_damaged" id="edit_quantity_damaged" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_damage_description">Damage Description</label>
                        <textarea name="damage_description" id="edit_damage_description" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_action_taken">Action Taken</label>
                        <input type="text" name="action_taken" id="edit_action_taken" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_status">Status</label>
                        <select name="status" id="edit_status" class="form-control">
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved">Resolved</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_remarks">Remarks</label>
                        <textarea name="remarks" id="edit_remarks" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_date_resolved">Date Resolved</label>
                        <input type="datetime-local" name="date_resolved" id="edit_date_resolved" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_checked_by">Checked By</label>
                        <input type="text" name="checked_by" id="edit_checked_by" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Report</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="vendor/jquery-3.2.1.min.js"></script>
<script src="vendor/bootstrap-4.1/popper.min.js"></script>
<script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#reportTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "searching": true,
        "ordering": true,
        "responsive": true
    });

    // Add Report Form Submit
    $('#addReportForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading
        Swal.fire({
            title: 'Adding Report',
            text: 'Please wait...',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Prepare form data
        var formData = new FormData(this);
        formData.append('add_report', '1');

        // AJAX request
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.close();
                
                // Try to parse JSON response
                var jsonResponse;
                try {
                    if (typeof response === 'string') {
                        jsonResponse = JSON.parse(response);
                    } else {
                        jsonResponse = response;
                    }
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    console.log('Raw Response:', response);
                    Swal.fire({
                        title: 'Error!',
                        html: 'Invalid response format. Please check server logs.<br><small>Response: ' + response.substring(0, 100) + '...</small>',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                
                if (jsonResponse.status === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: jsonResponse.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        $('#addReportModal').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: jsonResponse.message || 'An error occurred',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.close();
                console.error('AJAX Error:', error);
                console.log('Status:', status);
                console.log('Response Text:', xhr.responseText);
                
                Swal.fire({
                    title: 'Error!',
                    html: 'Request failed: ' + error + '<br><small>Status: ' + status + '</small>',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    // Edit Report Form Submit
    $('#editReportForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading
        Swal.fire({
            title: 'Updating Report',
            text: 'Please wait...',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Prepare form data
        var formData = new FormData(this);
        formData.append('update_report', '1');

        // AJAX request
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.close();
                
                // Try to parse JSON response
                var jsonResponse;
                try {
                    if (typeof response === 'string') {
                        jsonResponse = JSON.parse(response);
                    } else {
                        jsonResponse = response;
                    }
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    console.log('Raw Response:', response);
                    Swal.fire({
                        title: 'Error!',
                        html: 'Invalid response format. Please check server logs.<br><small>Response: ' + response.substring(0, 100) + '...</small>',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                
                if (jsonResponse.status === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: jsonResponse.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        $('#editReportModal').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: jsonResponse.message || 'An error occurred',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.close();
                console.error('AJAX Error:', error);
                console.log('Status:', status);
                console.log('Response Text:', xhr.responseText);
                
                Swal.fire({
                    title: 'Error!',
                    html: 'Request failed: ' + error + '<br><small>Status: ' + status + '</small>',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    // Edit Button Click
    $(document).on('click', '.editBtn', function() {
        var button = $(this);
        $('#edit_id').val(button.data('id'));
        $('#edit_product_name').val(button.data('product_name'));
        $('#edit_requested_by').val(button.data('requested_by'));
        $('#edit_number').val(button.data('number'));
        $('#edit_section').val(button.data('section'));
        $('#edit_quantity_damaged').val(button.data('quantity_damaged'));
        $('#edit_damage_description').val(button.data('damage_description'));
        $('#edit_action_taken').val(button.data('action_taken'));
        $('#edit_status').val(button.data('status'));
        $('#edit_remarks').val(button.data('remarks'));
        $('#edit_date_resolved').val(button.data('date_resolved'));
        $('#edit_checked_by').val(button.data('checked_by'));
        $('#editReportModal').modal('show');
    });

    // Delete Button Click
    $(document).on('click', '.deleteBtn', function() {
        var deleteId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this action!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Deleting Report',
                    text: 'Please wait...',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // AJAX delete request
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: {
                        delete_id: deleteId
                    },
                    success: function(response) {
                        Swal.close();
                        
                        // Try to parse JSON response
                        var jsonResponse;
                        try {
                            if (typeof response === 'string') {
                                jsonResponse = JSON.parse(response);
                            } else {
                                jsonResponse = response;
                            }
                        } catch (e) {
                            console.error('JSON Parse Error:', e);
                            console.log('Raw Response:', response);
                            Swal.fire({
                                title: 'Error!',
                                html: 'Invalid response format. Please check server logs.<br><small>Response: ' + response.substring(0, 100) + '...</small>',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                            return;
                        }
                        
                        if (jsonResponse.status === 'success') {
                            Swal.fire({
                                title: 'Deleted!',
                                text: jsonResponse.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: jsonResponse.message || 'An error occurred',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        console.error('AJAX Error:', error);
                        console.log('Status:', status);
                        console.log('Response Text:', xhr.responseText);
                        
                        Swal.fire({
                            title: 'Error!',
                            html: 'Request failed: ' + error + '<br><small>Status: ' + status + '</small>',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });

    // Print Single Report Button
    $(document).on('click', '.printBtnSingle', function() {
        var reportId = $(this).data('id');
        window.open('print_freight_reports.php?report_id=' + reportId, '_blank');
    });

    // Reset Add Form when modal is hidden
    $('#addReportModal').on('hidden.bs.modal', function() {
        $('#addReportForm')[0].reset();
        $('#product_id_input').val('');
    });

    // Reset Edit Form when modal is hidden
    $('#editReportModal').on('hidden.bs.modal', function() {
        $('#editReportForm')[0].reset();
    });
});
</script>

</body>
</html>