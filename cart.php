<?php
session_start();
include 'config.php';

// Create or get session ID for cart
if (!isset($_SESSION['cart_session_id'])) {
    $_SESSION['cart_session_id'] = session_id();
}
$session_id = $_SESSION['cart_session_id'];

// Get cart items
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
$cartItems = $result->fetch_all(MYSQLI_ASSOC);

// Calculate totals
$subtotal = 0;
$totalItems = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['subtotal'];
    $totalItems += $item['quantity'];
}
?>

<?php include 'index/header.php'; ?>
  

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - University of San Jose - Recoletos</title>
    <!-- Include stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
    /* Header Styles matching index.php */
    .header-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 8px;
        background: #fff;
        position: fixed;
        top: 0; left: 0; width: 100%;
        z-index: 50;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .header-logo-title {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .header-logo-title img {
        height: 32px;
        width: 32px;
    }

    .header-title {
        font-family: Algerian, serif;
        color: #1a5f3f;
        font-weight: bold;
        font-size: 1.05rem;
        white-space: nowrap;
    }

    .header-nav {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .header-nav a, .header-nav button {
        background: none;
        border: none;
        padding: 7px 10px;
        border-radius: 6px;
        font-size: 1.1rem;
        color: #1a5f3f;
        display: flex;
        align-items: center;
        transition: background 0.2s;
        text-decoration: none;
        font-family: 'Candara Light', Candara, Arial, sans-serif;
    }

    .header-nav a:hover, .header-nav button:hover {
        background: #e6f4ea;
    }

    @media (min-width: 480px) {
        .header-logo-title img { height: 40px; width: 40px; }
        .header-title { font-size: 1.25rem; }
        .header-nav a, .header-nav button { font-size: 1.25rem; }
    }

    @media (min-width: 768px) {
        .header-bar { padding: 14px 32px; }
        .header-title { font-size: 1.5rem; }
        .header-nav { gap: 12px; }
    }

    @media (max-width: 350px) {
        .header-title { font-size: 0.85rem; }
    }

        /* Cart Styles */
        .cart-container {
            margin-top: 80px;
            min-height: calc(100vh - 160px);
        }

        .cart-item {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-1px);
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            border: 1px solid #e5e5e5;
            border-radius: 4px;
            overflow: hidden;
        }

        .quantity-btn {
            width: 32px;
            height: 32px;
            border: none;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s;
        }

        .quantity-btn:hover:not(:disabled) {
            background: #e0e0e0;
        }

        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .quantity-input {
            width: 50px;
            height: 32px;
            border: none;
            text-align: center;
            font-weight: 500;
        }

        .price-text {
            color: #ee4d2d;
            font-weight: 600;
        }

        .original-price {
            color: #929292;
            text-decoration: line-through;
            font-size: 0.9em;
        }

        .checkout-btn {
            background: linear-gradient(135deg, #ee4d2d, #ff6347);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(238, 77, 45, 0.3);
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .cart-summary {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: sticky;
            top: 100px;
        }

        @media (max-width: 768px) {
            .cart-summary {
                position: static;
                margin-top: 20px;
            }
        }

        /* Add visual feedback for clickable area */
        .cart-item {
            cursor: pointer;
            user-select: none;
            transition: all 0.2s ease;
        }

        .cart-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-1px);
        }

        /* Preserve original cursor for interactive elements */
        .cart-item button,
        .cart-item input,
        .cart-item a,
        .cart-item .quantity-selector {
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-gray-100">

<!-- Header -->
<header class="header-bar">
    <div class="header-logo-title">
        <img src="images/USJRlogo.png" alt="USJR Logo">
        <span class="header-title">UNIVERSITY OF SAN JOSE - RECOLETOS</span>
    </div>
    <nav class="header-nav" style="
        display: flex; 
        gap: 20px; 
        font-family: 'Candara Light', Candara, Arial, sans-serif;
    ">
        <a href="index.php" title="Home" style="
            text-decoration: none; 
            color: #17432b; 
            font-weight: normal;
        ">HOME</a>

        <a href="cart.php" title="Cart" class="relative" style="
            text-decoration: none; 
            color: #ee4d2d; 
            font-weight: 600;
        ">CART
            <?php if (isset($_SESSION['cart_session_id'])): ?>
                <?php
                $session_id = $_SESSION['cart_session_id'];
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM cart WHERE session_id = ?");
                $stmt->bind_param("s", $session_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $total_products = $result->fetch_assoc()['total'];
                if ($total_products > 0):
                ?>
                <span id="cart-badge" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                    <?php echo $total_products; ?>
                </span>
                <?php endif; ?>
            <?php endif; ?>
        </a>

        <button id="show-search-btn" title="Search Products" style="
            background: none; 
            border: none; 
            color: #17432b; 
            font-family: 'Candara Light', Candara, Arial, sans-serif;
            font-weight: normal;
            cursor: pointer;
        ">SEARCH</button>
    </nav>
</header>

<!-- Add padding to top to prevent content being hidden behind fixed header -->
<div class="pt-16"></div>

<!-- Cart Container -->
<div class="cart-container px-4 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Cart Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-800 mb-2">Shopping Cart</h1>
                <p class="text-gray-600">Review your items before checkout</p>
            </div>
          <?php if (!empty($cartItems)): ?>
  <div class="flex items-center gap-4">
    <button
  type="button"
  id="removeSelected"
  disabled
  class="flex items-center gap-2 px-4 py-2 rounded-md border border-red-200 
         text-red-600 hover:text-red-700 hover:bg-red-50 
         transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
>
  <i class="fas fa-trash-alt"></i>
  Remove Selected
</button>

  </div>
<?php endif; ?>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <?php if (empty($cartItems)): ?>
                    <div class="cart-item p-8 text-center">
                        <i class="fas fa-shopping-cart text-gray-400 text-5xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">Your cart is empty</h3>
                        <p class="text-gray-500 mb-4">Browse our products and add some items to your cart</p>
                        <a href="index.php" class="inline-block bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition-colors">
                            Continue Shopping
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item p-4 mb-4 flex flex-col md:flex-row gap-4 items-center md:items-start" data-item-id="<?php echo $item['cart_id']; ?>">
                            <div class="flex-shrink-0">
                                <input type="checkbox" 
                                       class="item-checkbox w-5 h-5 rounded border-gray-300"
                                       value="<?php echo $item['cart_id']; ?>">
                            </div>
                            <!-- Product Image -->
                            <div class="flex-shrink-0">
                                <?php
                                $img = !empty($item['image']) && file_exists($item['image']) ? $item['image'] : "images/no-image.png";
                                ?>
                                <img src="<?php echo htmlspecialchars($img); ?>"
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                     class="w-32 h-32 object-cover rounded-lg border"
                                     onerror="this.src='images/no-image.png'">
                            </div>
                            <!-- Product Details -->
                            <div class="flex-1 min-w-0">
                                <h3 class="text-xl font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h3>
                                <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($item['product_description'] ?? ''); ?></p>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-semibold">Available</span>
                                    <span class="text-gray-500 text-sm"><i class="fas fa-box"></i> In Stock: <b><?php echo $item['quantity_in_stock']; ?></b></span>
                                </div>
                                <div class="flex items-center gap-4 mb-2">
                                    <span class="text-2xl font-bold text-green-700">₱<?php echo number_format($item['unit_price'], 2); ?></span>
                                </div>
                                <div class="flex items-center gap-4">
                                    <!-- Quantity Controls -->
                                    <div class="quantity-selector">
                                        <button class="quantity-btn"
                                                onclick="updateQuantity(<?php echo $item['cart_id']; ?>, -1)"
                                                <?php echo ($item['quantity'] <= 1) ? 'disabled' : ''; ?>> <i class="fas fa-minus"></i></button>
                                        <input type="text"
                                               class="quantity-input"
                                               id="qty-<?php echo $item['cart_id']; ?>"
                                               value="<?php echo $item['quantity']; ?>"
                                               readonly>
                                        <button class="quantity-btn"
                                                onclick="updateQuantity(<?php echo $item['cart_id']; ?>, 1)"
                                                <?php echo ($item['quantity'] >= $item['quantity_in_stock']) ? 'disabled' : ''; ?>> <i class="fas fa-plus"></i></button>
                                    </div>
                                    <button class="text-red-500 hover:text-red-700 ml-4"
                                            onclick="removeItem(<?php echo $item['cart_id']; ?>)">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                            <!-- Subtotal -->
                            <div class="text-right mt-4 md:mt-0">
                                <span class="block text-gray-500 text-sm">Subtotal</span>
                                <span class="text-lg font-semibold text-green-700" id="subtotal-<?php echo $item['cart_id']; ?>">₱<?php echo number_format($item['subtotal'], 2); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Cart Summary -->
            <div class="lg:col-span-1">
                <div class="cart-summary p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Order Summary</h3>
                    
                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Selected Items (<span id="selected-items-count">0</span>)</span>
                            <span class="font-semibold" id="selected-subtotal">₱0.00</span>
                        </div>
                        <hr class="border-gray-200">
                        <div class="flex justify-between text-lg">
                            <span class="font-semibold">Total</span>
                        </div>
                    </div>

                    <!-- Action Buttons - Remove the removeSelected button -->
                    <div class="space-y-3">
                       <button id="proceedSelected" disabled 
    class="w-full py-2 px-4 rounded-md border border-green-200 
           text-green-600 hover:text-green-700 hover:bg-green-50 
           transition-colors duration-200 
           disabled:opacity-50 disabled:cursor-not-allowed
           flex items-center justify-center gap-2">
    <i class="fas fa-lock"></i>
    Proceed to Checkout
</button>

                        <a href="index.php" 
                            class="block text-center py-2 text-gray-600 hover:text-gray-800 transition-colors">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer style="background: #ffffff; color: #333; padding: 50px 20px; font-family: 'Candara', Arial, sans-serif; margin-top: 60px;">
    <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px;">
        
        <!-- Logo and Short Info -->
        <div style="text-align: center;">
            <img src="images/USJRlogo.png" alt="USJR Logo" style="height: 80px; margin-bottom: 15px;">
            <h3 style="margin: 10px 0 5px; color: #17432b;">University of San Jose - Recoletos</h3>
            <p style="font-size: 0.95rem; color: #555;">Founded in 1947</p>
        </div>

        <!-- Vision Statement -->
        <div>
            <h4 style="color: #17432b; font-size: 1.2rem; margin-bottom: 12px;">Our Vision</h4>
            <p style="font-size: 1rem; line-height: 1.6; color: #555;">
                To be a premier Gospel and Community-oriented institution transforming Joseinians into proactive leaders and dynamic partners of society.
            </p>
        </div>

        <!-- Contact Information -->
        <div>
            <h4 style="color: #17432b; font-size: 1.2rem; margin-bottom: 12px;">Contact Us</h4>
            <p style="margin: 0 0 12px; font-size: 1rem;">
                <i class="fas fa-map-marker-alt" style="margin-right: 8px; color: #17432b;"></i>
                Magallanes Street, Cebu City, Philippines
            </p>
            <p style="margin: 0 0 12px; font-size: 1rem;">
                <i class="fas fa-envelope" style="margin-right: 8px; color: #17432b;"></i>
                external@usjr.edu.ph
            </p>
            <p style="margin: 0; font-size: 1rem;">
                <i class="fas fa-phone" style="margin-right: 8px; color: #17432b;"></i>
                (63-32) 253-7900
            </p>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div style="text-align: center; margin-top: 40px; color: #555; font-size: 0.9rem;">
        &copy; 2025 University of San Jose - Recoletos. All rights reserved.
    </div>
</footer>

<script>
function updateQuantity(cartId, change) {
    $.ajax({
        url: 'update_cart.php',
        method: 'POST',
        data: { cart_id: cartId, change: change },
        success: function(response) {
            if (response.status === 'success') {
                // Update quantity input
                $('#qty-' + cartId).val(response.new_qty);
                // Update subtotal
                $('#subtotal-' + cartId).text('₱' + parseFloat(response.new_subtotal).toFixed(2));
                // Enable/disable buttons
                let minusBtn = $('#qty-' + cartId).closest('.quantity-selector').find('.quantity-btn').first();
                let plusBtn = $('#qty-' + cartId).closest('.quantity-selector').find('.quantity-btn').last();
                minusBtn.prop('disabled', response.new_qty <= 1);
                plusBtn.prop('disabled', response.new_qty >= response.max_qty);
                // Update total and item count
                $('#cart-total').text('₱' + parseFloat(response.cart_total).toFixed(2));
                $('#cart-subtotal').text('₱' + parseFloat(response.cart_total).toFixed(2));
                $('#cart-items-count').text(response.cart_items);
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        }
    });
}

function removeItem(cartId) {
    Swal.fire({
        title: 'Remove Item?',
        text: 'Are you sure you want to remove this item from your cart?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ee4d2d',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, remove it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Removing item...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false
            });

            $.ajax({
                url: 'remove_from_cart.php',
                method: 'POST',
                data: { cart_id: cartId },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Item removed from cart',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Fade out the body, then reload
                            $('body').fadeOut(300, function() {
                                window.location.reload();
                            });
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to remove item', 'error');
                }
            });
        }
    });
}

