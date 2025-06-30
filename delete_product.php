<?php
include 'config.php';

if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Optional: Get product name before deleting for a better notification message
    $product_name = '';
    $name_query = "SELECT product_name FROM inventory_products WHERE product_id = '$delete_id'";
    $name_result = mysqli_query($conn, $name_query);
    if ($row = mysqli_fetch_assoc($name_result)) {
        $product_name = $row['product_name'];
    }

    $query = "DELETE FROM inventory_products WHERE product_id = '$delete_id'";

    if (mysqli_query($conn, $query)) {
        // Add notification after successful product delete (audit trail)
        $notif_message = "Product deleted: " . ($product_name ? $product_name : "ID $delete_id");
        $user_id = $_SESSION['user_id'] ?? null;

        $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at, product_id) VALUES (?, ?, 0, 'unread', NOW(), ?)";
        $notif_stmt = $conn->prepare($notif_query);
        $notif_stmt->bind_param('isi', $user_id, $notif_message, $delete_id);
        $notif_stmt->execute();
        $notif_stmt->close();

        echo "Product deleted successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }



}
?>