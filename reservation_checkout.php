<?php
session_start();
include 'config.php';

// Check if this is a direct checkout
if (isset($_GET['direct_checkout']) && $_GET['direct_checkout'] === 'true' && isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    $qty = isset($_GET['qty']) && is_numeric($_GET['qty']) && $_GET['qty'] > 0 ? (int)$_GET['qty'] : 1;

    // Get product details
    $stmt = $conn->prepare("SELECT * FROM inventory_products WHERE product_id = ? AND status = 'Available'");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();

        // Create temporary cart session if doesn't exist
        if (!isset($_SESSION['cart_session_id'])) {
            $_SESSION['cart_session_id'] = uniqid('cart_');
        }
        $session_id = $_SESSION['cart_session_id'];

        // Insert into cart temporarily with correct quantity
        $stmt = $conn->prepare("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $session_id, $product_id, $qty);
        $stmt->execute();

        // Set the cart item as selected
        $_SESSION['selected_cart_items'] = [$conn->insert_id];
        $_SESSION['direct_checkout'] = true;
    } else {
        header("Location: index.php");
        exit();
    }
}

// Redirect if no cart session
if (!isset($_SESSION['cart_session_id'])) {
    header("Location: cart.php");
    exit();
}

$session_id = $_SESSION['cart_session_id'];

// For direct checkout, get the latest added item
if (isset($_GET['direct_checkout']) && $_GET['direct_checkout'] === 'true') {
    $stmt = $conn->prepare("
        SELECT cart_id 
        FROM cart 
        WHERE session_id = ? 
        ORDER BY added_at DESC 
        LIMIT 1
    ");
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION['selected_cart_items'] = [$row['cart_id']];
        $_SESSION['direct_checkout'] = true;
    }
}

// Add this near the top of the file, after session_start()
if (isset($_SESSION['direct_checkout']) && isset($_SESSION['selected_cart_items'])) {
    // If this is a direct checkout, only get the specific cart item
    $selected_items = implode(',', array_map('intval', $_SESSION['selected_cart_items']));
} else {
    // Regular checkout - get all selected items
    if (!isset($_SESSION['selected_cart_items']) || empty($_SESSION['selected_cart_items'])) {
        header("Location: cart.php");
        exit();
    }
    $selected_items = implode(',', array_map('intval', $_SESSION['selected_cart_items']));
}

// Fetch all cart items for the session
$stmt = $conn->prepare("
    SELECT 
        c.cart_id,
        c.quantity,
        p.product_id,
        p.product_name,
        p.product_description,
        p.image,
        p.unit_price,
        p.quantity_in_stock,
        (c.quantity * p.unit_price) as subtotal
    FROM cart c
    JOIN inventory_products p ON c.product_id = p.product_id
    WHERE c.session_id = ?
    ORDER BY c.added_at DESC
");
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();
$allCartItems = $result->fetch_all(MYSQLI_ASSOC);

// Filter only selected cart items
$selected_cart_ids = array_map('intval', $_SESSION['selected_cart_items']);
$cartItems = array_values(array_filter($allCartItems, function($item) use ($selected_cart_ids) {
    return in_array($item['cart_id'], $selected_cart_ids);
}));

// Calculate totals
$subtotal = 0;
$totalItems = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['subtotal'];
    $totalItems += $item['quantity'];
}