$(document).ready(function() {
    // Update the checkbox change handler
    $(document).on('change', '.item-checkbox', function() {
        const hasChecked = $('.item-checkbox:checked').length > 0;
        const $removeBtn = $('#removeSelected');
        const $proceedBtn = $('#proceedSelected');

        // Handle Remove Selected button
        if (hasChecked) {
            $removeBtn
                .prop('disabled', false)
                .removeClass('border-red-200 text-red-600 hover:text-red-700 hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed')
                .addClass('bg-red-600 hover:bg-red-700 text-white border-transparent');
        } else {
            $removeBtn
                .prop('disabled', true)
                .removeClass('bg-red-600 hover:bg-red-700 text-white border-transparent')
                .addClass('border-red-200 text-red-600 hover:text-red-700 hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed');
        }

        // Handle Proceed to Checkout button
        if (hasChecked) {
            $proceedBtn
                .prop('disabled', false)
                .removeClass('border-green-200 text-green-600 hover:text-green-700 hover:bg-green-50 disabled:opacity-50 disabled:cursor-not-allowed')
                .addClass('bg-green-600 hover:bg-green-700 text-white border-transparent');
        } else {
            $proceedBtn
                .prop('disabled', true)
                .removeClass('bg-green-600 hover:bg-green-700 text-white border-transparent')
                .addClass('border-green-200 text-green-600 hover:text-green-700 hover:bg-green-50 disabled:opacity-50 disabled:cursor-not-allowed');
        }
    });

    // Handle Remove Selected button
    $('#removeSelected').on('click', function() {
        const selectedIds = $('.item-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        Swal.fire({
            title: 'Remove Selected Items?',
            text: 'Are you sure you want to remove the selected items from your cart?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ee4d2d',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, remove them!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Removing items...',
                    didOpen: () => {
                        Swal.showLoading();
                    },
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false
                });

                $.ajax({
                    url: 'remove_multiple_from_cart.php',
                    method: 'POST',
                    data: { cart_ids: selectedIds },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Selected items removed from cart',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                // Fade out the body, then reload
                                $('body').fadeOut(300, function() {
                                    window.location.reload();
                            });
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to remove items', 'error');
                    }
                });
            }
        });
    });

    // Handle Remove All button
    function removeAllItems() {
        Swal.fire({
            title: 'Remove All Items?',
            text: 'Are you sure you want to remove all items from your cart?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ee4d2d',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, remove all!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'remove_all_from_cart.php',
                    method: 'POST',
                    data: { session_id: '<?php echo $session_id; ?>' },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Cart Cleared!',
                                text: 'All items have been removed.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload(); // Force page refresh
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    }

    // Function to update Order Summary based on selected items
    function updateOrderSummary() {
        let selectedTotal = 0;
        let selectedCount = 0;
        
        $('.item-checkbox:checked').each(function() {
            const itemId = $(this).val();
            const subtotal = parseFloat($('#subtotal-' + itemId)
                .text()
                .replace('₱', '')
                .replace(',', ''));
            selectedTotal += subtotal;
            selectedCount++;
        });

        // Update summary display
        $('#selected-items-count').text(selectedCount);
        $('#selected-subtotal').text('₱' + selectedTotal.toFixed(2));
        $('#selected-total').text('₱' + selectedTotal.toFixed(2));

        // Enable/disable action buttons
        const hasSelection = selectedCount > 0;
        $('#proceedSelected, #removeSelected').prop('disabled', !hasSelection);
    }

    // Handle checkbox changes
    $(document).on('change', '.item-checkbox', function() {
        updateOrderSummary();
    });

    // Handle "Proceed with Selected Items" button
    $('#proceedSelected').on('click', function() {
        const selectedIds = $('.item-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        // Store selected items in session
        $.ajax({
            url: 'set_selected_items.php',
            method: 'POST',
            data: { selected_items: selectedIds },
            success: function(response) {
                if (response.status === 'success') {
                    window.location.href = 'reservation_checkout.php';
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    });

    // Make the entire cart item clickable
    $(document).on('click', '.cart-item', function(e) {
        // Prevent triggering when clicking specific elements
        if ($(e.target).is('button, input, a') || $(e.target).closest('button, .quantity-selector, a').length) {
            return;
        }
        
        // Find the checkbox within this cart item
        const checkbox = $(this).find('.item-checkbox');
        
        // Toggle checkbox
        checkbox.prop('checked', !checkbox.prop('checked'));
        
        // Trigger change event to update UI
        checkbox.trigger('change');
    });

    // Prevent checkbox clicks from triggering twice
    $(document).on('click', '.item-checkbox', function(e) {
        e.stopPropagation();
    });
});
</script>

</body>
</html>