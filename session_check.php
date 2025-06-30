<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // Not logged in, redirect to index.php or login page
    header("Location: /Inventory/index.php");
    exit();
}
// Role check removed; only user_id is required
?>