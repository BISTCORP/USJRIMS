<?php
session_start();

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// --- AJAX handler for bulk delete ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected_ids'])) {
    include 'config.php';
    header('Content-Type: application/json');
    $ids = $_POST['delete_selected_ids'];
    if (is_array($ids) && count($ids) > 0) {
        $ids = array_map('intval', $ids);
        $ids_string = implode(',', $ids);
        $sql = "DELETE FROM borrowed_items WHERE borrowed_id IN ($ids_string)";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['status' => 'success', 'message' => 'Selected borrowed items deleted.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Delete failed: ' . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No IDs provided.']);
    }
    exit();
}

// --- AJAX handler for bulk return ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_selected_ids'])) {
    include 'config.php';
    header('Content-Type: application/json');
    
    // Accept all relevant form data
    $ids = $_POST['return_selected_ids'];
    $reservation_codes = isset($_POST['reservation_code']) ? $_POST['reservation_code'] : [];
    $requested_bys = isset($_POST['requested_by']) ? $_POST['requested_by'] : [];
    $product_ids = isset($_POST['product_id']) ? $_POST['product_id'] : [];
    $images = isset($_POST['image']) ? $_POST['image'] : [];
    $product_names = isset($_POST['product_name']) ? $_POST['product_name'] : [];
    $date_borroweds = isset($_POST['date_borrowed']) ? $_POST['date_borrowed'] : [];
    $reservation_dates = isset($_POST['reservation_date']) ? $_POST['reservation_date'] : []; // <-- ADD THIS
    $statuses = isset($_POST['status']) ? $_POST['status'] : [];
    $borrowed_quantities = isset($_POST['borrowed_quantity']) ? $_POST['borrowed_quantity'] : [];
    $return_quantities = isset($_POST['return_quantity']) ? $_POST['return_quantity'] : [];
    $unit_prices = isset($_POST['unit_price']) ? $_POST['unit_price'] : [];
    $returned_bys = isset($_POST['returned_by']) ? $_POST['returned_by'] : [];
    $checked_bys = isset($_POST['checked_by']) ? $_POST['checked_by'] : [];

    if (is_array($ids) && count($ids) > 0) {
        $ids = array_map('intval', $ids);
        $ids_string = implode(',', $ids);
        $success = true;
        $errorMsg = '';
        for ($i = 0; $i < count($ids); $i++) {
            // Defensive: check if all arrays have this index
            if (!isset($reservation_codes[$i], $requested_bys[$i], $product_ids[$i], $product_names[$i], $date_borroweds[$i], $reservation_dates[$i], $borrowed_quantities[$i], $return_quantities[$i], $unit_prices[$i], $returned_bys[$i], $checked_bys[$i])) continue;
            $reservation_code = mysqli_real_escape_string($conn, $reservation_codes[$i]);
            $requested_by = mysqli_real_escape_string($conn, $requested_bys[$i]);
            $product_id = intval($product_ids[$i]);
            // --- Fix: get image value from form or fallback to DB if empty ---
            $image = '';
            if (isset($images[$i]) && !empty($images[$i])) {
                $image = mysqli_real_escape_string($conn, $images[$i]);
            } else {
                // fallback: fetch from borrowed_items if not present in POST
                $img_res = mysqli_query($conn, "SELECT image FROM borrowed_items WHERE borrowed_id = {$ids[$i]}");
                $img_row = mysqli_fetch_assoc($img_res);
                $image = isset($img_row['image']) ? mysqli_real_escape_string($conn, $img_row['image']) : '';
            }
            $product_name = mysqli_real_escape_string($conn, $product_names[$i]);
            $date_borrowed = mysqli_real_escape_string($conn, $date_borroweds[$i]);
            $reservation_date = mysqli_real_escape_string($conn, $reservation_dates[$i]); // <-- ADD THIS
            $status = isset($statuses[$i]) ? mysqli_real_escape_string($conn, $statuses[$i]) : 'Returned';
            $borrowed_quantity = intval($borrowed_quantities[$i]);
            $return_quantity = intval($return_quantities[$i]);
            $unit_price = floatval($unit_prices[$i]);
            $subtotal = $return_quantity * $unit_price;
            $returned_by = mysqli_real_escape_string($conn, $returned_bys[$i]);
            $checked_by = mysqli_real_escape_string($conn, $checked_bys[$i]);
            // Backend validation: return_quantity must not exceed borrowed_quantity and must be at least 1
            if ($return_quantity > $borrowed_quantity || $return_quantity < 1) {
                $success = false;
                $errorMsg = 'Return quantity must be between 1 and the borrowed quantity.';
                break;
            }
            // Insert return record with correct values, including reservation_date
            $sql = "INSERT INTO returns (reservation_code, requested_by, product_id, image, product_name, reservation_date_borrowed, reservation_date, status, borrowed_quantity, return_quantity, unit_price, returned_by, checked_by, date_time_returned, created_at) VALUES ('{$reservation_code}', '{$requested_by}', {$product_id}, '{$image}', '{$product_name}', '{$date_borrowed}', '{$reservation_date}', 'Returned', {$borrowed_quantity}, {$return_quantity}, {$unit_price}, '{$returned_by}', '{$checked_by}', NOW(), NOW())";
            if (!mysqli_query($conn, $sql)) {
                $success = false;
                $errorMsg = mysqli_error($conn);
                break;
            } else {
                // Restock only the actual returned quantity
                $updateStockSql = "UPDATE inventory_products SET quantity_in_stock = quantity_in_stock + {$return_quantity} WHERE product_id = {$product_id}";
                if (!mysqli_query($conn, $updateStockSql)) {
                    $success = false;
                    $errorMsg = 'Stock update failed: ' . mysqli_error($conn);
                    break;
                }
            }
        }
        // Delete borrowed_items records after return
        $sql = "DELETE FROM borrowed_items WHERE borrowed_id IN ($ids_string)";
        mysqli_query($conn, $sql);
        if ($success) {
            echo json_encode(['status' => 'success', 'message' => 'Selected borrowed items returned, logged in returns, and removed from borrowed list.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Return failed: ' . $errorMsg]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No IDs provided.']);
    }
    exit();
}

if (!$isAjax) {
    include 'index/header.php';
    include 'index/navigation.php';
}

include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Borrowed Items</title>
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
                        <h4 class="card-title">Borrowed Items</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 action-buttons d-flex align-items-center" style="gap: 1rem;">
                            <button type="button" class="btn btn-danger" id="deleteSelectedBtn" style="display:none;">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                            <button type="button" class="btn btn-success" id="returnSelectedBtn" style="display:none;">
                                <i class="fas fa-undo"></i> Return Selected
                            </button>
                        </div>
                        <div class="table-responsive m-b-50">
                            <table class="table table-bordered" id="borrowedTable">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAll"></th>
                                        <th>ID</th>
                                        <th>Reservation Code</th>
                                        <th>Requested By</th>
                                        <th>Product ID</th>
                                        <th>Image</th>
                                        <th>Product Name</th>
                                        <th>Date Borrowed</th>
                                        <th>Reservation Date</th> <!-- NEW COLUMN -->
                                        <th>Status</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Subtotal</th>
                                        <th>Checked By</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                // Updated query to include reservation_date
                                $sql = "SELECT borrowed_id, reservation_code, requested_by, product_id, image, product_name, date_borrowed, reservation_date, status, product_qty, unit_price, subtotal, checked_by, created_at FROM borrowed_items ORDER BY borrowed_id DESC";
                                $result = mysqli_query($conn, $sql);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $status = strtolower($row['status']);
                                    $badgeClass = 'badge-secondary';
                                    if ($status === 'borrowed') $badgeClass = 'badge-info';
                                    elseif ($status === 'returned') $badgeClass = 'badge-primary';
                                    elseif ($status === 'overdue') $badgeClass = 'badge-danger';

                                    echo "<tr>
                                        <td><input type='checkbox' class='row-select' value='{$row['borrowed_id']}'></td>
                                        <td>{$row['borrowed_id']}</td>
                                        <td>{$row['reservation_code']}</td>
                                        <td>{$row['requested_by']}</td>
                                        <td>{$row['product_id']}</td>
                                        <td>";
                                    if ($row['image']) {
                                        $imgSrc = (strpos($row['image'], 'uploads/') === 0 || strpos($row['image'], 'images/') === 0)
                                            ? $row['image']
                                            : "uploads/{$row['image']}";
                                        echo "<img src='$imgSrc' alt='Product Image' style='width:40px;height:40px;object-fit:cover;'>";
                                    }
                                    echo "</td>
                                        <td>{$row['product_name']}</td>
                                        <td>{$row['date_borrowed']}</td>
                                        <td>{$row['reservation_date']}</td> <!-- NEW VALUE -->
                                        <td><span class='badge {$badgeClass}' style='font-size:1em;text-transform:capitalize;'>{$row['status']}</span></td>
                                        <td>{$row['product_qty']}</td>
                                        <td>" . number_format($row['unit_price'], 2) . "</td>
                                        <td>" . number_format($row['subtotal'], 2) . "</td>
                                        <td>{$row['checked_by']}</td>
                                        <td>{$row['created_at']}</td>
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

    <!-- Return Selected Modal -->
    <div class="modal fade" id="returnSelectedModal" tabindex="-1" role="dialog" aria-labelledby="returnSelectedModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl" role="document" style="max-width: 1200px; min-width: 500px; width: 95vw;">
        <form id="bulkReturnForm">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="returnSelectedModalLabel">Return Selected Borrowed Item(s)</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <p>Returning <span id="bulkReturnCount"></span> selected item(s):</p>
              <div class="alert alert-info py-2" style="font-size: 0.97em;">
                <strong>Note:</strong>
                <span>
                  Please review the details below before confirming the return. Only <b>Return Qty</b> and <b>Returned By</b> are editable.
                </span>
              </div>
              <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm" id="bulkReturnTable">
                  <thead>
                    <tr>
                      <th>Reservation Code</th>
                      <th>Requested By</th>
                      <th>Product ID</th>
                      <th>Image</th>
                      <th>Product Name</th>
                      <th>Date Borrowed</th>
                      <th>Reservation Date</th>
                      <th>Status</th>
                      <th>Borrowed Qty</th>
                      <th>Return Qty</th>
                      <th>Unit Price</th>
                      <th>Subtotal</th>
                      <th>Returned By</th>
                      <th>Checked By</th>
                    </tr>
                  </thead>
                  <tbody id="returnSelectedList">
                    <!-- Product rows will be injected here by JS -->
                  </tbody>
                </table>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-success">Return</button>
              <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    <!-- End Modal -->

    <script src="vendor/jquery-3.2.1.min.js"></script>
    <script src="vendor/bootstrap-4.1/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
    $(document).ready(function() {
        var table = $('#borrowedTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "searching": true,
            "lengthChange": true
        });

        // Checkbox logic for multiple select
        $('#selectAll').on('change', function() {
            $('.row-select').prop('checked', $(this).prop('checked'));
            toggleActionButtons();
        });
        $(document).on('change', '.row-select', function() {
            if (!$(this).prop('checked')) {
                $('#selectAll').prop('checked', false);
            }
            toggleActionButtons();
        });
        function toggleActionButtons() {
            var checked = $('.row-select:checked').length;
            if (checked > 0) {
                $('#deleteSelectedBtn').show();
                $('#returnSelectedBtn').show();
            } else {
                $('#deleteSelectedBtn').hide();
                $('#returnSelectedBtn').hide();
            }
        }

        // Delete selected logic
        $('#deleteSelectedBtn').on('click', function() {
            var selectedIds = $('.row-select:checked').map(function() {
                return $(this).val();
            }).get();
            if (selectedIds.length === 0) return;
            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete all selected borrowed items.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete selected!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '', // same page
                        type: 'POST',
                        data: { delete_selected_ids: selectedIds },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Deleted!', response.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });
                }
            });
        });

        // Return selected logic with modal
        let selectedReturnIds = [];
        $('#returnSelectedBtn').on('click', function() {
            selectedReturnIds = [];
            let tableRows = [];
            let count = 0;
            $('.row-select:checked').each(function() {
                let row = $(this).closest('tr');
                let tds = row.find('td');
                let borrowed_id = $(this).val();
                let reservation_code = tds.eq(2).text().trim();
                let requested_by = tds.eq(3).text().trim();
                let product_id = tds.eq(4).text().trim();
                let image_html = tds.eq(5).html();
                let product_name = tds.eq(6).text().trim();
                let date_borrowed = tds.eq(7).text().trim();
                let reservation_date = tds.eq(8).text().trim(); // NEW: get reservation date
                let status = tds.eq(9).text().trim();
                let borrowed_quantity = tds.eq(10).text().trim();
                let return_quantity = borrowed_quantity;
                let unit_price = tds.eq(11).text().trim();
                let subtotal = tds.eq(12).text().trim();
                let returned_by = ""; // Editable
                let checked_by = tds.eq(13).text().trim();

                selectedReturnIds.push(borrowed_id);
                count++;
                tableRows.push(
                    `<tr>
                        <td>
                            <input type="hidden" name="return_selected_ids[]" value="${borrowed_id}">
                            <input type="hidden" name="borrowed_ids[]" value="${borrowed_id}">
                            <input type="text" class="form-control" value="${reservation_code}" readonly>
                            <input type="hidden" name="reservation_code[]" value="${reservation_code}">
                        </td>
                        <td><input type="text" class="form-control" value="${requested_by}" readonly>
                            <input type="hidden" name="requested_by[]" value="${requested_by}">
                        </td>
                        <td><input type="number" class="form-control" value="${product_id}" readonly>
                            <input type="hidden" name="product_id[]" value="${product_id}">
                        </td>
                        <td>${image_html}
                            <input type="hidden" name="image[]" value="">
                        </td>
                        <td><input type="text" class="form-control" value="${product_name}" readonly>
                            <input type="hidden" name="product_name[]" value="${product_name}">
                        </td>
                        <td><input type="text" class="form-control" value="${date_borrowed}" readonly>
                            <input type="hidden" name="date_borrowed[]" value="${date_borrowed}">
                        </td>
                        <td><input type="text" class="form-control" value="${reservation_date}" readonly>
                            <input type="hidden" name="reservation_date[]" value="${reservation_date}">
                        </td>
                        <td><input type="text" class="form-control" value="${status}" readonly>
                            <input type="hidden" name="status[]" value="${status}">
                        </td>
                        <td><input type="number" class="form-control" value="${borrowed_quantity}" readonly>
                            <input type="hidden" name="borrowed_quantity[]" value="${borrowed_quantity}">
                        </td>
                        <td>
                            <input type="number" name="return_quantity[]" class="form-control" value="${return_quantity}" min="1" max="${borrowed_quantity}" required>
                        </td>
                        <td>
                            <input type="text" class="form-control" value="${unit_price}" readonly>
                            <input type="hidden" name="unit_price[]" value="${unit_price}">
                        </td>
                        <td>
                            <input type="text" class="form-control" value="${subtotal}" readonly>
                            <input type="hidden" name="subtotal[]" value="${subtotal}">
                        </td>
                        <td>
                            <input type="text" name="returned_by[]" class="form-control" value="${returned_by}" required placeholder="Returned By (required)">
                            <span class="text-danger" style="font-size:0.9em;display:none;">Required</span>
                        </td>
                        <td>
                            <input type="text" class="form-control" value="${checked_by}" readonly>
                            <input type="hidden" name="checked_by[]" value="${checked_by}">
                        </td>
                    </tr>`
                );
            });
            if (selectedReturnIds.length === 0) return;
            $('#bulkReturnCount').text(count);
            $('#returnSelectedList').html(tableRows.join(''));
            $('#returnSelectedModal').modal('show');
        });

        // Handle bulk return form submit
        $('#bulkReturnForm').on('submit', function(e) {
            let valid = true;
            $('#returnSelectedList input[name="returned_by[]"]').each(function() {
                if (!$(this).val().trim()) {
                    $(this).addClass('is-invalid');
                    $(this).siblings('.text-danger').show();
                    valid = false;
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).siblings('.text-danger').hide();
                }
            });
            if (!valid) {
                e.preventDefault();
                return false;
            }
            e.preventDefault();
            // You can add a confirmation dialog here if needed
            let formData = $(this).serializeArray();
            let selectedIds = [];
            $('input[name="borrowed_ids[]"]').each(function() {
                selectedIds.push($(this).val());
            });
            $.ajax({
                url: '', // same page
                type: 'POST',
                data: $(this).serialize(), // send all form fields
                dataType: 'json',
                success: function(response) {
                    $('#returnSelectedModal').modal('hide');
                    if (response.status === 'success') {
                        Swal.fire('Returned!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        });
    });
    </script>
</body>
</html>