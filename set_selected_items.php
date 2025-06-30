<?php

session_start();
header('Content-Type: application/json');

if (isset($_POST['selected_items'])) {
    $_SESSION['selected_cart_items'] = $_POST['selected_items'];
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No items selected'
    ]);
}