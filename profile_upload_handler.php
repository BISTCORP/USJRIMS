<?php

session_start();

if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/profile/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed)) {
        $_SESSION['profile_upload_error'] = "Invalid file type.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    $filename = 'user_' . (isset($_SESSION['username']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_SESSION['username']) : 'guest') . '_' . time() . '.' . $ext;
    $targetFile = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFile)) {
        $_SESSION['profile_pic'] = $targetFile;
        // Optionally, update the user's profile picture in the database here
    } else {
        $_SESSION['profile_upload_error'] = "Failed to upload file.";
    }
}
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
?>