// Handle reservation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requested_by = $_POST['requested_by'];
    $reservation_date = $_POST['reservation_date'];
    $reservation_time = $_POST['reservation_time'];
    $checked_by = !empty($_POST['checked_by']) ? $_POST['checked_by'] : null;

    // Determine start and end times based on timeslot
    $timeslot_map = [
        '08:00 AM' => ['08:00:00', '12:00:00', '08:00 AM - 12:00 PM'],
        '12:00 PM' => ['12:00:00', '18:00:00', '12:00 PM - 6:00 PM']
    ];
    $reservation_start_datetime = '';
    $reservation_timeslot_str = '';
    if ($reservation_date && $reservation_time && isset($timeslot_map[$reservation_time])) {
        $start_time = $timeslot_map[$reservation_time][0];
        $end_time = $timeslot_map[$reservation_time][1];
        $reservation_start_datetime = $reservation_date . ' ' . $start_time;
        $reservation_timeslot_str = $timeslot_map[$reservation_time][2];
    }

    $errors = [];
    if (!$requested_by) $errors[] = "Requested By is required.";
    if (!$reservation_date) $errors[] = "Reservation Date is required.";
    if (!$reservation_time) $errors[] = "Reservation Time is required.";

    if (empty($cartItems)) $errors[] = "Your cart is empty.";

    // Convert timeslot to start and end time for DB
    $reservation_timeslot = $reservation_time;
    $reservation_time_start = '';
    $reservation_time_end = '';
    if ($reservation_time === '08:00 AM') {
        $reservation_time_start = '08:00:00';
        $reservation_time_end = '12:00:00';
    } elseif ($reservation_time === '12:00 PM') {
        $reservation_time_start = '12:00:00';
        $reservation_time_end = '18:00:00';
    }

    // Save as DATETIME for reservation_date (start time)
    $reservation_datetime = '';
    if ($reservation_date && $reservation_time_start) {
        $reservation_datetime = $reservation_date . ' ' . $reservation_time_start;
    }

    if (empty($errors)) {
        confirmReservation(
            $conn,
            $cartItems,
            $requested_by,
            $reservation_start_datetime, // start datetime for reservation_date
            $reservation_timeslot_str,   // full timeslot string for reservation_timeslot
            $checked_by,
            $session_id
        );
        // No need to redirect here; confirmReservation() already handles redirection.
    }
}

function generateReservationCode() {
    return strtoupper(uniqid('RSV-'));
}

