<?php
session_start();
include 'config.php';

$user_id = $_SESSION['reset_user_id'] ?? null;
$email = $_SESSION['reset_email'] ?? null;
$error = '';
$success = '';
$show_reset_password = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // If coming from code verification
    if (isset($_POST['verify_code'])) {
        $code = trim($_POST['code'] ?? '');
        if ($code === '') {
            $error = "Please enter the code.";
        } else {
            $stmt = $conn->prepare("SELECT id, expires_at, used FROM password_resets WHERE user_id = ? AND code = ? ORDER BY id DESC LIMIT 1");
            $stmt->bind_param("is", $user_id, $code);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 0) {
                $error = "Invalid code.";
            } else {
                $stmt->bind_result($reset_id, $expires_at, $used);
                $stmt->fetch();
                if ($used || strtotime($expires_at) < time()) {
                    $error = "This code is expired or already used.";
                } else {
                    $_SESSION['reset_verified'] = true;
                    $_SESSION['reset_code_id'] = $reset_id;
                    $show_reset_password = true;
                }
            }
            $stmt->close();
        }
    }
    // If coming from password reset
    elseif (isset($_POST['reset_password'])) {
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        $reset_id = $_SESSION['reset_code_id'] ?? null;

        if ($password === '' || $password2 === '') {
            $error = "All fields are required.";
            $show_reset_password = true;
        } elseif ($password !== $password2) {
            $error = "Passwords do not match.";
            $show_reset_password = true;
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
            $show_reset_password = true;
        } else {
            // Update password
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt2->bind_param("si", $hashed, $user_id);
            $stmt2->execute();
            $stmt2->close();

            // Mark code as used
            $stmt3 = $conn->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
            $stmt3->bind_param("i", $reset_id);
            $stmt3->execute();
            $stmt3->close();

            unset($_SESSION['reset_user_id'], $_SESSION['reset_email'], $_SESSION['reset_verified'], $_SESSION['reset_code_id']);
            $_SESSION['login_success'] = true;
            $_SESSION['login_message'] = "Password reset successful! You can now log in.";
            header("Location: login.php");
            exit;
        }
    }
} else {
    unset($_SESSION['reset_verified'], $_SESSION['reset_code_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>University of San Jose - Recoletos</title>
  
  <!-- Include all header resources -->
  <?php include 'index/header.php'; ?>
  
  <!-- Additional resources specific to this page -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<!-- Add in <head> -->
<link rel="stylesheet" href="vendor/slick/slick.css">
<link rel="stylesheet" href="vendor/slick/slick-theme.css">
<link href="https://fonts.googleapis.com/css?family=Poppins:400,700&display=swap" rel="stylesheet">
<style>
        /* Remove invalid @font-face blocks and keep only the correct font-family usage */
  body, .reset-box {
    font-family: 'Arial', sans-serif;
}

.modal-header {
    text-align: center; /* Center all text inside modal-header */
}

.modal-header h2 {
    font-family: 'Candara Light', Candara, Arial, sans-serif;
    font-weight: 600;
    letter-spacing: -0.01em;
}

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #0a2e1f 0%, #1a3d2e 25%, #2d5a3d 50%, #3d7a5d 75%, #4a9b7a 100%);
            min-height: 100vh;
            font-family: 'Inter', 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Enhanced background effects */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(74, 155, 122, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(45, 122, 93, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(26, 61, 46, 0.4) 0%, transparent 50%);
            animation: backgroundPulse 8s ease-in-out infinite;
            z-index: 0;
        }
        
        body::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            z-index: 0;
        }
        
        .reset-container {
            position: relative;
            z-index: 10;
            max-width: 420px;
            width: 100%;
            margin: 0 20px;
        }
        
        .reset-box {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0;
            border-radius: 24px;
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.15),
                0 8px 16px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            transform: translateY(0);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            position: relative;
        }
        
        .reset-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(74, 155, 122, 0.5), transparent);
            animation: shimmer 3s ease-in-out infinite;
        }
        
        .reset-box:hover {
            transform: translateY(-5px);
            box-shadow: 
                0 30px 60px rgba(0, 0, 0, 0.2),
                0 12px 24px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #2d5a3d 0%, #4a9b7a 100%);
            padding: 32px 40px 24px;
            position: relative;
            overflow: hidden;
        }
        
        .modal-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: headerShine 4s ease-in-out infinite;
        }
        
        .modal-header h2 {
            color: #ffffff;
            font-size: 1.75rem;
            font-weight: 700;
            text-align: center;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            letter-spacing: -0.025em;
            position: relative;
            z-index: 1;
        }
        
        .modal-body {
            padding: 32px 40px 40px;
        }
        
        .form-group {
            margin-bottom: 24px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2d5a3d;
            font-weight: 600;
            font-size: 0.95rem;
            letter-spacing: 0.01em;
        }
        
        .form-group input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid rgba(45, 90, 61, 0.1);
            border-radius: 16px;
            font-size: 1rem;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: #2d5a3d;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #4a9b7a;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 
                0 0 0 4px rgba(74, 155, 122, 0.1),
                0 4px 12px rgba(74, 155, 122, 0.15);
            transform: translateY(-2px);
        }
        
        .form-group input::placeholder {
            color: rgba(45, 90, 61, 0.5);
            font-weight: 400;
        }
        
        .btn {
            width: 100%;
            background: linear-gradient(135deg, #2d5a3d 0%, #4a9b7a 100%);
            color: #ffffff;
            border: none;
            border-radius: 16px;
            padding: 16px 0;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(45, 90, 61, 0.3);
            position: relative;
            overflow: hidden;
            letter-spacing: 0.025em;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(45, 90, 61, 0.4);
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            margin-top: 12px;
        }
        
        .btn-secondary:hover {
            box-shadow: 0 8px 24px rgba(108, 117, 125, 0.4);
        }
        
        .error {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
            animation: slideIn 0.3s ease-out;
        }
        
        .success {
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
            color: white;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(81, 207, 102, 0.3);
            animation: slideIn 0.3s ease-out;
        }
        
        .timer-container {
            margin-top: 12px;
            padding: 12px 16px;
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        #timer {
            color: #f39c12;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        #resendBtn {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            color: white;
            border: none;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
            display: none;
        }
        
        #resendBtn:hover {
            transform: scale(1.05) rotate(90deg);
            box-shadow: 0 6px 16px rgba(255, 152, 0, 0.4);
        }
        
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }
        
        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.6;
            animation: float 20s ease-in-out infinite;
        }
        
        .shape-1 {
            top: 10%;
            left: 10%;
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, rgba(74, 155, 122, 0.2), rgba(45, 90, 61, 0.1));
            animation-delay: 0s;
        }
        
        .shape-2 {
            top: 20%;
            right: 15%;
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, rgba(45, 90, 61, 0.15), rgba(74, 155, 122, 0.1));
            animation-delay: -5s;
        }
        
        .shape-3 {
            bottom: 20%;
            left: 20%;
            width: 100px;
            height: 100px;
            background: linear-gradient(225deg, rgba(74, 155, 122, 0.18), rgba(45, 90, 61, 0.12));
            animation-delay: -10s;
        }
        
        .shape-4 {
            bottom: 30%;
            right: 10%;
            width: 90px;
            height: 90px;
            background: linear-gradient(315deg, rgba(45, 90, 61, 0.2), rgba(74, 155, 122, 0.08));
            animation-delay: -15s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) translateX(0px);
                opacity: 0.6;
            }
            50% {
                transform: translateY(-30px) translateX(20px);
                opacity: 0.8;
            }
        }
        
        @keyframes backgroundPulse {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.8;
                transform: scale(1.05);
            }
        }
        
        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }
            100% {
                transform: translateX(100%);
            }
        }
        
        @keyframes headerShine {
            0% {
                left: -100%;
            }
            50%, 100% {
                left: 100%;
            }
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Code input styling */
        input[name="code"] {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.5em;
            font-family: 'Monaco', 'Menlo', monospace;
        }
        
        /* Responsive design */
        @media (max-width: 480px) {
            .reset-container {
                margin: 0 16px;
            }
            
            .modal-header {
                padding: 24px 24px 20px;
            }
            
            .modal-body {
                padding: 24px;
            }
            
            .modal-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
    </div>
    
    <div class="reset-container">
        <!-- Reset Code Modal -->
        <?php if (!$show_reset_password): ?>
      <div class="reset-box">
    <div class="modal-header">
        <h2>Enter Reset Code</h2>
    </div>
            <div class="modal-body">
                <?php if ($error) echo "<div class='error'>⚠️ $error</div>"; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="code">Security Code</label>
                        <input type="text" name="code" id="code" required maxlength="6" pattern="\d{6}" placeholder="000000" autocomplete="one-time-code">
                        <div class="timer-container">
                            <div id="timer"></div>
                            <button type="button" id="resendBtn" title="Resend Code">↻</button>
                        </div>
                    </div>
                    <button type="submit" class="btn" name="verify_code">Verify Code</button>
                </form>
                <form method="get" action="login.php">
                    <button type="submit" class="btn btn-secondary">← Back to Login</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Reset Password Modal -->
        <?php if ($show_reset_password): ?>
       <div class="reset-box">
    <div class="modal-header">
        <h2>Reset Password</h2>
    </div>
            <div class="modal-body">
                <?php if ($error) echo "<div class='error'>⚠️ $error</div>"; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" name="password" id="password" required minlength="8" placeholder="Enter new password" oninput="checkPasswordStrength(this.value)">
                        <div id="password-strength-msg" style="font-size:0.9em;color:#888;margin-top:4px;">
                <i class="fas fa-info-circle"></i> Use at least 8 characters, with uppercase, lowercase, number, and symbol for a strong password.
            </div>
                    </div>
                    <div class="form-group">
                        <label for="password2">Confirm Password</label>
                        <input type="password" name="password2" id="password2" required minlength="8" placeholder="Confirm new password">
                    </div>
                    <button type="submit" class="btn" name="reset_password">Reset Password</button>
                </form>
                <form method="get" action="login.php">
                    <button type="submit" class="btn btn-secondary">← Back to Login</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['login_success']) && $_SESSION['login_success'] && isset($_SESSION['login_message'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="vendor/jquery-3.2.1.min.js"></script>
<script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
<script src="vendor/jquery-3.2.1.min.js"></script>
<script src="vendor/slick/slick.js"></script>
<script></script>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?php echo addslashes($_SESSION['login_message']); ?>',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'btn'
                }
            }).then(() => {
                window.location.href = 'login.php';
            });
        </script>
        <?php
        unset($_SESSION['login_success']);
        unset($_SESSION['login_message']);
        ?>
    <?php endif; ?>

    <script>
    <?php
    // Fetch the latest code's expiry for this user for the timer
    $expires_at = null;
    if ($user_id) {
        // Set expiry to 5 minutes from the latest code creation
        $stmt = $conn->prepare("SELECT created_at FROM password_resets WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($created_at);
        $stmt->fetch();
        $stmt->close();
        if ($created_at) {
            $expires_at = date('Y-m-d H:i:s', strtotime($created_at) + 300); // 5 minutes = 300 seconds
        }
    }
    ?>
    <?php if ($expires_at): ?>
    const expiresAt = new Date("<?php echo $expires_at; ?>").getTime();
    const timerDiv = document.getElementById('timer');
    const resendBtn = document.getElementById('resendBtn');
    
    function updateTimer() {
        const now = new Date().getTime();
        let distance = Math.floor((expiresAt - now) / 1000);
        if (distance < 0) distance = 0;
        const min = Math.floor(distance / 60);
        const sec = distance % 60;
        
        if (distance > 0) {
            // Show "5 mins" at start, then countdown
            if (distance >= 299) {
                timerDiv.textContent = "⏱️ 5 mins";
            } else {
                timerDiv.textContent = `⏱️ ${min}:${sec.toString().padStart(2, '0')}`;
            }
            resendBtn.style.display = "none";
        } else {
            timerDiv.textContent = "✅ Ready to resend";
            resendBtn.style.display = "block";
        }
    }
    
    updateTimer();
    setInterval(updateTimer, 1000);

    resendBtn.addEventListener('click', function() {
        resendBtn.disabled = true;
        resendBtn.innerHTML = "⏳";
        resendBtn.style.transform = "scale(0.9)";
        
        fetch('resend_code.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    resendBtn.innerHTML = "❌";
                    setTimeout(() => {
                        resendBtn.innerHTML = "↻";
                        resendBtn.disabled = false;
                        resendBtn.style.transform = "scale(1)";
                    }, 2000);
                }
            })
            .catch(() => {
                resendBtn.innerHTML = "❌";
                setTimeout(() => {
                    resendBtn.innerHTML = "↻";
                    resendBtn.disabled = false;
                    resendBtn.style.transform = "scale(1)";
                }, 2000);
            });
    });
    <?php endif; ?>

    // Add input formatting for code
    const codeInput = document.getElementById('code');
    if (codeInput) {
        codeInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
        
        codeInput.addEventListener('paste', function(e) {
            setTimeout(() => {
                e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6);
            }, 0);
        });
    }

    function checkPasswordStrength(password) {
    const msg = document.getElementById('password-strength-msg');
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    let text = '';
    let color = '';
    if (password.length === 0) {
        text = '<i class="fas fa-info-circle"></i> Use at least 8 characters, with uppercase, lowercase, number, and symbol for a strong password.';
        color = '#888';
    } else if (strength <= 2) {
        text = '<span style="color:#e74c3c"><i class="fas fa-exclamation-circle"></i> Weak password</span>';
        color = '#e74c3c';
    } else if (strength === 3 || strength === 4) {
        text = '<span style="color:#f39c12"><i class="fas fa-exclamation-triangle"></i> Strong password</span>';
        color = '#f39c12';
    } else if (strength === 5) {
        text = '<span style="color:#27ae60"><i class="fas fa-check-circle"></i> Very strong password</span>';
        color = '#27ae60';
    }
    msg.innerHTML = text;
}
    </script>
</body>
</html>