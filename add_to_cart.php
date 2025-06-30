<?php

session_start();
include 'config.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Get product ID from POST data
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

// Validate product ID
if ($product_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
    exit;
}

// Check if product exists and is in stock
$stmt = $conn->prepare("SELECT quantity_in_stock, status FROM inventory_products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo json_encode(['status' => 'error', 'message' => 'Product not found']);
    exit;
}

if ($product['status'] !== 'Available' || $product['quantity_in_stock'] <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Product is out of stock']);
    exit;
}

// Create or get session ID for cart
if (!isset($_SESSION['cart_session_id'])) {
    $_SESSION['cart_session_id'] = session_id();
}
$session_id = $_SESSION['cart_session_id'];

// Check if product already exists in cart
$stmt = $conn->prepare("SELECT quantity FROM cart WHERE session_id = ? AND product_id = ?");
$stmt->bind_param("si", $session_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing cart item
    $cart_item = $result->fetch_assoc();
    $new_quantity = $cart_item['quantity'] + 1;
    
    // Check if requested quantity is available
    if ($new_quantity > $product['quantity_in_stock']) {
        echo json_encode(['status' => 'error', 'message' => 'Not enough stock available']);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE session_id = ? AND product_id = ?");
    $stmt->bind_param("isi", $new_quantity, $session_id, $product_id);
} else {
    // Insert new cart item
    $stmt = $conn->prepare("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, 1)");
    $stmt->bind_param("si", $session_id, $product_id);
}

if ($stmt->execute()) {
    // Get total items in cart
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE session_id = ?");
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $total_result = $stmt->get_result();
    $total_items = $total_result->fetch_assoc()['total'];
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Added to cart successfully!',
        'total_items' => $total_items
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add item to cart']);
}

$stmt->close();
$conn->close();