function confirmReservation($conn, $cartItems, $requested_by, $reservation_datetime, $reservation_timeslot, $checked_by, $session_id) {
    // Generate a single reservation code for all products in this checkout
    $reservation_code = generateReservationCode();
    $reservation_codes = [$reservation_code];
    try {
        $conn->begin_transaction();

        // Collect reserved products for session
        $reserved_products = [];

        // Get current time in Philippines (Asia/Manila)
        $dt_ph = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $current_time = $dt_ph->format('H:i:s');

        foreach ($cartItems as $item) {
            // Create reservation
            $product_id = $item['product_id'];
            $image = $item['image'];
            $product_name = $item['product_name'];
            $status = 'pending';
            $product_qty = $item['quantity'];
            $unit_price = $item['unit_price'];
            $subtotal = $item['subtotal'];
            // Use the same $reservation_code for all products

            // Use a dedicated statement for INSERT
            $insert_stmt = $conn->prepare("INSERT INTO reservation 
                (reservation_code, requested_by, product_id, image, product_name, reservation_date, reservation_timeslot, status, product_qty, unit_price, checked_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            // All arguments must be variables, not expressions
            $reservation_code_var = $reservation_code;
            $requested_by_var = $requested_by;
            $product_id_var = $item['product_id'];
            $image_var = $item['image'];
            $product_name_var = $item['product_name'];
            $reservation_datetime_var = $reservation_datetime;
            $reservation_timeslot_var = $reservation_timeslot;
            $status_var = $status;
            $product_qty_var = $item['quantity'];
            $unit_price_var = $item['unit_price'];
            $checked_by_var = $checked_by;

            // Overwrite $reservation_datetime to always use current date and PH time
            $reservation_date_only = '';
            if (!empty($reservation_datetime)) {
                $reservation_date_only = substr($reservation_datetime, 0, 10);
            } else {
                $reservation_date_only = $dt_ph->format('Y-m-d');
            }
            $reservation_datetime_var = $reservation_date_only . ' ' . $current_time;

            $insert_stmt->bind_param(
                "ssisssssids",
                $reservation_code_var,
                $requested_by_var,
                $product_id_var,
                $image_var,
                $product_name_var,
                $reservation_datetime_var,
                $reservation_timeslot_var,
                $status_var,
                $product_qty_var,
                $unit_price_var,
                $checked_by_var
            );
            $insert_stmt->execute();
            if ($insert_stmt->error) {
                throw new Exception("Insert error: " . $insert_stmt->error);
            }

            // Prepare notification message with reservation link
            $notification_message = "New reservation <a href='reservation_view.php?code=" . htmlspecialchars($reservation_code) . "'>#" . htmlspecialchars($reservation_code) . "</a> by " . htmlspecialchars($requested_by) . " for " . htmlspecialchars($reservation_datetime) . ".";

            // Set the user_id for the notification (NULL for admin/global notification)
            $user_id = null; // Set to a specific user ID if needed

            // Insert notification with reservation_code and product_id
            $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at, status, reservation_code, product_id) VALUES (?, ?, 0, NOW(), 'unread', ?, ?)");
            $notif_stmt->bind_param("sssi", $user_id, $notification_message, $reservation_code, $product_id);
            $notif_stmt->execute();
            $notif_stmt->close();

            // Add product info to reserved_products array
            $reserved_products[] = [
                'product_id' => $product_id,
                'product_name' => $product_name,
                'product_qty' => $product_qty,
                'unit_price' => $unit_price,
                'subtotal' => $subtotal
            ];

            // Use a dedicated statement for DELETE
            if (isset($_SESSION['direct_checkout'])) {
                $delete_stmt = $conn->prepare("DELETE FROM cart WHERE session_id = ? AND product_id = ?");
                $delete_stmt->bind_param("si", $session_id, $product_id);
            } else {
                $delete_stmt = $conn->prepare("DELETE FROM cart WHERE session_id = ? AND cart_id = ?");
                $delete_stmt->bind_param("si", $session_id, $item['cart_id']);
            }
            $delete_stmt->execute();
            if ($delete_stmt->error) {
                throw new Exception("Delete error: " . $delete_stmt->error);
            }
        }

        // Clear checkout-related session variables
        unset($_SESSION['direct_checkout']);
        unset($_SESSION['selected_cart_items']);

        $conn->commit();
        
        // Store success data and redirect
        $_SESSION['reservation_success'] = [
            'requested_by' => $requested_by,
            'reservation_date' => $reservation_datetime, // Save the combined date and time slot
            'total_items' => count($cartItems),
            'total_amount' => array_sum(array_column($cartItems, 'subtotal')),
            'timestamp' => date('Y-m-d H:i:s'),
            'products' => $reserved_products
        ];
        $_SESSION['reservation_codes'] = $reservation_codes;

        header("Location: reservation_success.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

// IMPORTANT: The reservation_code column in the reservation table must NOT be UNIQUE
// to allow multiple products to share the same reservation code in a single checkout.
?>

<?php include 'index/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reservation Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(to right, #ff6b33, #ff4563);
        }
        .orange-gradient {
            background: linear-gradient(135deg, #ff8a00 0%, #ff6b35 100%);
        }
        .card-shadow {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .input-focus:focus {
            border-color: #ff8a00;
            box-shadow: 0 0 0 3px rgba(255, 138, 0, 0.1);
        }
        .animate-bounce-slow {
            
            animation: bounce 2s infinite;
        }
        @keyframes shimmer {
            0% { background-position: -200px 0; }
            100% { background-position: calc(200px + 100%) 0; }
        }
        .shimmer {
            background: linear-gradient(90deg, #f0f0f0 0px, #e0e0e0 40px, #f0f0f0 80px);
            background-size: 200px;
            animation: shimmer 1.5s infinite;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(-5%);
                animation-timing-function: cubic-bezier(0.8,0,1,1);
            }
            50% {
                transform: translateY(0);
                animation-timing-function: cubic-bezier(0,0,0.2,1);
            }
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-orange-50 to-orange-100">
    <!-- Header Section -->
    <div class="gradient-bg py-8" style="background: linear-gradient(to right, #ff6b33, #ff4563);">
        <div class="max-w-4xl mx-auto px-4">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 mb-4 animate-bounce-slow">
                    <img src="images/icons8-shopping-bag-94.png" 
                         alt="Shopping Bag" 
                         class="w-20 h-20 object-contain">
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">Reservation Checkout</h1>
                <p class="text-orange-100 text-lg">Complete your reservation in just a few steps</p>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 -mt-8 pb-12">
        <!-- Main Card -->
        <div class="bg-white rounded-2xl card-shadow overflow-hidden">
            
            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-6 m-6 rounded-r-xl">
                    <div class="flex items-center mb-2">
                        <svg class="w-6 h-6 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-red-800">Please fix the following errors:</h3>
                    </div>
                    <?php foreach ($errors as $err): ?>
                        <div class="text-red-700 ml-9"><?= htmlspecialchars($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Cart Items Section -->
            <div class="p-8">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 2.5M7 13l2.5 2.5m6-2.5h3.5"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Your Cart Items</h2>
                </div>

                <?php if (empty($cartItems)): ?>
                    <div class="text-center py-12">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 2.5M7 13l2.5 2.5"/>
                            </svg>
                        </div>
                        <p class="text-xl text-gray-500 font-medium">Your cart is empty</p>
                        <p class="text-gray-400 mt-2">Add some items to proceed with reservation</p>
                    </div>
                <?php else: ?>
                    <div class="bg-gray-50 rounded-xl overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-orange-500 text-grey">
                                        <th class="px-6 py-4 text-left font-semibold">Image</th>
                                        <th class="px-6 py-4 text-left font-semibold">Product</th>
                                        <th class="px-6 py-4 text-center font-semibold">Qty</th>
                                        <th class="px-6 py-4 text-right font-semibold">Unit Price</th>
                                        <th class="px-6 py-4 text-right font-semibold">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($cartItems as $item): ?>
                                    <tr class="hover:bg-orange-50 transition-colors duration-200">
                                        <td class="px-6 py-4">
                                            <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden">
                                                <img src="<?= htmlspecialchars(!empty($item['image']) && file_exists($item['image']) ? $item['image'] : 'images/no-image.png') ?>"
                                                     alt="<?= htmlspecialchars($item['product_name']) ?>"
                                                     class="w-full h-full object-cover"
                                                     onerror="this.src='images/no-image.png'">
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-semibold text-gray-800"><?= htmlspecialchars($item['product_name']) ?></div>
                                            <?php if (!empty($item['product_description'])): ?>
                                                <div class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($item['product_description']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full font-semibold">
                                                <?= $item['quantity'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold text-gray-800">₱<?= number_format($item['unit_price'], 2) ?></td>
                                        <td class="px-6 py-4 text-right font-bold text-orange-600">₱<?= number_format($item['subtotal'], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-orange-600 text-grey">
                                        <td class="px-6 py-4 font-bold text-lg" colspan="4">Total Amount</td>
                                        <td class="px-6 py-4 text-right font-bold text-xl">₱<?= number_format($subtotal, 2) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Form Section -->
            <div class="bg-gradient-to-r from-orange-50 to-amber-50 p-8">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Reservation Details</h2>
                </div>

                <form method="post" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Requested By
                                    <span class="text-red-500 ml-1">*</span>
                                </span>
                            </label>
                            <input type="text" 
                                   name="requested_by" 
                                   class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-gray-700 focus:outline-none input-focus transition-all duration-200 placeholder-gray-400" 
                                   placeholder="Enter your full name"
                                   required>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h6a2 2 0 012 2v10a2 2 0 01-2 2H8a2 2 0 01-2-2V9a2 2 0 012-2z"/>
                                    </svg>
                                    Reservation Date & Time
                                    <span class="text-red-500 ml-1">*</span>
                                </span>
                            </label>
                            <div class="flex gap-2">
                                <input type="date" 
                                       name="reservation_date" 
                                       class="w-1/2 border-2 border-gray-200 rounded-xl px-4 py-3 text-gray-700 focus:outline-none input-focus transition-all duration-200 placeholder-gray-400" 
                                       placeholder="dd/mm/yyyy"
                                       required
                                       min="<?= date('Y-m-d') ?>">
                                <select name="reservation_time" id="reservation_time"
                                        class="w-1/2 border-2 border-gray-200 rounded-xl px-4 py-3 text-gray-700 focus:outline-none input-focus transition-all duration-200"
                                        required>
                                    <option value="">Select Time Slot</option>
                                    <option value="08:00 AM">8:00 AM - 12:00 PM</option>
                                    <option value="12:00 PM">12:00 PM - 6:00 PM</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Status
                                </span>
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       name="status" 
                                       class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 bg-gray-50 text-gray-500" 
                                       value="pending" 
                                       readonly>
                                <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-1 rounded-full">
                                        Pending Review
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    </svg>
                                    Checked By
                                    <span class="text-gray-400 font-normal text-xs ml-1">(optional)</span>
                                </span>
                            </label>
                            <input type="text" 
                                   name="checked_by" 
                                   class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-gray-700 focus:outline-none input-focus transition-all duration-200 placeholder-gray-400" 
                                   placeholder="Admin or supervisor name">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
                        <a href="cart.php"
                           class="flex-1 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-8 py-4 rounded-xl font-bold text-lg transition-all duration-200 border-2 border-gray-200 hover:border-gray-300">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Cancel
                        </a>
                        
                        <button type="submit"
                                class="flex-1 inline-flex items-center justify-center orange-gradient text-white px-8 py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none transition-all duration-200"
                                <?= empty($cartItems) ? 'disabled' : '' ?>>
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <?= empty($cartItems) ? 'Cart is Empty' : 'Confirm Reservation' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Add Footer -->
    <footer class="mt-8" style="background: linear-gradient(to right, #ff6b33, #ff4563);">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-white">
                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact Information</h3>
                    <div class="space-y-2">
                        <p class="flex items-center gap-2">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>University of San Jose - Recoletos</span>
                        </p>
                        <p class="flex items-center gap-2">
                            <i class="fas fa-phone"></i>
                            <span>(032) 123-4567</span>
                        </p>
                        <p class="flex items-center gap-2">
                            <i class="fas fa-envelope"></i>
                            <span>info@usjr.edu.ph</span>
                        </p>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="index.php" class="hover:text-orange-200 transition-colors">
                                <i class="fas fa-chevron-right mr-2"></i>Home
                            </a>
                        </li>
                        <li>
                            <a href="cart.php" class="hover:text-orange-200 transition-colors">
                                <i class="fas fa-chevron-right mr-2"></i>Cart
                            </a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-orange-200 transition-colors">
                                <i class="fas fa-chevron-right mr-2"></i>My Reservations
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Social Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Connect With Us</h3>
                    <div class="flex gap-4">
                        <a href="https://www.facebook.com/usjr.official/" target="_blank" class="hover:text-orange-200 transition-colors">
                            <i class="fab fa-facebook fa-2x"></i>
                        </a>

            <!-- Copyright -->
            <div class="mt-8 pt-8 border-t border-white/20 text-center text-white/80">
                <p>&copy; <?= date('Y') ?> University of San Jose - Recoletos. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.flatpickr) {
            flatpickr("#reservation_time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "h:i K",
                time_24hr: false,
                minuteIncrement: 1,
                defaultHour: 8
            });
        }
    });
    </script>
</body>
</html>