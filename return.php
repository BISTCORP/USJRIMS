<?php

session_start();

include 'index/header.php';
include 'index/navigation.php';
include 'config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Return Management</title>
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
                        <h4 class="card-title">Return List</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 action-buttons">
                            <button id="deleteSelectedReturnsBtn" class="btn btn-danger mr-2" style="display:none;">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>
                        <div class="table-responsive m-b-50">
                            <table class="table table-bordered" id="returnLogTable">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAllReturns"></th>
                                        <th>ID</th>
                                        <th>Reservation Code</th>
                                        <th>Requested By</th>
                                        <th>Product ID</th>
                                        <th>Image</th>
                                        <th>Product Name</th>
                                        <th>Reservation Date Borrowed</th>
                                        <th>Reservation Date</th> <!-- Newly added column -->
                                        <th>Status</th>
                                        <th>Borrowed Qty</th>
                                        <th>Return Qty</th>
                                        <th>Unit Price</th>
                                        <th>Subtotal</th>
                                        <th>Returned By</th>
                                        <th>Checked By</th>
                                        <th>Date & Time Returned</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $query = "SELECT 
                                    return_id,
                                    reservation_code,
                                    requested_by,
                                    product_id,
                                    image,
                                    product_name,
                                    reservation_date_borrowed,
                                    reservation_date, -- fetch new column
                                    status,
                                    borrowed_quantity,
                                    return_quantity,
                                    unit_price,
                                    subtotal,
                                    returned_by,
                                    checked_by,
                                    date_time_returned,
                                    created_at
                                FROM returns
                                ORDER BY return_id DESC";
                                $result = mysqli_query($conn, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    // Fix image path logic
                                    $imgSrc = '';
                                    if (!empty($row['image'])) {
                                        $img = $row['image'];
                                        if (filter_var($img, FILTER_VALIDATE_URL)) {
                                            $imgSrc = $img;
                                        } elseif (file_exists($img) && is_file($img)) {
                                            $imgSrc = $img;
                                        } elseif (strpos($img, 'uploads/') === 0 || strpos($img, 'images/') === 0) {
                                            $imgSrc = $img;
                                        } else {
                                            $imgSrc = "uploads/$img";
                                        }
                                    }
                                    echo "<tr>
                                        <td><input type='checkbox' class='return-row-checkbox' value='{$row['return_id']}'></td>
                                        <td>{$row['return_id']}</td>
                                        <td>" . htmlspecialchars($row['reservation_code']) . "</td>
                                        <td>" . htmlspecialchars($row['requested_by']) . "</td>
                                        <td>{$row['product_id']}</td>
                                        <td>";
                                    if ($imgSrc && @getimagesize($imgSrc)) {
                                        echo "<img src='" . htmlspecialchars($imgSrc) . "' width='50' height='50' style='object-fit:cover;'>";
                                    } else {
                                        echo "<span class='text-muted'>No Image</span>";
                                    }
                                    echo "</td>
                                        <td>" . htmlspecialchars($row['product_name']) . "</td>
                                        <td>{$row['reservation_date_borrowed']}</td>
                                        <td>{$row['reservation_date']}</td> <!-- Show new column -->
                                        ";
                                    // --- Add status badge color ---
                                    $status = strtolower($row['status']);
                                    $badgeClass = 'badge-secondary';
                                    if ($status === 'returned') $badgeClass = 'badge-success';
                                    elseif ($status === 'pending') $badgeClass = 'badge-warning';
                                    elseif ($status === 'overdue') $badgeClass = 'badge-danger';
                                    echo "<td><span class='badge {$badgeClass}' style='font-size:1em;text-transform:capitalize;'>" . htmlspecialchars($row['status']) . "</span></td>";
                                    echo "<td>{$row['borrowed_quantity']}</td>
                                        <td>{$row['return_quantity']}</td>
                                        <td>{$row['unit_price']}</td>
                                        <td>{$row['subtotal']}</td>
                                        <td>" . htmlspecialchars($row['returned_by']) . "</td>
                                        <td>" . htmlspecialchars($row['checked_by']) . "</td>
                                        <td>{$row['date_time_returned']}</td>
                                        <td>{$row['created_at']}</td>                                       
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

<script src="vendor/jquery-3.2.1.min.js"></script>
<script src="vendor/bootstrap-4.1/popper.min.js"></script>
<script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    // DataTable Initialization
    var table = $('#returnLogTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "searching": true,
        "lengthChange": true
    });

    // Delete function
    $(document).on('click', '.deleteReturnBtn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This return log will be deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Fetch info for audit trail before delete
                $.post('return_info.php', { return_id: id }, function(infoRes) {
                    $.post('return_delete.php', { return_id: id }, function(res) {
                        if(res.status === 'success') {
                            // Audit trail notification for single delete
                            if (infoRes && infoRes.status === 'success') {
                                var msg = "Return log deleted: Code " + infoRes.data.reservation_code + ", Product: " + infoRes.data.product_name + ", Requested by: " + infoRes.data.requested_by;
                                $.post('notification_add.php', { message: msg });
                            }
                            Swal.fire('Deleted!', 'Return log deleted.', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', res.message || 'Failed to delete.', 'error');
                        }
                    }, 'json');
                }, 'json');
            }
        });
    });

    // Show/hide Delete Selected button
    function toggleDeleteSelectedReturnsBtn() {
        if ($('.return-row-checkbox:checked').length > 0) {
            $('#deleteSelectedReturnsBtn').show();
        } else {
            $('#deleteSelectedReturnsBtn').hide();
        }
    }

    // Show/hide Print Selected button
    function togglePrintSelectedReturnsBtn() {
        if ($('.return-row-checkbox:checked').length > 0) {
            $('#printSelectedReturnsBtn').show();
        } else {
            $('#printSelectedReturnsBtn').hide();
        }
    }

    // Update both delete and print button visibility on checkbox change
    function toggleActionButtons() {
        toggleDeleteSelectedReturnsBtn();
        togglePrintSelectedReturnsBtn();
    }

    $(document).on('change', '.return-row-checkbox', function() {
        toggleActionButtons();
    });

    $('#selectAllReturns').on('change', function() {
        $('.return-row-checkbox').prop('checked', this.checked);
        toggleActionButtons();
    });

    // Uncheck "select all" if any box is unchecked
    $(document).on('change', '.return-row-checkbox', function() {
        if (!this.checked) {
            $('#selectAllReturns').prop('checked', false);
        }
    });

    // Print Selected logic
    $('#printSelectedReturnsBtn').on('click', function() {
        var ids = $('.return-row-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (ids.length === 0) return;

        // Open a new tab/window for each selected return log
        ids.forEach(function(id) {
            window.open('return_print.php?return_id=' + id, '_blank');
        });
    });

    // Batch delete logic
    $('#deleteSelectedReturnsBtn').on('click', function() {
        var ids = $('.return-row-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (ids.length === 0) return;

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to delete " + ids.length + " return log(s). This cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete selected!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Fetch info for audit trail before delete
                $.post('return_info.php', { delete_ids: ids }, function(infoRes) {
                    $.post('return_delete.php', { delete_ids: ids }, function(res) {
                        if (res.status === 'success') {
                            // Audit trail notification for multiple delete
                            if (infoRes && infoRes.status === 'success' && Array.isArray(infoRes.data)) {
                                var msgArr = infoRes.data.map(function(row) {
                                    return "Code " + row.reservation_code + ", Product: " + row.product_name + ", Requested by: " + row.requested_by;
                                });
                                var msg = "Return logs deleted: " + msgArr.join("; ");
                                $.post('notification_add.php', { message: msg });
                            }
                            Swal.fire('Deleted!', res.message || 'Return logs deleted.', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', res.message || 'Failed to delete.', 'error');
                        }
                    }, 'json');
                }, 'json');
            }
        });
    });
});
</script>
</body>
</html>