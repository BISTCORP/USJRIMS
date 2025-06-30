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
    $deleteQuery = "DELETE FROM client WHERE id = '$delete_id'";
    if (mysqli_query($conn, $deleteQuery)) {
        echo json_encode(["status" => "success", "message" => "Client deleted successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error deleting record: " . mysqli_error($conn)]);
    }
    exit();
}

// Handle Update Request (AJAX)
if (isset($_POST['update_client'])) {
    $id = $_POST['id'];
    $client_name = $_POST['client_name'];
    $address = $_POST['address'];
    $contact_person = $_POST['contact_person'];

    $updateQuery = "UPDATE client SET 
                    client_name='$client_name',
                    address='$address',
                    contact_person='$contact_person'
                    WHERE id='$id'";

    if (mysqli_query($conn, $updateQuery)) {
        echo json_encode(["status" => "success", "message" => "Client updated successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error updating record: " . mysqli_error($conn)]);
    }
    exit();
}

// Handle Add Request (AJAX)
if (isset($_POST['add_client'])) {
    $client_name = $_POST['client_name'];
    $address = $_POST['address'];
    $contact_person = $_POST['contact_person'];

    $addQuery = "INSERT INTO client (client_name, address, contact_person) 
                 VALUES ('$client_name', '$address', '$contact_person')";

    if (mysqli_query($conn, $addQuery)) {
        echo json_encode(["status" => "success", "message" => "Client added successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error adding record: " . mysqli_error($conn)]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Management</title>
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
                            <h4 class="card-title">Client List</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 action-buttons">
                                <button class="btn btn-primary" data-toggle="modal" data-target="#addClientModal">
                                    <i class="fas fa-plus"></i> Add Client
                                </button>
                            </div>
                            <div class="table-responsive m-b-50">
                                <table class="table table-bordered" id="clientTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Client Name</th>
                                            <th>Address</th>
                                            <th>Contact Person</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $query = "SELECT * FROM client ORDER BY id ASC";
                                    $result = mysqli_query($conn, $query);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>
                                        <td>{$row['id']}</td>
                                        <td>{$row['client_name']}</td>
                                        <td>{$row['address']}</td>
                                        <td>{$row['contact_person']}</td>
                                        <td>
                                            <div class='btn-group' role='group'>
                                                <button class='btn btn-warning btn-sm editBtn' data-toggle='modal' 
                                                    data-target='#editClientModal'
                                                    data-id='{$row['id']}'
                                                    data-client_name='{$row['client_name']}'
                                                    data-address='{$row['address']}'
                                                    data-contact_person='{$row['contact_person']}'>
                                                   <i class='fas fa-edit'></i>
                                                </button>
                                                <button class='btn btn-danger btn-sm deleteBtn' data-id='{$row['id']}'>
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

    <!-- Add Client Modal -->
    <div class="modal fade" id="addClientModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="addClientForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Client</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Client Name <span style="color:red">*</span></label>
                            <input type="text" name="client_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Address <span style="color:red">*</span></label>
                            <textarea name="address" class="form-control" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Contact Person <span style="color:red">*</span></label>
                            <input type="text" name="contact_person" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Add Client</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Client Modal -->
    <div class="modal fade" id="editClientModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="editClientForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Client</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="form-group">
                            <label>Client Name <span style="color:red">*</span></label>
                            <input type="text" name="client_name" id="edit_client_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Address <span style="color:red">*</span></label>
                            <textarea name="address" id="edit_address" class="form-control" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Contact Person <span style="color:red">*</span></label>
                            <input type="text" name="contact_person" id="edit_contact_person" class="form-control" required>
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
    var table = $('#clientTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "searching": true,
        "lengthChange": true
    });

    // Load data into Edit Modal
    $(document).on('click', '.editBtn', function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_client_name').val($(this).data('client_name'));
        $('#edit_address').val($(this).data('address'));
        $('#edit_contact_person').val($(this).data('contact_person'));
    });

    // Add Client via AJAX
    $('#addClientForm').submit(function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Adding Client',
            text: 'Please wait...',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        $.post("client.php", $(this).serialize() + "&add_client=1", function(response) {
            try {
                const res = JSON.parse(response);
                if (res.status === "success") {
                    Swal.fire({
                        title: 'Client Added',
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

    // Update Client via AJAX
    $('#editClientForm').submit(function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Updating Client',
            text: 'Please wait...',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        $.post("client.php", $(this).serialize() + "&update_client=1", function(response) {
            try {
                const res = JSON.parse(response);
                if (res.status === "success") {
                    Swal.fire({
                        title: 'Client Updated',
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

    // Delete Client via AJAX
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
                    title: 'Deleting Client',
                    text: 'Please wait...',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.post("client.php", { delete_id: deleteId }, function(response) {
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