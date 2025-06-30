<?php
session_start();
include 'config.php';

// Load PHPMailer
require_once __DIR__ . '/PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-master/PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/PHPMailer-master/PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $_SESSION['forgot_success'] = false;
        $_SESSION['forgot_message'] = "Email is required.";
        header("Location: login.php");
        exit;
    }

    // Check if the user exists
    $stmt = $conn->prepare("SELECT user_id, username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $_SESSION['forgot_success'] = false;
        $_SESSION['forgot_message'] = "No account found with that email.";
        $stmt->close();
        header("Location: login.php");
        exit;
    }

    $stmt->bind_result($user_id, $username);
    $stmt->fetch();
    $stmt->close();

    // Generate reset code and expiry
    $code = strval(random_int(100000, 999999));
    $expires = date('Y-m-d H:i:s', time() + 600); // 10 minutes from now

    // Insert into password_resets table
    $stmt = $conn->prepare("INSERT INTO password_resets (user_id, code, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $code, $expires);
    $stmt->execute();
    $stmt->close();

    // Send email
    $mail = new PHPMailer(true);

    try {
        // Enable verbose debug output during testing
        $mail->SMTPDebug = 0; // Change to 2 to debug SMTP issues

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'projectimsqdmin576@gmail.com';         // 🔁 Replace with your Gmail
        $mail->Password   = 'ulbhztranjhxcnsi';            // 🔁 Replace with Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Optional: Allow sending from localhost (testing)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom('YOUR_GMAIL@gmail.com', 'IMS Support');
        $mail->addAddress($email, $username);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Code';
        $mail->Body = "
            Hi <b>$username</b>,<br><br>
            Your password reset code is:<br>
            <div style='font-size: 24px; font-weight: bold;'>$code</div><br>
            This code will expire in 10 minutes.<br><br>
            If you did not request this, you may ignore this email.";

        $mail->send();

        // Success
        $_SESSION['reset_user_id'] = $user_id;
        $_SESSION['reset_email'] = $email;
        $_SESSION['forgot_success'] = true;
        $_SESSION['forgot_message'] = "A password reset code has been sent to your email.";
        header("Location: verify_code.php");
        exit;

    } catch (PHPMailerException $e) {
        $_SESSION['forgot_success'] = false;
        $_SESSION['forgot_message'] = "Email failed to send. Error: " . $mail->ErrorInfo;
        header("Location: login.php");
        exit;
    }
}
?>
