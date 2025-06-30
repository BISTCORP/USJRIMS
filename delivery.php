<?php
session_start();

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if (!$isAjax) {
    include 'index/header.php';
    include 'index/navigation.php';
}
//delivery.php
include 'config.php';

$productList = [];
$productResult = $conn->query("SELECT product_id, product_name, product_description, quantity_in_stock, status FROM inventory_products");
while ($row = $productResult->fetch_assoc()) {
    $productList[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery Receipt Management</title>
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <style>
        #products-container .product-row:not(:last-child) {
            margin-bottom: 10px;
        }
            /* Make the modal wider */
        #addDeliveryModal .modal-dialog {
            max-width: 900px;
        }
        /* Remove extra margin on delete button */
        #products-container .remove-product-row {
            margin-left: 0;
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
                            <h4 class="card-title">Delivery Product Lists</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 action-buttons">
                                <button class="btn btn-primary" data-toggle="modal" data-target="#addDeliveryModal">
                                    <i class="fas fa-plus"></i> Add Delivery 
                                </button>
                            </div>
                            <div class="table-responsive m-b-50">
                                <table class="table table-bordered" id="deliveryTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Client Name</th>
                                            <th>Address</th>
                                            <th>Date</th>
                                            <th>PO Number</th>
                                            <th>Contact Person</th>
                                            <th>Product Name</th>
                                            <th>Product Status</th>
                                            <th>Delivery Status</th> <!-- Add this line -->
                                            <th>Qty</th>
                                            <th>Unit</th>
                                            <th>Item Code</th>
                                            <th>Serial Number</th>
                                            <th>Prepared By</th>
                                            <th>Checked By</th>
                                            <th>Approved By</th>
                                            <th>Delivered By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $query = "SELECT 
                                        dr.id,
                                        dr.client_name,
                                        dr.address,
                                        dr.date,
                                        dr.po_number,
                                        dr.contact_person,
                                        ip.product_name,
                                        ip.status AS product_status,
                                        ip.quantity_in_stock AS product_qty,
                                        dr.status AS delivery_status,
                                        dr.qty AS delivery_qty,
                                        dr.unit,
                                        dr.item_code,
                                        dr.serial_number,
                                        dr.prepared_by,
                                        dr.checked_by,
                                        dr.approved_by,
                                        dr.delivered_by
                                    FROM delivery_receipt dr
                                    JOIN inventory_products ip ON dr.item_code = ip.product_id
                                    ORDER BY dr.id ASC";
                                    $result = mysqli_query($conn, $query);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>
                                            <td>{$row['id']}</td>
                                            <td>{$row['client_name']}</td>
                                            <td>{$row['address']}</td>
                                            <td>{$row['date']}</td>
                                            <td>{$row['po_number']}</td>
                                            <td>{$row['contact_person']}</td>
                                            <td>{$row['product_name']}</td>
                                            <td><span class='badge badge-"
                                                . ($row['product_status'] == 'Available' ? 'success' : ($row['product_status'] == 'Not Available' ? 'danger' : 'warning'))
                                                . "'>{$row['product_status']}</span></td>
                                            <td><span class='badge badge-"
                                                . ($row['delivery_status'] == 'Delivered' ? 'success' : ($row['delivery_status'] == 'Pending' ? 'warning' : 'danger'))
                                                . "'>{$row['delivery_status']}</span></td>
                                          <td>{$row['delivery_qty']}</td>
                                            <td>{$row['unit']}</td>
                                            <td>{$row['item_code']}</td>
                                            <td>{$row['serial_number']}</td>
                                            <td>{$row['prepared_by']}</td>
                                            <td>{$row['checked_by']}</td>
                                            <td>{$row['approved_by']}</td>
                                            <td>{$row['delivered_by']}</td>
                                            <td>
                                                <div class='btn-group' role='group'>
                                                    <button class='btn btn-warning btn-sm editBtn' data-toggle='modal' 
                                                        data-target='#editDeliveryModal'
                                                        data-id='{$row['id']}'
                                                        data-client_name='{$row['client_name']}'
                                                        data-address='{$row['address']}'
                                                        data-date='{$row['date']}'
                                                        data-po_number='{$row['po_number']}'
                                                        data-contact_person='{$row['contact_person']}'
                                                        data-status='{$row['delivery_status']}'
                                                        data-qty='{$row['delivery_qty']}'
                                                        data-unit='{$row['unit']}'
                                                        data-item_code='{$row['item_code']}'
                                                        data-item_description='{$row['product_name']}'
                                                        data-serial_number='{$row['serial_number']}'
                                                        data-prepared_by='{$row['prepared_by']}'
                                                        data-checked_by='{$row['checked_by']}'
                                                        data-approved_by='{$row['approved_by']}'
                                                        data-delivered_by='{$row['delivered_by']}'
                                                    >
                                                        <i class='fas fa-edit'></i>
                                                    </button>
                                                    <button class='btn btn-danger btn-sm deleteBtn' data-id='{$row['id']}'>
                                                        <i class='fas fa-trash'></i>
                                                    </button>
                                                    <button class='btn btn-info btn-sm printBtn' data-id='{$row['id']}'>
                                                        <i class='fas fa-print'></i>
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

    <!-- Add Delivery Modal -->
    <div class="modal fade" id="addDeliveryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="addDeliveryForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Delivery Receipt</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Client Name <span style="color:red">*</span></label>
                            <input type="text" name="client_name" class="form-control" required placeholder="Enter client name">
                        </div>
                        <div class="form-group">
                            <label>Address <span style="color:red">*</span></label>
                            <textarea name="address" class="form-control" required placeholder="Enter client address"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Date <span style="color:red">*</span></label>
                            <input type="date" name="date" class="form-control" required placeholder="Select delivery date">
                        </div>
                        <div class="form-group">
                            <label>Contact Person <span style="color:red">*</span></label>
                            <input type="text" name="contact_person" class="form-control" required placeholder="Enter contact person">
                        </div>
                        <div class="form-group">
                            <label>PO Number</label>
                            <input type="text" name="po_number" class="form-control" placeholder="Enter purchase order number (optional)">
                        </div>
                        <!-- Product Rows Header -->
                        <div class="form-row font-weight-bold mb-2">
                            <div class="col-md-3">Product</div>
                            <div class="col-md-2">Status</div>
                            <div class="col-md-2">Qty In Stock</div>
                            <div class="col-md-2">Order Qty</div>
                            <div class="col-md-1">Unit</div>
                            <div class="col-md-2">Serial Number</div>
                            <div class="col-md-1"></div>
                        </div>
                        <div id="products-container">
                            <div class="form-row align-items-center product-row mb-2 p-2 border rounded">
                                <div class="col-md-3">
                                    <select name="product_id[]" class="form-control product-dropdown" required>
                                        <option value="">Select Product</option>
                                        <?php foreach ($productList as $product): ?>
                                            <option 
                                                value="<?= htmlspecialchars($product['product_id']) ?>"
                                                data-status="<?= htmlspecialchars($product['status']) ?>"
                                                data-qty="<?= htmlspecialchars($product['quantity_in_stock']) ?>"
                                            >
                                                <?= htmlspecialchars($product['product_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control product-status" name="product_status[]" placeholder="Auto-filled: status" readonly>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control product-qty" name="product_qty[]" placeholder="Auto-filled: qty in stock" readonly>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" name="qty[]" placeholder="Enter quantity to deliver" min="1" required>
                                </div>
                                <div class="col-md-1">
                                    <input type="text" class="form-control" name="unit[]" placeholder="Enter unit (e.g. pcs, box)" required>
                                </div>
                                <div class="col-md-2 d-flex">
                                    <input type="text" class="form-control serial-number-input" name="serial_number[]" placeholder="Enter serial number for this product">
                                    <button type="button" class="btn btn-danger remove-product-row ml-2" style="white-space:nowrap;">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="add-product-row" class="btn btn-info mb-3">
                            Add Another Product
                        </button>
                        <div class="form-group">
                            <label>Status <span style="color:red">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="">Select Status</option>
                                <option value="Delivered">Delivered</option>
                                <option value="Pending">Pending</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Prepared By <span style="color:red">*</span></label>
                            <input type="text" name="prepared_by" class="form-control" required placeholder="Enter name of preparer">
                        </div>
                        <div class="form-group">
                            <label>Checked By <span style="color:red">*</span></label>
                            <input type="text" name="checked_by" class="form-control" required placeholder="Enter name of checker">
                        </div>
                        <div class="form-group">
                            <label>Approved By <span style="color:red">*</span></label>
                            <input type="text" name="approved_by" class="form-control" required placeholder="Enter name of approver">
                        </div>
                        <div class="form-group">
                            <label>Delivered By <span style="color:red">*</span></label>
                            <input type="text" name="delivered_by" class="form-control" required placeholder="Enter name of delivery person">
                        </div>
                        <!-- Product Name and Details -->
                          </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Add</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Delivery Modal -->
    <div class="modal fade" id="editDeliveryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" style="max-width:900px;" role="document">
            <form id="editDeliveryForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Delivery Receipt</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <!-- Other shared fields -->
                        <div class="form-group">
                            <label>Client Name <span style="color:red">*</span></label>
                            <input type="text" name="client_name" id="edit_client_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Address <span style="color:red">*</span></label>
                            <textarea name="address" id="edit_address" class="form-control" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Date <span style="color:red">*</span></label>
                            <input type="date" name="date" id="edit_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Contact Person <span style="color:red">*</span></label>
                            <input type="text" name="contact_person" id="edit_contact_person" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>PO Number</label>
                            <input type="text" name="po_number" id="edit_po_number" class="form-control">
                        </div>
                        <!-- Product Rows Header -->
                        <div class="form-row font-weight-bold mb-2">
                            <div class="col-md-3">Product</div>
                            <div class="col-md-2">Status</div>
                            <div class="col-md-2">Qty In Stock</div>
                            <div class="col-md-2">Order Qty</div>
                            <div class="col-md-1">Unit</div>
                            <div class="col-md-2">Serial Number</div>
                        </div>
                        <div id="edit-products-container">
                            <!-- Product rows will be loaded here by JS -->
                        </div>
                        <button type="button" id="edit-add-product-row" class="btn btn-info mb-3">
                            Add Another Product
                        </button>
                        <!-- Other shared fields -->
                        <div class="form-group">
                            <label>Status <span style="color:red">*</span></label>
                            <select name="status" id="edit_status" class="form-control" required>
                                <option value="">Select Status</option>
                                <option value="Delivered">Delivered</option>
                                <option value="Pending">Pending</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Prepared By <span style="color:red">*</span></label>
                            <input type="text" name="prepared_by" id="edit_prepared_by" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Checked By <span style="color:red">*</span></label>
                            <input type="text" name="checked_by" id="edit_checked_by" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Approved By <span style="color:red">*</span></label>
                            <input type="text" name="approved_by" id="edit_approved_by" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Delivered By <span style="color:red">*</span></label>
                            <input type="text" name="delivered_by" id="edit_delivered_by" class="form-control" required>
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
    var table = $('#deliveryTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "searching": true,
        "lengthChange": true
    });

    // Helper for generating product row for edit modal
    function getEditProductRow(productList, selected = {}) {
        let options = '<option value="">Select Product</option>';
        productList.forEach(function(product) {
            options += `<option value="${product.product_id}" 
                data-status="${product.status}" 
                data-qty="${product.quantity_in_stock}"
                ${selected.product_id == product.product_id ? 'selected' : ''}>
                ${product.product_name}
            </option>`;
        });
        return `
        <div class="form-row align-items-center product-row mb-2 p-2 border rounded">
            <div class="col-md-3">
                <select name="edit_product_id[]" class="form-control product-dropdown" required>
                    ${options}
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control product-status" name="edit_product_status[]" placeholder="Status" value="${selected.product_status || ''}" readonly>
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control product-qty" name="edit_product_qty[]" placeholder="Qty In Stock" value="${selected.product_qty || ''}" readonly>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="edit_qty[]" placeholder="Qty" min="1" value="${selected.qty || ''}" required>
            </div>
            <div class="col-md-1">
                <input type="text" class="form-control" name="edit_unit[]" placeholder="Unit" value="${selected.unit || ''}" required>
            </div>
            <div class="col-md-2 d-flex">
                <input type="text" class="form-control serial-number-input" name="edit_serial_number[]" placeholder="Serial Number for this product" value="${selected.serial_number || ''}">
                <button type="button" class="btn btn-danger remove-product-row ml-2" style="white-space:nowrap;">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
        `;
    }

    // Make productList available to JS
    var productList = <?php echo json_encode($productList); ?>;

    // Load data into Edit Modal (including products)
    $(document).on('click', '.editBtn', function() {
        var deliveryId = $(this).data('id');
        // Load shared fields as before...
        $('#edit_id').val(deliveryId);
        $('#edit_client_name').val($(this).data('client_name'));
        $('#edit_address').val($(this).data('address'));
        $('#edit_date').val($(this).data('date'));
        $('#edit_po_number').val($(this).data('po_number'));
        $('#edit_contact_person').val($(this).data('contact_person'));
        $('#edit_status').val($(this).data('status'));
        $('#edit_prepared_by').val($(this).data('prepared_by'));
        $('#edit_checked_by').val($(this).data('checked_by'));
        $('#edit_approved_by').val($(this).data('approved_by'));
        $('#edit_delivered_by').val($(this).data('delivered_by'));

        // AJAX: Load all product rows for this delivery
        $.getJSON('get_delivery_products.php', {id: deliveryId}, function(products) {
            var html = '';
            if (products.length === 0) {
                html = getEditProductRow(productList, {});
            } else {
                products.forEach(function(prod) {
                    html += getEditProductRow(productList, prod);
                });
            }
            $('#edit-products-container').html(html);
        });
    });

    // Delete button click
    $(document).on('click', '.deleteBtn', function() {
        var deleteId = $(this).data('id');
        var row = $(this).closest('tr');
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
                $.ajax({
                    url: 'delete_delivery.php',
                    type: 'POST',
                    data: { delete_id: deleteId },
                    success: function(response) {
                        var json = JSON.parse(response);
                        if (json.status === 'success') {
                            Swal.fire(
                                'Deleted!',
                                json.message,
                                'success'
                            );
                            // Reload the page instead of using ajax reload
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            Swal.fire(
                                'Error!',
                                json.message,
                                'error'
                            );
                        }
                    }
                });
            }
        })
    });

    // Add Delivery Form Submission
    $('#addDeliveryForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate stock before submitting
        var valid = true;
        $('input[name="qty[]"]').each(function(index) {
            var selectedQty = parseInt($(this).val());
            var availableQty = parseInt($('.product-dropdown').eq(index).find('option:selected').data('qty'));
            
            if (selectedQty > availableQty) {
                valid = false;
                Swal.fire(
                    'Error!',
                    'Quantity for product ' + (index + 1) + ' exceeds available stock (' + availableQty + ')',
                    'error'
                );
                return false; // Break out of each loop
            }
        });
        
        if (!valid) {
            return; // Stop form submission
        }
        
        $.ajax({
            url: 'add_delivery.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                var json = JSON.parse(response);
                if (json.status === 'success') {
                    Swal.fire(
                        'Added!',
                        json.message,
                        'success'
                    );
                    $('#addDeliveryModal').modal('hide');
                    $('#addDeliveryForm')[0].reset();
                    // Reload the page to show updated data
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    Swal.fire(
                        'Error!',
                        json.message,
                        'error'
                    );
                }
            }
        });
    });

    // Edit Delivery Form Submission
    $('#editDeliveryForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'update_delivery.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                var json = JSON.parse(response);
                if (json.status === 'success') {
                    Swal.fire(
                        'Updated!',
                        json.message,
                        'success'
                    );
                    $('#editDeliveryModal').modal('hide');
                    // Reload the page to show updated data
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    Swal.fire(
                        'Error!',
                        json.message,
                        'error'
                    );
                }
            }
        });
    });

    // Add new product row
    $('#add-product-row').on('click', function() {
        var row = $('#products-container .product-row:first').clone();
        row.find('select, input').val('');
        // Ensure placeholder for serial number is always set
        row.find('.serial-number-input').attr('placeholder', 'Serial Number for this product');
        $('#products-container').append(row);
    });

    // Remove product row
    $(document).on('click', '.remove-product-row', function() {
        if ($('#products-container .product-row').length > 1) {
            $(this).closest('.product-row').remove();
        }
    });

    // Autofill status and quantity on product change
    $(document).on('change', '.product-dropdown', function() {
        var selected = $(this).find('option:selected');
        var row = $(this).closest('.product-row');
        row.find('.product-status').val(selected.data('status') || '');
        row.find('.product-qty').val(selected.data('qty') || '');
    });

    // Reset form when modal is closed
    $('#addDeliveryModal').on('hidden.bs.modal', function () {
        $('#addDeliveryForm')[0].reset();
    });

    // Print button click
    $(document).on('click', '.printBtn', function() {
        var id = $(this).data('id');
        printDelivery(id);
    });

    // Separate print function
    function printDelivery(id) {
        window.open('print_delivery.php?id=' + id, '_blank');
    }

    // Add another product row in edit modal
    $('#edit-add-product-row').on('click', function() {
        $('#edit-products-container').append(getEditProductRow(productList, {}));
    });

    // Remove product row in edit modal
    $(document).on('click', '#edit-products-container .remove-product-row', function() {
        if ($('#edit-products-container .product-row').length > 1) {
            $(this).closest('.product-row').remove();
        }
    });

    // Autofill status and quantity on product change in edit modal
    $(document).on('change', '#edit-products-container .product-dropdown', function() {
        var selected = $(this).find('option:selected');
        var row = $(this).closest('.product-row');
        row.find('.product-status').val(selected.data('status') || '');
        row.find('.product-qty').val(selected.data('qty') || '');
    });
});
</script>
</body>
</html>