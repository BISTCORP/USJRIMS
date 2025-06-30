<?php
session_start();
include 'config.php';

if ($_POST['action'] === 'delete_returns' && isset($_POST['ids']) && is_array($_POST['ids'])) {
    $ids = array_map('intval', $_POST['ids']);
    $ids_str = implode(',', $ids);
    $user_id = $_SESSION['user_id'] ?? null;

    // Fetch info for audit message
    $res = mysqli_query($conn, "SELECT reservation_code, product_name, returned_by FROM returns WHERE return_id IN ($ids_str)");
    $infos = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $infos[] = "Code {$row['reservation_code']}, Product: {$row['product_name']}, Returned by: {$row['returned_by']}";
    }
    if (!empty($infos)) {
        $notif_message = "Return logs deleted: " . implode("; ", $infos);
        $notif_query = "INSERT INTO notifications (user_id, message, is_read, status, created_at) VALUES (?, ?, 0, 'unread', NOW())";
        $notif_stmt = $conn->prepare($notif_query);
        $notif_stmt->bind_param('is', $user_id, $notif_message);
        $notif_stmt->execute();
        $notif_stmt->close();
    }
    echo json_encode(['success' => true]);
    exit;
}
echo json_encode(['success' => false]);
?>
