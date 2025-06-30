<?php
session_start();
include 'config.php';

// get_project_details.php

// Check if session is active (user is logged in)
if (!isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

// Get project_id from POST request
if (isset($_POST['project_id'])) {
    $project_id = intval($_POST['project_id']);
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT total_cost, description, payment_date FROM project_payments WHERE project_id = ? LIMIT 1");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Return project details as JSON
        echo json_encode([
            'success' => true,
            'total_cost' => $row['total_cost'],
            'description' => $row['description'],
            'payment_date' => $row['payment_date']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Project not found']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No project ID provided']);
}

$conn->close();
?>
