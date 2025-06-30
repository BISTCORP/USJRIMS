<?php
session_start();
// Move all AJAX handlers to the very top, before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'config.php';
    header('Content-Type: application/json');

    // Handle delete selected IDs (single/multiple)
    if (isset($_POST['delete_selected_ids'])) {
        $delete_ids = $_POST['delete_selected_ids'];
        if (is_array($delete_ids)) {
            $delete_ids = array_map('intval', $delete_ids); // Sanitize input
            $ids_string = implode(',', $delete_ids);

            // Fetch reservation info for audit trail
            $res = mysqli_query($conn, "SELECT reservation_code, requested_by, product_name FROM reservation WHERE req_id IN ($ids_string)");
            $infos = [];
            while ($row = mysqli_fetch_assoc($res)) {
                $infos[] = "Code {$row['reservation_code']}, Product: {$row['product_name']}, Requested by: {$row['requested_by']}";
            }

            // Perform delete query
            $deleteQuery = "DELETE FROM reservation WHERE req_id IN ($ids_string)";
            if (mysqli_query($conn, $deleteQuery)) {
                // Audit trail notification for multiple delete
                if (!empty($infos)) {
                    $user_id = $_SESSION['user_id'] ?? null;
                    $notif_message = "Reservations deleted: " . implode("; ", $infos);
                    $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at) VALUES (?, ?, 0, 'unread', NOW())";
                    $notif_stmt = $conn->prepare($notif_query);
                    $notif_stmt->bind_param('is', $user_id, $notif_message);
                    $notif_stmt->execute();
                    $notif_stmt->close();
                }
                echo json_encode(["status" => "success", "message" => "Selected reservations deleted successfully!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error deleting records: " . mysqli_error($conn)]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid ID list"]);
        }
        exit();
    }

    // Handle edit selected IDs (single/multiple)
    if (isset($_POST['edit_selected_ids']) && isset($_POST['edit_data'])) {
        $edit_ids = $_POST['edit_selected_ids'];
        $edit_data = $_POST['edit_data']; // associative array: field => value
        if (is_array($edit_ids) && is_array($edit_data)) {
            $edit_ids = array_map('intval', $edit_ids);
            $ids_string = implode(',', $edit_ids);

            // Fetch old values for audit
            $res = mysqli_query($conn, "SELECT req_id, reservation_code, requested_by, product_name, status, checked_by, product_qty FROM reservation WHERE req_id IN ($ids_string)");
            $old_rows = [];
            while ($row = mysqli_fetch_assoc($res)) {
                $old_rows[$row['req_id']] = $row;
            }

            // Build SET part dynamically
            $set_parts = [];
            $params = [];
            $types = '';
            foreach ($edit_data as $field => $value) {
                $set_parts[] = "`$field` = ?";
                $params[] = $value;
                $types .= 's';
            }
            $set_sql = implode(', ', $set_parts);

            // Prepare the statement
            $sql = "UPDATE reservation SET $set_sql WHERE req_id IN ($ids_string)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                // Bind params dynamically
                $stmt->bind_param($types, ...$params);
                if ($stmt->execute()) {
                    // Audit trail notification for edit (all changes in one message)
                    $user_id = $_SESSION['user_id'] ?? null;
                    $changes = [];
                    foreach ($edit_ids as $id) {
                        if (isset($old_rows[$id])) {
                            $row = $old_rows[$id];
                            $change = [];
                            foreach ($edit_data as $field => $value) {
                                if (isset($row[$field]) && $row[$field] != $value) {
                                    $change[] = "$field: '{$row[$field]}' → '$value'";
                                }
                            }
                            if (!empty($change)) {
                                $changes[] = "ID $id, Code {$row['reservation_code']}, Product: {$row['product_name']}, Requested by: {$row['requested_by']} (" . implode("; ", $change) . ")";
                            }
                        }
                    }
                    if (!empty($changes)) {
                        $notif_message = "Reservations updated: " . implode(" | ", $changes);
                        $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at) VALUES (?, ?, 0, 'unread', NOW())";
                        $notif_stmt = $conn->prepare($notif_query);
                        $notif_stmt->bind_param('is', $user_id, $notif_message);
                        $notif_stmt->execute();
                        $notif_stmt->close();
                    }
                    echo json_encode(["status" => "success", "message" => "Selected reservations updated successfully!"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error updating records: " . $stmt->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid data for edit"]);
        }
        exit();
    }

    // Handle edit selected IDs (single/multiple) from bulk edit modal
    if (isset($_POST['bulk_edit_selected_ids']) && isset($_POST['bulk_edit_status'])) {
        $edit_ids = $_POST['bulk_edit_selected_ids'];
        $bulk_status = $_POST['bulk_edit_status'];
        $bulk_checked_by = isset($_POST['bulk_edit_checked_by']) ? $_POST['bulk_edit_checked_by'] : null;
        $bulk_qty = isset($_POST['bulk_edit_qty']) ? $_POST['bulk_edit_qty'] : null;

        if (is_array($edit_ids) && $bulk_status) {
            $edit_ids = array_map('intval', $edit_ids);
            $ids_string = implode(',', $edit_ids);

            // Fetch old values for audit and for possible move to borrowed_items
            $res = mysqli_query($conn, "SELECT * FROM reservation WHERE req_id IN ($ids_string)");
            $old_rows = [];
            while ($row = mysqli_fetch_assoc($res)) {
                $old_rows[$row['req_id']] = $row;
            }

            $set_parts = [];
            $params = [];
            $types = '';

            $set_parts[] = "`status` = ?";
            $params[] = $bulk_status;
            $types .= 's';

            if ($bulk_checked_by !== null) {
                $set_parts[] = "`checked_by` = ?";
                $params[] = $bulk_checked_by;
                $types .= 's';
            }
            if ($bulk_qty !== null) {
                $set_parts[] = "`product_qty` = ?";
                $params[] = intval($bulk_qty);
                $types .= 'i';
            }

            $set_sql = implode(', ', $set_parts);

            $sql = "UPDATE reservation SET $set_sql WHERE req_id IN ($ids_string)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param($types, ...$params);
                if ($stmt->execute()) {
                    // Audit trail notification for bulk edit
                    $user_id = $_SESSION['user_id'] ?? null;
                    $changes = [];
                    $moved_ids = [];
                    foreach ($edit_ids as $id) {
                        if (isset($old_rows[$id])) {
                            $row = $old_rows[$id];
                            $change = [];
                            if ($row['status'] != $bulk_status) {
                                $change[] = "Status: '{$row['status']}' → '$bulk_status'";
                            }
                            if ($bulk_checked_by !== null && $row['checked_by'] != $bulk_checked_by) {
                                $change[] = "Checked By: '{$row['checked_by']}' → '$bulk_checked_by'";
                            }
                            if ($bulk_qty !== null && $row['product_qty'] != $bulk_qty) {
                                $change[] = "Quantity: {$row['product_qty']} → $bulk_qty";
                            }
                            if (!empty($change)) {
                                $changes[] = "Reservation updated (ID $id, Code {$row['reservation_code']}, Product: {$row['product_name']}, Requested by: {$row['requested_by']}): " . implode("; ", $change);
                            }

                            // If status is being set to 'borrowed', move to borrowed_items and delete from reservation
                            if ($row['status'] != 'borrowed' && $bulk_status === 'borrowed') {
                                $reservation_code = $row['reservation_code'];
                                $requested_by = $row['requested_by'];
                                $product_id = $row['product_id'];
                                $product_name = $row['product_name'];
                                $image = $row['image'];
                                $product_qty = $bulk_qty !== null ? $bulk_qty : $row['product_qty'];
                                $checked_by = $bulk_checked_by !== null ? $bulk_checked_by : $row['checked_by'];
                                $status = 'borrowed';
                                $date_borrowed = date('Y-m-d H:i:s');

                                try {
                                    $subtotal = $product_qty * $unit_price;
                                    $insert = $conn->prepare("INSERT IGNORE INTO borrowed_items (
                                        reservation_code, requested_by, product_id, image, product_name, date_borrowed, status, product_qty, unit_price, subtotal, checked_by
                                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                    $insert->bind_param(
                                        "ssissssidds",
                                        $reservation_code,
                                        $requested_by,
                                        $product_id,
                                        $image,
                                        $product_name,
                                        $date_borrowed,
                                        $status,
                                        $product_qty,
                                        $unit_price,
                                        $subtotal,
                                        $checked_by
                                    );
                                    if (!$insert->execute()) {
                                        throw new Exception("Execute failed: " . $insert->error);
                                    }
                                    // 4. Delete from reservation
                                    $conn->query("DELETE FROM reservation WHERE req_id = $id");
                                    $moved_ids[] = $id;

                                    // Add notification for status change to borrowed
                                    $notif_message = "Reservation status changed: Code $reservation_code, Product: $product_name, Requested by: $requested_by from '{$row['status']}' to 'borrowed'";
                                    $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at, reservation_code, product_id) VALUES (?, ?, 0, 'unread', NOW(), ?, ?)";
                                    $notif_stmt = $conn->prepare($notif_query);
                                    $notif_stmt->bind_param('isss', $user_id, $notif_message, $reservation_code, $product_id);
                                    $notif_stmt->execute();
                                    $notif_stmt->close();

                                    // Audit trail notification for move to borrowed
                                    $audit_message = "[AUDIT] Reservation moved to borrowed_items: Code $reservation_code, Product: $product_name, Requested by: $requested_by, Qty: $product_qty, Checked by: $checked_by";
                                    $audit_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at, reservation_code, product_id) VALUES (?, ?, 0, 'unread', NOW(), ?, ?)";
                                    $audit_stmt = $conn->prepare($audit_query);
                                    $audit_stmt->bind_param('isss', $user_id, $audit_message, $reservation_code, $product_id);
                                    $audit_stmt->execute();
                                    $audit_stmt->close();
                                } catch (Exception $e) {
                                    $errors[] = "Error for $product_name (ID $product_id): " . $e->getMessage();
                                }
                            }
                        }
                    }
                    // Audit trail notification for bulk edit (all changes)
                    if (!empty($changes)) {
                        $audit_bulk_message = "[AUDIT] Bulk edit: " . implode(" | ", $changes);
                        $audit_bulk_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at) VALUES (?, ?, 0, 'unread', NOW())";
                        $audit_bulk_stmt = $conn->prepare($audit_bulk_query);
                        $audit_bulk_stmt->bind_param('is', $user_id, $audit_bulk_message);
                        $audit_bulk_stmt->execute();
                        $audit_bulk_stmt->close();
                        foreach ($changes as $notif_message) {
                            $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at) VALUES (?, ?, 0, 'unread', NOW())";
                            $notif_stmt = $conn->prepare($notif_query);
                            $notif_stmt->bind_param('is', $user_id, $notif_message);
                            $notif_stmt->execute();
                            $notif_stmt->close();
                        }
                    }
                    $msg = "Selected reservations updated successfully!";
                    if (!empty($moved_ids)) {
                        $msg .= " " . count($moved_ids) . " moved to borrowed_items.";
                    }
                    echo json_encode(["status" => "success", "message" => $msg]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error updating records: " . $stmt->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid data for bulk edit"]);
        }
        exit();
    }

    // Handle move to borrowed_items and delete reservation
    if (isset($_POST['move_to_borrowed_id'])) {
        $reservation_id = intval($_POST['move_to_borrowed_id']);
        // Fetch reservation details
        $res = mysqli_query($conn, "SELECT * FROM reservation WHERE req_id = $reservation_id");
        if ($row = mysqli_fetch_assoc($res)) {
            $reservation_code = $row['reservation_code'];
            $requested_by = $row['requested_by'];
            $product_id = $row['product_id'];
            $product_name = $row['product_name'];
            $image = $row['image'];
            $product_qty = $row['product_qty'];
            $unit_price = $row['unit_price'];
            $checked_by = $row['checked_by'];
            $status = 'borrowed';
            $date_borrowed = date('Y-m-d H:i:s');

            try {
                $conn->begin_transaction();
                // Check stock
                $check_sql = "SELECT quantity_in_stock FROM inventory_products WHERE product_id = ? FOR UPDATE";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $product_id);
                $check_stmt->execute();
                $check_stmt->bind_result($available_stock);
                if (!$check_stmt->fetch()) {
                    $check_stmt->close();
                    $conn->rollback();
                    throw new Exception("Product ID $product_id does not exist in inventory_products table.");
                }
                $check_stmt->close();
                // Always compare reserved quantity with quantity_in_stock, ignore reorder_level
                if ($available_stock < $product_qty) {
                    $conn->rollback();
                    throw new Exception("Not enough stock for $product_name (ID $product_id). Available: $available_stock, Requested: $product_qty");
                }
                // If reserved qty equals available stock, set new stock to 0
                if ($product_qty == $available_stock) {
                    $update = $conn->prepare("UPDATE inventory_products SET quantity_in_stock = 0 WHERE product_id = ?");
                    $update->bind_param("i", $product_id);
                } else {
                    $update = $conn->prepare("UPDATE inventory_products SET quantity_in_stock = quantity_in_stock - ? WHERE product_id = ? AND quantity_in_stock >= ?");
                    $update->bind_param("iii", $product_qty, $product_id, $product_qty);
                }
                $update->execute();
                if ($update->affected_rows === 0) {
                    $update->close();
                    $conn->rollback();
                    throw new Exception("Failed to update inventory stock for product ID $product_id. Not enough stock.");
                }
                $update->close();
                // Insert into borrowed_items
                $subtotal = $product_qty * $unit_price;
                // Get reservation_date from the reservation row if available
                $reservation_date = isset($row['reservation_date']) ? $row['reservation_date'] : null;
                $insert = $conn->prepare("INSERT INTO borrowed_items (
                    reservation_code, requested_by, product_id, image, product_name, date_borrowed, reservation_date, status, product_qty, unit_price, subtotal, checked_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $insert->bind_param(
                    "ssisssssidds",
                    $reservation_code,
                    $requested_by,
                    $product_id,
                    $image,
                    $product_name,
                    $date_borrowed,
                    $reservation_date,
                    $status,
                    $product_qty,
                    $unit_price,
                    $subtotal,
                    $checked_by
                );
                if (!$insert->execute()) {
                    $insert->close();
                    $conn->rollback();
                    throw new Exception("Failed to insert into borrowed_items: " . $insert->error);
                }
                $insert->close();
                // Delete from reservation
                $delete = $conn->prepare("DELETE FROM reservation WHERE req_id = ?");
                $delete->bind_param("i", $reservation_id);
                if (!$delete->execute()) {
                    $delete->close();
                    $conn->rollback();
                    throw new Exception("Failed to delete from reservation: " . $delete->error);
                }
                $delete->close();
                $conn->commit();
                echo json_encode(["status" => "success", "message" => "Moved to borrowed_items and deleted from reservation. Stock updated."]);
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Reservation not found."]);
        }
        exit();
    }

    // --- REPLACE bulk_edit_to_borrowed handler with improved batch stock check ---
    if (isset($_POST['bulk_edit_to_borrowed'])) {
        $edit_ids = $_POST['bulk_edit_selected_ids'] ?? [];
        $reservation_codes = $_POST['reservation_code'] ?? [];
        $requested_bys = $_POST['requested_by'] ?? [];
        $product_ids = $_POST['product_id'] ?? [];// Update reservation quantity and checked_by in DB to match modal values
$modal_qty = intval($product_qtys[$i]);
$modal_checked_by = $checked_bys[$i];
$update_res = $conn->prepare("UPDATE reservation SET product_qty = ?, checked_by = ? WHERE req_id = ?");
$update_res->bind_param("isi", $modal_qty, $modal_checked_by, $req_id);
$update_res->execute();
$update_res->close();$res_qty_check = $conn->query("SELECT product_qty FROM reservation WHERE req_id = $req_id");
$product_qty = ($res_qty_check && $res_qty_check->num_rows > 0) ? intval($res_qty_check->fetch_assoc()['product_qty']) : 0;
$checked_by = $modal_checked_by;$modal_qty = intval($product_qtys[$i]);
$modal_checked_by = $checked_bys[$i];
$update_res = $conn->prepare("UPDATE reservation SET product_qty = ?, checked_by = ? WHERE req_id = ?");
$update_res->bind_param("isi", $modal_qty, $modal_checked_by, $req_id);
$update_res->execute();
$update_res->close();$res_qty_check = $conn->query("SELECT product_qty FROM reservation WHERE req_id = $req_id");
$product_qty = ($res_qty_check && $res_qty_check->num_rows > 0) ? intval($res_qty_check->fetch_assoc()['product_qty']) : 0;
$checked_by = $modal_checked_by;// 1. Update reservation with modal value before deduction
$modal_qty = intval($product_qtys[$i]);
$modal_checked_by = $checked_bys[$i];
$update_res = $conn->prepare("UPDATE reservation SET product_qty = ?, checked_by = ? WHERE req_id = ?");
$update_res->bind_param("isi", $modal_qty, $modal_checked_by, $req_id);
$update_res->execute();
$update_res->close();
// Only block if reserved quantity exceeds available stock
if ($product_qty > $available_stock) {
$conn->rollback();
throw new Exception("Reserved quantity cannot exceed available stock. ($product_name: Reserved $product_qty, Available $available_stock)");
}
// Allow deduction if reserved quantity is less than or equal to available stock (including zero)
// No check for reorder_level; deduction is based solely on quantity_in_stock
// 2. Fetch the updated value from the database
$res_qty_check = $conn->query("SELECT product_qty FROM reservation WHERE req_id = $req_id");
$product_qty = ($res_qty_check && $res_qty_check->num_rows > 0) ? intval($res_qty_check->fetch_assoc()['product_qty']) : 0;
$checked_by = $modal_checked_by;

// 3. Use $product_qty for deduction and move to borrowed
        $images = $_POST['image'] ?? [];
        $product_names = $_POST['product_name'] ?? [];
        $date_borroweds = $_POST['date_borrowed'] ?? [];
        $statuses = $_POST['status'] ?? [];
        $product_qtys = $_POST['product_qty'] ?? [];
    for ($i = 0; $i < count($edit_ids); $i++) {
    $req_id = intval($edit_ids[$i]);
    $reservation_code = $reservation_codes[$i];
    $requested_by = $requested_bys[$i];
    $product_id = intval($product_ids[$i]);
    $image = $images[$i];
    $product_name = $product_names[$i];
    $date_borrowed = $date_borroweds[$i];
    $status = 'borrowed';
    $unit_price = floatval($unit_prices[$i]);
    $modal_qty = intval($product_qtys[$i]);
    $modal_checked_by = $checked_bys[$i];

    // Update reservation with modal value before deduction
    $update_res = $conn->prepare("UPDATE reservation SET product_qty = ?, checked_by = ? WHERE req_id = ?");
    $update_res->bind_param("isi", $modal_qty, $modal_checked_by, $req_id);
    $update_res->execute();
    $update_res->close();

    // Fetch the updated value from the database
    $res_qty_check = $conn->query("SELECT product_qty FROM reservation WHERE req_id = $req_id");
    $product_qty = ($res_qty_check && $res_qty_check->num_rows > 0) ? intval($res_qty_check->fetch_assoc()['product_qty']) : 0;
    $checked_by = $modal_checked_by;

    // ... continue with deduction and move logic ...
}    $unit_prices = $_POST['unit_price'] ?? [];
        $checked_bys = $_POST['checked_by'] ?? [];

        $errors = [];
        $success = 0;

        // 1. Sum total requested qty per product_id in the batch
        $product_totals = [];
        for ($i = 0; $i < count($edit_ids); $i++) {
            $pid = intval($product_ids[$i]);
            $qty = intval($product_qtys[$i]);
            if (!isset($product_totals[$pid])) $product_totals[$pid] = 0;
            $product_totals[$pid] += $qty;
        }

        // 2. Check stock for all products before processing
        $insufficient = [];
        foreach ($product_totals as $pid => $total_qty) {
            $inv = $conn->query("SELECT product_name, quantity_in_stock FROM inventory_products WHERE product_id = $pid");
            $row = $inv ? $inv->fetch_assoc() : null;
            $available = $row ? intval($row['quantity_in_stock']) : 0;
            if ($available < $total_qty) {
                $pname = $row ? $row['product_name'] : "Product ID $pid";
                $insufficient[] = "$pname (ID $pid): Available $available, Requested $total_qty";
            }
        }
        if (!empty($insufficient)) {
            echo json_encode([
                "status" => "error",
                "message" => "Not enough stock for: " . implode("; ", $insufficient)
            ]);
            exit();
        }

        // 3. All stock checks passed, process each reservation
        for ($i = 0; $i < count($edit_ids); $i++) {
            $req_id = intval($edit_ids[$i]);
            $reservation_code = $reservation_codes[$i];
            $requested_by = $requested_bys[$i];
            $product_id = intval($product_ids[$i]);
            $image = $images[$i];
            $product_name = $product_names[$i];
            $date_borrowed = $date_borroweds[$i];
            $status = 'borrowed';
            // Update reservation with modal value before deduction
            $modal_qty = intval($product_qtys[$i]);
            $modal_checked_by = $checked_bys[$i];
            $update_res = $conn->prepare("UPDATE reservation SET product_qty = ?, checked_by = ? WHERE req_id = ?");
            $update_res->bind_param("isi", $modal_qty, $modal_checked_by, $req_id);
            $update_res->execute();
            $update_res->close();
            // Fetch the updated value from the database
            $res_qty_check = $conn->query("SELECT product_qty FROM reservation WHERE req_id = $req_id");
            $product_qty = ($res_qty_check && $res_qty_check->num_rows > 0) ? intval($res_qty_check->fetch_assoc()['product_qty']) : 0;
            $unit_price = floatval($unit_prices[$i]);
            $checked_by = $modal_checked_by;

            try {
                // --- Fix: Validate product_id ---
                if ($product_id <= 0) {
                    throw new Exception("Invalid product ID ($product_id) for $product_name. Skipped.");
                }

                // Start transaction for atomicity
                $conn->begin_transaction();

                // Check if product_id exists in inventory_products
                $check_sql = "SELECT quantity_in_stock FROM inventory_products WHERE product_id = ? FOR UPDATE";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $product_id);
                $check_stmt->execute();
                $check_stmt->bind_result($available_stock);
                if (!$check_stmt->fetch()) {
                    $check_stmt->close();
                    $conn->rollback();
                    throw new Exception("Product ID $product_id does not exist in inventory_products table.");
                }
                $check_stmt->close();

                // Always compare reserved quantity with quantity_in_stock, ignore reorder_level
                if ($available_stock < $product_qty) {
                    $conn->rollback();
                    throw new Exception("Not enough stock for $product_name (ID $product_id). Available: $available_stock, Requested: $product_qty");
                }
                // If reserved qty equals available stock, set new stock to 0
                if ($product_qty == $available_stock) {
                    $update = $conn->prepare("UPDATE inventory_products SET quantity_in_stock = 0 WHERE product_id = ?");
                    $update->bind_param("i", $product_id);
                } else {
                    $update = $conn->prepare("UPDATE inventory_products SET quantity_in_stock = quantity_in_stock - ? WHERE product_id = ? AND quantity_in_stock >= ?");
                    $update->bind_param("iii", $product_qty, $product_id, $product_qty);
                }
                $update->execute();
                if ($update->affected_rows === 0) {
                    $update->close();
                    $conn->rollback();
                    throw new Exception("Failed to update inventory stock for product ID $product_id. Not enough stock. Tried to deduct $product_qty from $available_stock.");
                }
                $update->close();

                // Calculate subtotal
                $subtotal = $product_qty * $unit_price;

                // Duplicate check removed: allow multiple borrowed entries with same reservation_code and product_id

                // Fetch reservation_date for this reservation
                $reservation_date = null;
                $res_date = $conn->query("SELECT reservation_date FROM reservation WHERE req_id = $req_id");
                if ($res_date && $res_date->num_rows > 0) {
                    $reservation_date = $res_date->fetch_assoc()['reservation_date'];
                }

                // Insert into borrowed_items with reservation_date
                $insert = $conn->prepare("INSERT INTO borrowed_items (
                    reservation_code, requested_by, product_id, image, product_name, date_borrowed, reservation_date, status, product_qty, unit_price, subtotal, checked_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $insert->bind_param(
                    "ssisssssidds",
                    $reservation_code,
                    $requested_by,
                    $product_id,
                    $image,
                    $product_name,
                    $date_borrowed,
                    $reservation_date,
                    $status,
                    $product_qty,
                    $unit_price,
                    $subtotal,
                    $checked_by
                );
                if (!$insert->execute()) {
                    $insert->close();
                    $conn->rollback();
                    throw new Exception("Failed to insert into borrowed_items: " . $insert->error);
                }
                $insert->close();

                // Delete from reservation
                $delete = $conn->prepare("DELETE FROM reservation WHERE req_id = ?");
                $delete->bind_param("i", $req_id);
                if (!$delete->execute()) {
                    $delete->close();
                    $conn->rollback();
                    throw new Exception("Failed to delete from reservation: " . $delete->error);
                }
                $delete->close();

                // Audit trail notification
                $user_id = $_SESSION['user_id'] ?? null;
                $audit_message = "[AUDIT] Reservation moved to borrowed: Code $reservation_code, Product: $product_name, Requested by: $requested_by, Qty: $product_qty, Checked by: $checked_by";
                $audit_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at, reservation_code, product_id) VALUES (?, ?, 0, 'unread', NOW(), ?, ?)";
                $audit_stmt = $conn->prepare($audit_query);
                $audit_stmt->bind_param('issi', $user_id, $audit_message, $reservation_code, $product_id);
                $audit_stmt->execute();
                $audit_stmt->close();

                $conn->commit();
                $success++;
            } catch (Exception $e) {
                $conn->rollback();
                $errors[] = "Error for $product_name (ID $product_id): " . $e->getMessage();
            }
        }

        // Always return status: success if at least one was moved
        if ($success > 0) {
            echo json_encode([
                "status" => "success",
                "message" => "$success reservation(s) moved to borrowed_items." . (!empty($errors) ? " Some errors: " . implode(" ", $errors) : "")
            ]);
        } else {
            // Only error if nothing was moved
            echo json_encode([
                "status" => "error",
                "message" => "No reservations were moved. " . implode(" ", $errors)
            ]);
        }
        exit();
    }

    // New AJAX handler for getting available quantity
    if (isset($_POST['get_available_qty']) && isset($_POST['product_id'])) {
        include 'config.php';
        $product_id = intval($_POST['product_id']);
        $res = mysqli_query($conn, "SELECT quantity_in_stock FROM inventory_products WHERE product_id = $product_id");
        $row = mysqli_fetch_assoc($res);
        echo json_encode(['available_qty' => $row ? $row['quantity_in_stock'] : 0]);
        exit();
    }

    // --- Add this handler above your existing bulk edit handlers ---

    if (isset($_POST['bulk_edit_reservation_update'])) {
        $edit_ids = $_POST['bulk_edit_selected_ids'] ?? [];
        $qtys = $_POST['product_qty'] ?? [];
        $checked_bys = $_POST['checked_by'] ?? [];
        $status = $_POST['bulk_status'] ?? '';
        $success = 0;
        $errors = [];

        if (is_array($edit_ids) && $status) {
            for ($i = 0; $i < count($edit_ids); $i++) {
                $id = intval($edit_ids[$i]);
                $qty = isset($qtys[$i]) ? intval($qtys[$i]) : null;
                $checked_by = isset($checked_bys[$i]) ? $checked_bys[$i] : null;

                $set = "`status` = ?";
                $params = [$status];
                $types = 's';

                if ($checked_by !== null) {
                    $set .= ", `checked_by` = ?";
                    $params[] = $checked_by;
                    $types .= 's';
                }
                if ($qty !== null) {
                    $set .= ", `product_qty` = ?";
                    $params[] = $qty;
                    $types .= 'i';
                }

                $sql = "UPDATE reservation SET $set WHERE req_id = ?";
                $params[] = $id;
                $types .= 'i';

                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param($types, ...$params);
                    if ($stmt->execute()) {
                        $success++;
                    } else {
                        $errors[] = "Failed to update reservation ID $id.";
                    }
                    $stmt->close();
                } else {
                    $errors[] = "Prepare failed for reservation ID $id.";
                }
            }
            if ($success > 0) {
                echo json_encode(["status" => "success", "message" => "$success reservation(s) updated."]);
            } else {
                echo json_encode(["status" => "error", "message" => implode(" ", $errors)]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid data for bulk edit"]);
        }
        exit();
    }

    // Utility: Find reservation or borrowed items with invalid product_id
    if (isset($_POST['find_invalid_product_ids'])) {
        $invalid_res = [];
        $invalid_borrowed = [];

        // Find invalid product_ids in reservation
        $sql1 = "SELECT r.product_id FROM reservation r LEFT JOIN inventory_products p ON r.product_id = p.product_id WHERE p.product_id IS NULL";
        $result1 = $conn->query($sql1);
        while ($row = $result1->fetch_assoc()) {
            $invalid_res[] = $row['product_id'];
        }

        // Find invalid product_ids in borrowed_items
        $sql2 = "SELECT b.product_id FROM borrowed_items b LEFT JOIN inventory_products p ON b.product_id = p.product_id WHERE p.product_id IS NULL";
        $result2 = $conn->query($sql2);
        while ($row = $result2->fetch_assoc()) {
            $invalid_borrowed[] = $row['product_id'];
        }

        // Output as plain text for easy copy/paste
        header('Content-Type: text/plain');
        if (!empty($invalid_res)) {
            echo "Invalid product_id(s) in reservation: " . implode(", ", $invalid_res) . "\n";
        }
        if (!empty($invalid_borrowed)) {
            echo "Invalid product_id(s) in borrowed_items: " . implode(", ", $invalid_borrowed) . "\n";
        }
        if (empty($invalid_res) && empty($invalid_borrowed)) {
            echo "No invalid product_id found in reservation or borrowed_items.\n";
        }
        exit();
    }

    // Add new AJAX handler for reorder level
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_reorder_level']) && isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        $res = mysqli_query($conn, "SELECT reorder_level FROM inventory_products WHERE product_id = $product_id");
        $row = mysqli_fetch_assoc($res);
        echo json_encode(['reorder_level' => $row ? $row['reorder_level'] : '']);
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
    <title>Reservation Items</title>
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
                        <h4 class="card-title">Reservation Items</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 d-flex align-items-center" style="gap: 1rem;">
                            <button type="button" class="btn btn-danger" id="deleteSelectedBtn" style="display:none;">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                            <button type="button" class="btn btn-warning" id="editSelectedBtn" style="display:none;">
                                <i class="fas fa-edit"></i> Edit Selected
                            </button>
                            <div class="form-inline" style="margin-bottom:0;">
                                <label for="statusFilter" class="mr-2 font-semibold">Filter by Status:</label>
                                <select id="statusFilter" class="form-control">
                                    <option value="">All</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="borrowed">Borrowed</option>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive m-b-50">
                            <table class="table table-bordered" id="reportTable">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAll"></th>
                                        <th>Req ID</th>
                                        <th>Reservation Code</th>
                                        <th>Requested By</th>
                                        <th>Product ID</th>
                                        <th>Image</th>
                                        <th>Product Name</th>
                                        <th>Reservation Date</th>
                                        <th>Reservation Timeslot</th>
                                        <th>Status</th>
                                        <th>Reservation Qty</th>
                                        <th>Unit Price</th>
                                        <th>Subtotal</th>
                                        <th>Checked By</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $query = "SELECT `req_id`, `reservation_code`, `requested_by`, `product_id`, `image`, `product_name`, `reservation_date`, `reservation_timeslot`, `status`, `product_qty`, `unit_price`, `subtotal`, `checked_by`, `created_at` FROM `reservation` ORDER BY req_id DESC";
                                $result = mysqli_query($conn, $query);
                                if ($result) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $req_id = htmlspecialchars($row['req_id']);
                                        $reservation_code = htmlspecialchars($row['reservation_code']);
                                        $requested_by = htmlspecialchars($row['requested_by']);
                                        $product_id = htmlspecialchars($row['product_id']);
                                        $image = htmlspecialchars($row['image']);
                                        $product_name = htmlspecialchars($row['product_name']);
                                        $reservation_date = htmlspecialchars($row['reservation_date']);
                                        $reservation_timeslot = htmlspecialchars($row['reservation_timeslot']);
                                        $status = htmlspecialchars($row['status']);
                                        $product_qty = htmlspecialchars($row['product_qty']);
                                        $unit_price = htmlspecialchars($row['unit_price']);
                                        $subtotal = htmlspecialchars($row['subtotal']);
                                        $checked_by = htmlspecialchars($row['checked_by']);
                                        $created_at = htmlspecialchars($row['created_at']);

                                        // Status badge color
                                        $status_lc = strtolower($status);
                                        $badgeClass = 'badge-secondary';
                                        if ($status_lc === 'pending') $badgeClass = 'badge-warning';
                                        elseif ($status_lc === 'approved') $badgeClass = 'badge-success';
                                        elseif ($status_lc === 'borrowed') $badgeClass = 'badge-info';

                                        echo "<tr>
                                            <td><input type='checkbox' class='row-select' value='$req_id'
                                                data-req_id='$req_id'
                                                data-reservation_code='$reservation_code'
                                                data-requested_by='$requested_by'
                                                data-product_id='$product_id'
                                                data-image='$image'
                                                data-product_name='$product_name'
                                                data-reservation_date='$reservation_date'
                                                data-reservation_timeslot='$reservation_timeslot'
                                                data-status='$status'
                                                data-product_qty='$product_qty'
                                                data-unit_price='$unit_price'
                                                data-subtotal='$subtotal'
                                                data-checked_by='$checked_by'
                                                data-created_at='$created_at'
                                            ></td>
                                            <td>$req_id</td>
                                            <td>$reservation_code</td>
                                            <td>$requested_by</td>
                                            <td>$product_id</td>
                                            <td>";
                                        if ($image) {
                                            $imgSrc = (strpos($image, 'uploads/') === 0 || strpos($image, 'images/') === 0) ? $image : "uploads/$image";
                                            echo "<img src='$imgSrc' alt='Product Image' style='width:40px;height:40px;object-fit:cover;'>";
                                        }
                                        echo "</td>
                                            <td>$product_name</td>
                                            <td>$reservation_date</td>
                                            <td>$reservation_timeslot</td>
                                            <td><span class='badge $badgeClass' style='font-size:1em;text-transform:capitalize;'>$status</span></td>
                                            <td>$product_qty</td>
                                            <td>$unit_price</td>
                                            <td>$subtotal</td>
                                            <td>$checked_by</td>
                                            <td>$created_at</td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='15'>Error loading data: " . mysqli_error($conn) . "</td></tr>";
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

<!-- Edit selected Modal -->
<div class="modal fade" id="EditStatusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="max-width: 1100px; min-width: 500px; width: 95vw;">
        <form id="bulkEditStatusForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Selected Reservation(s)</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Modify the details for <span id="bulkEditCount"></span> selected reservation(s):</p>
                    <div class="alert alert-info py-2" style="font-size: 0.97em;">
  <strong>Note:</strong> 
  <span>
    <b>Reorder Level</b> is the minimum number of items that should be kept in stock. 
    If the available stock is equal to or lower than this level, 
    the system will not allow further deductions to prevent stock from running out.
  </span>
</div>

                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm" id="bulkEditTable">
                            <thead>
                                <tr>
                                    <th>Reservation Code</th>
                                    <th>Requested By</th>
                                    <th>Product ID</th>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Reservation Date</th><!---New Added Column fetch the reservation date of each items and insert it into the borrowed_items if the status updates--->
                                    <th>Date Borrowed</th>
                                    <th>Reservation Qty</th>
                                    <th>Available Qty</th>
                                    <th>Reorder Level</th>
                                    <th>Unit Price</th>
                                    <th>Checked By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>                  
                    <div class="form-group">
                        <label>Status</label>
                        <select name="bulk_status" id="bulk_status" class="form-control" required>
                            <option value="">-- Select Status --</option>
                            <option value="pending" selected>Pending</option>
                            <option value="approved">Approved</option>
                            <option value="borrowed">Borrowed</option>
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
            $('#editSelectedBtn').show();
        } else {
            $('#deleteSelectedBtn').hide();
            $('#editSelectedBtn').hide();
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
            text: "This will delete all selected reservations.",
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

    // Edit selected logic (open bulk edit modal and populate table)
    $('#editSelectedBtn').on('click', function() {
        var $checked = $('.row-select:checked');
        if ($checked.length === 0) return;

        // Clear previous table body
        $('#bulkEditTable tbody').empty();

        // Get current date and time in PH timezone for Date Borrowed column
        function getPHDateTimeString() {
            try {
                let now = new Date();
                let options = {
                    timeZone: 'Asia/Manila',
                    year: 'numeric', month: '2-digit', day: '2-digit',
                    hour: '2-digit', minute: '2-digit', second: '2-digit',
                    hour12: false
                };
                let parts = new Intl.DateTimeFormat('en-CA', options).formatToParts(now);
                let y = parts.find(p => p.type === 'year').value;
                let m = parts.find(p => p.type === 'month').value;
                let d = parts.find(p => p.type === 'day').value;
                let h = parts.find(p => p.type === 'hour').value;
                let min = parts.find(p => p.type === 'minute').value;
                let s = parts.find(p => p.type === 'second').value;
                return `${y}-${m}-${d} ${h}:${min}:${s}`;
            } catch (e) {
                var nowPH = new Date();
                var utc = nowPH.getTime() + (nowPH.getTimezoneOffset() * 60000);
                var offsetPH = 8;
                var nowPHDate = new Date(utc + (3600000 * offsetPH));
                return nowPHDate.toISOString().slice(0, 19).replace('T', ' ');
            }
        }
        var date_borrowed_str = getPHDateTimeString();

        // Populate modal table with selected rows
        $checked.each(function() {
            var $row = $(this).closest('tr');
            var req_id = $(this).val();
            var reservation_code = $row.find('td:eq(2)').text();
            var requested_by = $row.find('td:eq(3)').text();
            var product_id = $row.find('td:eq(4)').text();
            var image = $row.find('td:eq(5) img').attr('src') || '';
            var product_name = $row.find('td:eq(6)').text();
            var reservation_date = $row.find('td:eq(7)').text();
            var date_borrowed = date_borrowed_str;
            var qty = $row.find('td:eq(10)').text();
            var unit_price = $row.find('td:eq(11)').text();
            var checked_by = $row.find('td:eq(13)').text();

            // Fetch available qty and reorder level via AJAX (synchronously for each row)
            var available_qty = '';
            var reorder_level = '';
            $.ajax({
                url: '', // same page
                type: 'POST',
                data: { get_available_qty: 1, product_id: product_id },
                dataType: 'json',
                async: false,
                success: function(res) {
                    available_qty = res.available_qty !== undefined ? parseInt(res.available_qty, 10) : 0;
                }
            });
            $.ajax({
                url: '', // same page
                type: 'POST',
                data: { get_reorder_level: 1, product_id: product_id },
                dataType: 'json',
                async: false,
                success: function(res) {
                    reorder_level = res.reorder_level !== undefined ? parseInt(res.reorder_level, 10) : 0;
                }
            });

            var effective_available_qty = available_qty - reorder_level;
            if (effective_available_qty < 0) effective_available_qty = 0;

            var qty_val = parseInt(qty, 10);
            if (isNaN(qty_val) || qty_val < 1) qty_val = 1;
            if (qty_val > effective_available_qty) qty_val = effective_available_qty;

            $('#bulkEditTable tbody').append(
                `<tr>
                    <td>
                        <input type="hidden" name="bulk_edit_selected_ids[]" value="${req_id}">
                        <input type="text" class="form-control" value="${reservation_code}" readonly>
                        <input type="hidden" name="reservation_code[]" value="${reservation_code}">
                    </td>
                    <td><input type="text" class="form-control" value="${requested_by}" readonly>
                        <input type="hidden" name="requested_by[]" value="${requested_by}">
                    </td>
                    <td><input type="number" class="form-control" value="${product_id}" readonly>
                        <input type="hidden" name="product_id[]" value="${product_id}">
                    </td>
                    <td>
                        <img src="${image}" width="40" height="40">
                        <input type="hidden" name="image[]" value="${image}">
                    </td>
                    <td><input type="text" class="form-control" value="${product_name}" readonly>
                        <input type="hidden" name="product_name[]" value="${product_name}">
                    </td>
                    <td>
                        <input type="text" class="form-control" value="${reservation_date}" readonly>
                        <input type="hidden" name="reservation_date[]" value="${reservation_date}">
                    </td>
                    <td>
                        <input type="text" class="form-control" value="${date_borrowed}" readonly>
                        <input type="hidden" name="date_borrowed[]" value="${date_borrowed}">
                    </td>
                    <td>
                        <input type="number" name="product_qty[]" class="form-control" value="${qty_val}" min="1" max="${effective_available_qty}" required>
                    </td>
                    <td>
                        <input type="text" class="form-control" value="${effective_available_qty}" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control" value="${reorder_level}" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control" value="${unit_price}" readonly style="background-color: #e9ecef;">
                        <input type="hidden" name="unit_price[]" value="${unit_price}">
                    </td>
                    <td>
                        <input type="text" name="checked_by[]" class="form-control" value="${checked_by}" required>
                    </td>
                </tr>`
            );
        });

        // Show count
        $('#bulkEditCount').text($checked.length);

        // Show modal
        $('#EditStatusModal').modal('show');
    });

    // Handle bulk edit form submit
    $('#bulkEditStatusForm').on('submit', function(e) {
        e.preventDefault();

        // Gather all details for summary
        var detailsHtml = '<ul style="text-align:left;">';
        var infoList = [];
        $('#bulkEditTable tbody tr').each(function() {
            var code = $(this).find('input[name="reservation_code[]"]').val();
            var pname = $(this).find('input[name="product_name[]"]').val();
            var qty = $(this).find('input[name="product_qty[]"]').val();
            var checked_by = $(this).find('input[name="checked_by[]"]').val();
            infoList.push(`<li><b>Code:</b> ${code} &mdash; <b>Product:</b> ${pname} &mdash; <b>Qty:</b> ${qty} &mdash; <b>Checked By:</b> ${checked_by}</li>`);
        });
        detailsHtml += infoList.join('') + '</ul>';

        var status = $('#bulk_status').val();

        if (status === 'borrowed') {
            // Handle move to borrowed items
            Swal.fire({
                title: 'Move to Borrowed?',
                html: `<div style="font-size:1.1em;">Changing status to <b>borrowed</b> will move these reservations to the borrowed items list and remove them from reservations.<br><br><b>Details:</b>${detailsHtml}</div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, move to Borrowed',
                cancelButtonText: 'Cancel',
                width: 700
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '', // same page
                        type: 'POST',
                        data: $('#bulkEditStatusForm').serialize() + '&bulk_edit_to_borrowed=1',
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Updated!', response.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            // Fallback: If items were moved but JSON was invalid, show a generic success
                            if (xhr.responseText && xhr.responseText.indexOf('moved to borrowed_items') !== -1) {
                                Swal.fire('Updated!', 'Reservation(s) moved to borrowed items.', 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', 'An error occurred: ' + error, 'error');
                            }
                        }
                    });
                }
            });
        } else if (status === 'pending' || status === 'approved') {
            // Handle update in reservation table for pending/approved status
            Swal.fire({
                title: 'Update Reservations?',
                html: `<div style="font-size:1.1em;">This will update the selected reservations with status <b>${status}</b> and any quantity/checked by changes.<br><br><b>Details:</b>${detailsHtml}</div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, update reservations',
                cancelButtonText: 'Cancel',
                width: 700
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '', // same page
                        type: 'POST',
                        data: $('#bulkEditStatusForm').serialize() + '&bulk_edit_reservation_update=1',
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Updated!', response.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            // Fallback: If items were moved but JSON was invalid, show a generic success
                            if (xhr.responseText && xhr.responseText.indexOf('moved to borrowed_items') !== -1) {
                                Swal.fire('Updated!', 'Reservation(s) moved to borrowed items.', 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', 'An error occurred: ' + error, 'error');
                            }
                        }
                    });
                }
            });
        } else {
            Swal.fire('Error', 'Please select a valid status', 'error');
        }
    });

    // Status Filter
    $('#statusFilter').on('change', function() {
        var val = $(this).val();
        // Find the column index for "Status"
        var statusColIdx = $('#reportTable thead th').filter(function() {
            return $(this).text().trim().toLowerCase() === 'status';
        }).index();
        if (val) {
            table.column(statusColIdx).search('^' + val + '$', true, false).draw();
        } else {
            table.column(statusColIdx).search('').draw();
        }
    });
});

</script>

</body>
</html>