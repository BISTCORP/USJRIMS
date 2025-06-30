<?php
session_start();

    include 'index/header.php';
  include 'config.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Inventory Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #0f3a2a 0%, #1a5f3f 50%, #2d8f5a 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Enhanced animated background elements */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(255,255,255,0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.03) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }

        /* Aesthetic floating shapes */
        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .shape {
            position: absolute;
            opacity: 0.6;
            animation: floatShape 15s ease-in-out infinite;
        }

        .shape-1 {
            top: 10%;
            left: 5%;
            width: 120px;
            height: 120px;
            background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(45,143,90,0.2));
            border-radius: 50%;
            animation-delay: 0s;
            animation-duration: 20s;
        }

        .shape-2 {
            top: 20%;
            right: 8%;
            width: 80px;
            height: 160px;
            background: linear-gradient(135deg, rgba(255,255,255,0.08), rgba(26,95,63,0.15));
            border-radius: 40px;
            animation-delay: -5s;
            animation-duration: 18s;
            transform: rotate(45deg);
        }

        .shape-3 {
            bottom: 30%;
            left: 3%;
            width: 100px;
            height: 100px;
            background: linear-gradient(90deg, rgba(45,143,90,0.1), rgba(255,255,255,0.05));
            clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
            animation-delay: -8s;
            animation-duration: 22s;
        }

        .shape-4 {
            top: 60%;
            right: 15%;
            width: 140px;
            height: 70px;
            background: linear-gradient(60deg, rgba(255,255,255,0.06), rgba(15,58,42,0.12));
            border-radius: 50px;
            animation-delay: -12s;
            animation-duration: 16s;
        }

        .shape-5 {
            bottom: 15%;
            right: 5%;
            width: 90px;
            height: 90px;
            background: linear-gradient(225deg, rgba(45,143,90,0.12), rgba(255,255,255,0.08));
            border-radius: 20px;
            animation-delay: -3s;
            animation-duration: 19s;
            transform: rotate(30deg);
        }

        .shape-6 {
            top: 40%;
            left: 2%;
            width: 60px;
            height: 120px;
            background: linear-gradient(180deg, rgba(255,255,255,0.07), rgba(26,95,63,0.1));
            border-radius: 30px;
            animation-delay: -15s;
            animation-duration: 25s;
        }

        .shape-7 {
            top: 5%;
            left: 50%;
            width: 110px;
            height: 110px;
            background: linear-gradient(315deg, rgba(45,143,90,0.09), rgba(255,255,255,0.04));
            clip-path: polygon(25% 0%, 100% 0%, 75% 100%, 0% 100%);
            animation-delay: -7s;
            animation-duration: 21s;
        }

        .shape-8 {
            bottom: 5%;
            left: 20%;
            width: 130px;
            height: 65px;
            background: linear-gradient(45deg, rgba(255,255,255,0.05), rgba(15,58,42,0.08));
            border-radius: 65px;
            animation-delay: -10s;
            animation-duration: 17s;
        }

        @keyframes floatShape {
            0%, 100% { 
                transform: translateY(0px) translateX(0px) rotate(0deg); 
                opacity: 0.4;
            }
            25% { 
                transform: translateY(-30px) translateX(20px) rotate(90deg); 
                opacity: 0.7;
            }
            50% { 
                transform: translateY(-60px) translateX(-10px) rotate(180deg); 
                opacity: 0.5;
            }
            75% { 
                transform: translateY(-30px) translateX(-25px) rotate(270deg); 
                opacity: 0.8;
            }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(1deg); }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            box-shadow: 
                0 25px 50px rgba(15, 58, 42, 0.2),
                0 10px 20px rgba(15, 58, 42, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            display: flex;
            max-width: 950px;
            width: 95%;
            overflow: hidden;
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
            animation: slideUp 0.8s ease-out;
        }

        /* Additional floating shapes inside the form area */
        .login-form-section::before {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(26,95,63,0.03), rgba(255,255,255,0.08));
            border-radius: 50%;
            animation: gentleFloat 12s ease-in-out infinite;
        }

        .login-form-section::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: -30px;
            width: 150px;
            height: 75px;
            background: linear-gradient(45deg, rgba(45,143,90,0.04), rgba(255,255,255,0.06));
            border-radius: 75px;
            animation: gentleFloat 15s ease-in-out infinite reverse;
        }

        @keyframes gentleFloat {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.3; }
            50% { transform: translateY(-15px) rotate(180deg); opacity: 0.6; }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-welcome {
            background: linear-gradient(135deg, #0f3a2a 0%, #1a5f3f 60%, #2d8f5a 100%);
            color: #fff;
            padding: 60px 45px;
            width: 45%;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .login-welcome::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 30% 70%, rgba(255,255,255,0.1) 0%, transparent 40%),
                radial-gradient(circle at 70% 30%, rgba(255,255,255,0.08) 0%, transparent 40%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .login-welcome .logo {
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            position: relative;
            z-index: 2;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            animation: pulse 4s ease-in-out infinite;
            padding: 5px;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .login-welcome .logo img {
            width: 300px;
            height: 300px;
            object-fit: contain;
        }

        .login-welcome .logo::after {
            content: 'USJR';
            position: absolute;
            font-size: 1.2rem;
            font-weight: bold;
            color: #0f3a2a;
            display: none;
        }

        .login-welcome .logo img[src=""]:after,
        .login-welcome .logo img:not([src]):after {
            display: block;
        }

        .login-welcome h2 {
            font-size: 2.8rem;
            margin-bottom: 20px;
            letter-spacing: 2px;
            font-weight: 700;
            text-align: center;
            position: relative;
            z-index: 2;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .login-welcome p {
            font-size: 1.1rem;
            opacity: 0.9;
            text-align: center;
            line-height: 1.6;
            position: relative;
            z-index: 2;
            max-width: 280px;
        }

        .login-form-section {
            flex: 1;
            padding: 60px 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: linear-gradient(145deg, #f8fbf9 0%, #ffffff 100%);
            position: relative;
        }

        .login-form-section h3 {
            margin-bottom: 35px;
            font-size: 2.2rem;
            color: #0f3a2a;
            font-weight: 700;
            text-align: center;
            position: relative;
        }

        .login-form-section h3::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #1a5f3f, #2d8f5a);
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 1.05rem;
            margin-bottom: 8px;
            color: #0f3a2a;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .form-group input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid #e8f0ea;
            border-radius: 12px;
            font-size: 1.05rem;
            background: #f8fbf9;
            outline: none;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus {
            border-color: #1a5f3f;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(26, 95, 63, 0.1);
            transform: translateY(-1px);
        }

        .form-group input:focus + .input-icon {
            color: #1a5f3f;
            transform: scale(1.1);
        }

        .input-icon {
            position: absolute;
            left: 18px;
            color: #1a5f3f;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .eye-icon {
            position: absolute;
            right: 18px;
            color: #1a5f3f;
            font-size: 1.2rem;
            cursor: pointer;
            background: none;
            border: none;
            outline: none;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .eye-icon:hover {
            background: rgba(26, 95, 63, 0.1);
            transform: scale(1.1);
        }

        .options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .remember-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #1a5f3f;
            cursor: pointer;
        }

        .remember-wrapper label {
            font-size: 0.95rem;
            color: #0f3a2a;
            cursor: pointer;
            margin: 0;
            font-weight: 500;
        }

        .forgot-link {
            color: #1a5f3f;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .forgot-link:hover {
            color: #2d8f5a;
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #0f3a2a 0%, #1a5f3f 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 16px 0;
            font-size: 1.15rem;
            font-weight: 700;
            cursor: pointer;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(15, 58, 42, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #1a5f3f 0%, #2d8f5a 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(15, 58, 42, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-other {
            width: 100%;
            background: transparent;
            color: #0f3a2a;
            border: 2px solid #1a5f3f;
            border-radius: 12px;
            padding: 16px 0;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-other::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background: linear-gradient(135deg, #1a5f3f, #2d8f5a);
            transition: width 0.4s ease;
            z-index: -1;
        }

        .btn-other:hover::before {
            width: 100%;
        }

        .btn-other:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(26, 95, 63, 0.3);
        }

        .signup-row {
            text-align: center;
            font-size: 1.05rem;
            color: #666;
        }

        .signup-row a {
            color: #1a5f3f;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .signup-row a:hover {
            color: #2d8f5a;
            text-decoration: underline;
        }

        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .form-group.error input {
            border-color: #e74c3c;
            background: #fdf2f2;
        }

        .form-group.error .input-icon {
            color: #e74c3c;
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.9rem;
            margin-top: 5px;
            display: none;
        }

        .form-group.error .error-message {
            display: block;
        }

        /* Responsive design */
        @media (max-width: 950px) {
            .login-container {
                flex-direction: column;
                max-width: 500px;
            }

            .login-welcome,
            .login-form-section {
                width: 100%;
                padding: 40px 30px;
            }

            .login-welcome {
                text-align: center;
            }

            .login-welcome h2 {
                font-size: 2.2rem;
            }

            .options-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            /* Hide some background shapes on smaller screens */
            .shape-2, .shape-4, .shape-6, .shape-8 {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                width: 98%;
                margin: 10px;
            }

            .login-welcome,
            .login-form-section {
                padding: 30px 20px;
            }

            .login-welcome h2 {
                font-size: 2rem;
            }

            .form-group input {
                padding: 12px 15px 12px 45px;
            }

            .input-icon {
                left: 15px;
            }

            .eye-icon {
                right: 15px;
            }

            /* Hide more background shapes on mobile */
            .shape-3, .shape-5, .shape-7 {
                display: none;
            }
        }
    </style>
</head>

<body>
    <!-- Aesthetic background shapes -->
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
        <div class="shape shape-5"></div>
        <div class="shape shape-6"></div>
        <div class="shape shape-7"></div>
        <div class="shape shape-8"></div>
    </div>

    <div class="login-container">
        <div class="login-welcome">
            <div class="logo">
                <img src="images/USJRlogo.png" alt="USJR Logo" onerror="this.style.display='none'; this.parentNode.innerHTML='<div style=\'font-size:1.2rem;font-weight:bold;color:#0f3a2a;\'>USJR</div>'">
            </div>
            <h2>WELCOME</h2>
            <p>University of San Jose-Recoletos</p>
            <p>Inventory Management System - Secure access to your academic resources and tools.</p>
        </div>
        
        <div class="login-form-section">
            <h3>Sign In</h3>
            <form id="loginForm" method="POST" action="auth.php" novalidate>
                <div id="login-timer-message" style="display:none;color:#e74c3c;text-align:center;margin-bottom:15px;font-weight:600;">
                    Too many failed attempts. Please wait <span id="login-timer"></span> before trying again.
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" required autocomplete="username" 
                               placeholder="Enter your email">
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                    <div class="error-message">Please enter a valid email</div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" required autocomplete="current-password" 
                               placeholder="Enter your password">
                        <i class="fas fa-lock input-icon"></i>
                        <button type="button" class="eye-icon" onclick="togglePassword()" tabindex="-1">
                            <i id="eyeIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="error-message">Please enter your password</div>
                </div>
                
                <div class="options-row">
                    <div class="remember-wrapper">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-link" id="openForgotModal">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn-login" id="loginBtn">
                    <span>Sign In</span>
                </button>
            </form>
            
            <div class="signup-row">
               <a href="#" id="openSignupModal"></a>
            </div>
        </div>
    </div>

    <!-- Sign Up Modal -->
    <div id="signupModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(15,58,42,0.25); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:18px; max-width:420px; width:95%; padding:36px 28px 28px 28px; box-shadow:0 10px 40px rgba(15,58,42,0.18); position:relative;">
            <button onclick="closeSignupModal()" style="position:absolute; top:18px; right:18px; background:none; border:none; font-size:1.4rem; color:#1a5f3f; cursor:pointer;"><i class="fas fa-times"></i></button>
            <h3 style="margin-bottom:18px; color:#0f3a2a; text-align:center;">Sign Up</h3>
            <form id="signupForm" method="POST" action="signup.php" novalidate>
                <div class="form-group">
                    <label for="signup_username">Username</label>
                    <div class="input-wrapper">
                        <input type="text" id="signup_username" name="username" required placeholder="Enter your username">
                        <i class="fas fa-user input-icon"></i>
                    </div>
                    <div class="error-message">Please enter your username</div>
                </div>
                <div class="form-group">
                    <label for="signup_email">Email</label>
                    <div class="input-wrapper">
                        <input type="email" id="signup_email" name="email" required placeholder="Enter your email">
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                    <div class="error-message">Please enter a valid email</div>
                </div>
                <div class="form-group">
                    <label for="signup_password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="signup_password" name="password" required placeholder="Create a password">
                        <i class="fas fa-lock input-icon"></i>
                        <button type="button" class="eye-icon" onclick="toggleSignupPassword()" tabindex="-1">
                            <i id="signupEyeIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="error-message">Please enter a password</div>
                </div>
                <div class="form-group">
                    <label for="signup_role">Role</label>
                    <div class="input-wrapper">
                        <select id="signup_role" name="role" required style="width:100%;padding:15px 20px 15px 50px;border:2px solid #e8f0ea;border-radius:12px;font-size:1.05rem;background:#f8fbf9;outline:none;">
                            <option value="">Select role</option>
                            <option value="Viewer">Viewer</option>
                            <option value="Updater">Updater</option>
                            <option value="Admin">Admin</option>
                        </select>
                        <i class="fas fa-user-tag input-icon"></i>
                    </div>
                    <div class="error-message">Please select a role</div>
                </div>
                <button type="submit" class="btn-login" style="margin-bottom:0;">Sign Up</button>
            </form>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(15,58,42,0.25); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:18px; max-width:420px; width:95%; padding:36px 28px 28px 28px; box-shadow:0 10px 40px rgba(15,58,42,0.18); position:relative;">
            <button onclick="closeForgotModal()" style="position:absolute; top:18px; right:18px; background:none; border:none; font-size:1.4rem; color:#1a5f3f; cursor:pointer;"><i class="fas fa-times"></i></button>
            <h3 style="margin-bottom:18px; color:#0f3a2a; text-align:center;">Forgot Password</h3>
            <form id="forgotForm" method="POST" action="forgot_password.php" novalidate>
                <div class="form-group">
                    <label for="forgot_email">Enter your email address</label>
                    <div class="input-wrapper">
                        <input type="email" id="forgot_email" name="email" required placeholder="Enter your email">
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                    <div class="error-message">Please enter a valid email</div>
                </div>
                <button type="submit" class="btn-login" style="margin-bottom:0;">Send Reset Link</button>
            </form>
        </div>
    </div>

    <script>
        // Form validation and enhancement
        const form = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const loginBtn = document.getElementById('loginBtn');

        // Toggle password visibility
        function togglePassword() {
            const pwd = document.getElementById('password');
            const eye = document.getElementById('eyeIcon');
            
            if (pwd.type === 'password') {
                pwd.type = 'text';
                eye.classList.remove('fa-eye');
                eye.classList.add('fa-eye-slash');
            } else {
                pwd.type = 'password';
                eye.classList.remove('fa-eye-slash');
                eye.classList.add('fa-eye');
            }
        }

        // Real-time validation
        function validateField(field) {
            const formGroup = field.closest('.form-group');
            const value = field.value.trim();
            
            if (field.type === 'email') {
                // Basic email validation
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(value)) {
                    formGroup.classList.add('error');
                    return false;
                }
            } else if (value === '') {
                formGroup.classList.add('error');
                return false;
            } else {
                formGroup.classList.remove('error');
                return true;
            }
            formGroup.classList.remove('error');
            return true;
        }

        // Input event listeners
        emailInput.addEventListener('input', function() {
            validateField(this);
        });

        passwordInput.addEventListener('input', function() {
            validateField(this);
        });

        // --- Login attempt limiter ---
        const MAX_ATTEMPTS = 5;
        const LOCKOUT_TIME = 300; // seconds (5 minutes)

        function getLockoutData() {
            const data = localStorage.getItem('loginLockout');
            if (!data) return null;
            try {
                return JSON.parse(data);
            } catch {
                return null;
            }
        }

        function setLockoutData(attempts, until) {
            localStorage.setItem('loginLockout', JSON.stringify({ attempts, until }));
        }

        function clearLockoutData() {
            localStorage.removeItem('loginLockout');
        }

        function updateLoginTimer() {
            const lockout = getLockoutData();
            const timerDiv = document.getElementById('login-timer');
            const timerMsg = document.getElementById('login-timer-message');
            if (!lockout || !lockout.until) {
                timerMsg.style.display = 'none';
                form.querySelectorAll('input,button').forEach(el => el.disabled = false);
                return;
            }
            const now = Math.floor(Date.now() / 1000);
            let remaining = lockout.until - now;
            if (remaining <= 0) {
                clearLockoutData();
                timerMsg.style.display = 'none';
                form.querySelectorAll('input,button').forEach(el => el.disabled = false);
                return;
            }
            timerMsg.style.display = '';
            // Only disable the login button, not the forgot password or signup
            form.querySelectorAll('input,button').forEach(el => {
                if (el.type === 'submit' || el.type === 'email' || el.type === 'password') {
                    el.disabled = true;
                }
            });
            timerDiv.textContent = `${Math.floor(remaining/60)}:${(remaining%60).toString().padStart(2,'0')}`;
        }

        // On page load, check lockout
        updateLoginTimer();
        let loginTimerInterval = setInterval(updateLoginTimer, 1000);

        // On login form submit, handle attempts
        form.addEventListener('submit', function(e) {
            const isEmailValid = validateField(emailInput);
            const isPasswordValid = validateField(passwordInput);

            // Check lockout before proceeding
            const lockout = getLockoutData();
            const now = Math.floor(Date.now() / 1000);
            if (lockout && lockout.until && lockout.until > now) {
                updateLoginTimer();
                e.preventDefault();
                return;
            }

            if (isEmailValid && isPasswordValid) {
                // Remove the demo simulation of failed login
                // Show loading state
                loginBtn.classList.add('loading');
                loginBtn.innerHTML = '';

                // Submit the form to the server for real authentication
                // Remove setTimeout and Swal.fire for error here
                // Allow the form to submit normally
                // e.preventDefault(); // <-- REMOVE this line if present
            } else {
                e.preventDefault();
            }
        });

        // --- Sign Up Modal Logic ---
        function openSignupModal() {
            document.getElementById('signupModal').style.display = 'flex';
            setTimeout(() => {
                document.getElementById('signup_name').focus();
            }, 200);
        }
        function closeSignupModal() {
            document.getElementById('signupModal').style.display = 'none';
        }
        document.getElementById('openSignupModal').addEventListener('click', function(e) {
            e.preventDefault();
            openSignupModal();
        });

        // Toggle password visibility for signup
        function toggleSignupPassword() {
            const pwd = document.getElementById('signup_password');
            const eye = document.getElementById('signupEyeIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                eye.classList.remove('fa-eye');
                eye.classList.add('fa-eye-slash');
            } else {
                pwd.type = 'password';
                eye.classList.remove('fa-eye-slash');
                eye.classList.add('fa-eye');
            }
        }

        // Basic validation for signup form
        const signupForm = document.getElementById('signupForm');
        if (signupForm) {
            signupForm.addEventListener('submit', function(e) {
                let valid = true;
                // Username
                const username = document.getElementById('signup_username');
                if (!username.value.trim()) {
                    username.closest('.form-group').classList.add('error');
                    valid = false;
                } else {
                    username.closest('.form-group').classList.remove('error');
                }
                // Email
                const email = document.getElementById('signup_email');
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(email.value.trim())) {
                    email.closest('.form-group').classList.add('error');
                    valid = false;
                } else {
                    email.closest('.form-group').classList.remove('error');
                }
                // Password
                const pwd = document.getElementById('signup_password');
                if (!pwd.value.trim()) {
                    pwd.closest('.form-group').classList.add('error');
                    valid = false;
                } else {
                    pwd.closest('.form-group').classList.remove('error');
                }
                // Role
                const role = document.getElementById('signup_role');
                if (!role.value) {
                    role.closest('.form-group').classList.add('error');
                    valid = false;
                } else {
                    role.closest('.form-group').classList.remove('error');
                }
                if (!valid) {
                    e.preventDefault();
                }
            });
        }

        // Close modal on background click
        document.getElementById('signupModal').addEventListener('click', function(e) {
            if (e.target === this) closeSignupModal();
        });

        // Forgot Password Modal Logic
        function openForgotModal() {
            document.getElementById('forgotModal').style.display = 'flex';
            setTimeout(() => {
                document.getElementById('forgot_email').focus();
            }, 200);
        }
        function closeForgotModal() {
            document.getElementById('forgotModal').style.display = 'none';
        }
        document.getElementById('openForgotModal').addEventListener('click', function(e) {
            e.preventDefault();
            openForgotModal();
        });
        document.getElementById('forgotModal').addEventListener('click', function(e) {
            if (e.target === this) closeForgotModal();
        });

        // Forgot password form validation
        const forgotForm = document.getElementById('forgotForm');
        if (forgotForm) {
            forgotForm.addEventListener('submit', function(e) {
                const email = document.getElementById('forgot_email');
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(email.value.trim())) {
                    email.closest('.form-group').classList.add('error');
                    e.preventDefault();
                } else {
                    email.closest('.form-group').classList.remove('error');
                }
            });
        }

        // Enhanced SweetAlert2 styling
        const swalStyle = {
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                content: 'custom-swal-content',
                confirmButton: 'custom-swal-button'
            },
            buttonsStyling: false
        };

        // Add custom styles for SweetAlert2
        const style = document.createElement('style');
        style.textContent = `
            .custom-swal-popup {
                border-radius: 16px !important;
                box-shadow: 0 20px 60px rgba(15, 58, 42, 0.3) !important;
            }
            .custom-swal-title {
                color: #0f3a2a !important;
                font-weight: 700 !important;
            }
            .custom-swal-content {
                color: #555 !important;
            }
            .custom-swal-button {
                background: linear-gradient(135deg, #0f3a2a 0%, #1a5f3f 100%) !important;
                border: none !important;
                border-radius: 8px !important;
                padding: 12px 30px !important;
                font-weight: 600 !important;
                transition: all 0.3s ease !important;
            }
            .custom-swal-button:hover {
                background: linear-gradient(135deg, #1a5f3f 0%, #2d8f5a 100%) !important;
                transform: translateY(-1px) !important;
            }
        `;
        document.head.appendChild(style);

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.ctrlKey) {
                form.dispatchEvent(new Event('submit'));
            }
        });

        // Auto-focus first empty field
        window.addEventListener('load', function() {
            if (!emailInput.value) {
                emailInput.focus();
            } else if (!passwordInput.value) {
                passwordInput.focus();
            }
        });

        // SweetAlert for login feedback
        <?php if (isset($_SESSION['login_message'])): ?>
        Swal.fire({
            title: '<?php echo $_SESSION['login_success'] ? "Success" : "Error"; ?>',
            text: '<?php echo addslashes($_SESSION['login_message']); ?>',
            icon: '<?php echo $_SESSION['login_success'] ? "success" : "error"; ?>',
            confirmButtonText: 'OK'
        });
        <?php
        unset($_SESSION['login_success']);
        unset($_SESSION['login_message']);
        endif;
        ?>

        // Remove or comment out this block to disable SweetAlert for forgot password feedback
        /*
        <?php if (isset($_SESSION['forgot_message'])): ?>
        Swal.fire({
            title: '<?php echo $_SESSION['forgot_success'] ? "Success" : "Error"; ?>',
            text: '<?php echo addslashes($_SESSION['forgot_message']); ?>',
            icon: '<?php echo $_SESSION['forgot_success'] ? "success" : "error"; ?>',
            confirmButtonText: 'OK'
        });
        <?php
        unset($_SESSION['forgot_success']);
        unset($_SESSION['forgot_message']);
        endif;
        ?>
        */
    </script>
</body>
</html>