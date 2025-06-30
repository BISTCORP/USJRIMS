<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_name = isset($_SESSION['username']) ? $_SESSION['username'] : "Admin"; // Get session username
$email = isset($_SESSION['email']) ? $_SESSION['email'] : "No Email"; // Get session email

include 'config.php';

// Check if 'is_read' column exists in the notifications table
$notificationQuery = "SELECT COUNT(*) AS unreadCount FROM notifications WHERE is_read = 0"; 
$notificationResult = mysqli_query($conn, $notificationQuery);

// Check if query was successful
if (!$notificationResult) {
    die("Query Failed: " . mysqli_error($conn));  // Display MySQL error
}

$notificationRow = mysqli_fetch_assoc($notificationResult);
$unreadCount = $notificationRow['unreadCount'];

// Fetch recent notifications (both read and unread)
$fetchNotifications = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5";
$rt = mysqli_query($conn, $fetchNotifications);

// Check if query was successful
if (!$rt) {
    die("Query Failed: " . mysqli_error($conn));  // Display MySQL error
}

// Get current date and time with timezone
date_default_timezone_set('Asia/Manila'); // You can change this to your preferred timezone
$current_date = date('F j, Y');
$current_time = date('g:i A');
$timezone = date('T');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dashboard</title>

    <!-- Fontfaces CSS-->
    <link href="css/font-face.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-5/css/fontawesome-all.min.css" rel="stylesheet" media="all">
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Bootstrap CSS-->
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet" media="all">
    <link rel="icon" type="image/x-icon" href="images/University_of_San_Jose–Recoletos_logo.ico">
    <!-- Vendor CSS-->
    <link href="vendor/animsition/animsition.min.css" rel="stylesheet" media="all">
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" media="all">

    <!-- Main CSS-->
    <link href="css/theme.css" rel="stylesheet" media="all">
    
    <style>
        .datetime-display {
            margin-right: 15px;
            text-align: right;
            line-height: 1.2;
        }
        .datetime-display .date {
            font-size: 14px;
            color: #666;
        }
        .datetime-display .time {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        .datetime-display .timezone {
            font-size: 12px;
            color: #888;
        }
        
        /* Add styling for unread notifications */
        .notifi__item.unread {
            background-color: #f0f7ff;
        }
        .notifi__item.read {
            background-color: #ffffff;
        }

        /* Updated Reservation dropdown styling */
        .reservation-item {
            border-top: 1px solid #e0e0e0;
            border-bottom: 1px solid #e0e0e0;
            margin: 5px 0;
        }

        .reservation-toggle {
            border: none !important;
            background: none !important;
            color: #666 !important;
            outline: none !important;
            box-shadow: none !important;
            transition: color 0.3s ease;
            padding: 0 !important;
        }

        .reservation-toggle:hover {
            color: #333 !important;
        }

        .reservation-toggle:focus {
            box-shadow: none !important;
        }

        .reservation-arrow {
            transition: transform 0.3s ease;
            font-size: 14px;
        }

        .reservation-toggle.active .reservation-arrow {
            transform: rotate(180deg);
        }

        /* Submenu styling */
        .reservation-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
            padding: 0;
            margin: 0;
        }

        .reservation-submenu.show {
            max-height: 200px;
            padding: 0.5rem 0;
        }

        .dropdown-divider {
            border-top: 1px solid rgba(0,0,0,0.1);
            margin: 0.3rem 0 0.3rem 2.5rem;
            width: calc(100% - 2.5rem);
        }

        .reservation-submenu .nav-link {
            padding: 8px 20px 8px 40px;
            transition: background-color 0.2s ease;
            color: #666;
        }

        .reservation-submenu .nav-link:hover {
            background-color: #f8f9fa;
            color: #333;
            text-decoration: none;
        }

        /* Make sure the parent link doesn't interfere */
        .reservation-item .d-flex .nav-link {
            color: #333;
            text-decoration: none;
        }

        .reservation-item .d-flex .nav-link:hover {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>

<body class="animsition">
    <div class="page-wrapper">
        <!-- MENU SIDEBAR-->
        <aside class="menu-sidebar d-none d-lg-block">
            <div class="logo" style="background-color:white; padding: 10px;">
                <a href="dashboard.php" style="display: flex; justify-content: center; align-items: center;">
                    <img src="images/USJR.png" alt="USJR Logo" style="max-width: 100%; height: auto; object-fit: contain;">
                </a>
            </div>
            <nav class="navbar-sidebar">
                <ul class="list-unstyled navbar__list">
                    <li>
                        <a href="dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>       
                    <li>
                        <a href="products">
                            <i class="fas fa-project-diagram"></i> Manage Products
                        </a>
                    </li>
                    
                    <!-- Updated Reservation Menu Item -->
                    <li class="reservation-item">
                        <!-- Parent link goes to reservation.php -->
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="reservation" class="nav-link flex-grow-1">
                                <i class="fas fa-calendar-check"></i> Reservation
                            </a>
                            <!-- Toggle button for dropdown -->
                            <button type="button" class="btn btn-link p-0 ml-2 reservation-toggle" id="reservationToggle" aria-expanded="false">
                                <i class="fas fa-chevron-down reservation-arrow"></i>
                            </button>
                        </div>

                        <!-- Submenu -->
                        <ul class="list-unstyled reservation-submenu" id="reservationSubmenu">
                            <li>
                                <a href="Borrowed" class="nav-link pl-4">
                                    <i class="fas fa-box-open"></i> Borrowed
                                </a>
                            </li>
                            <hr class="dropdown-divider">
                            <li>
                                <a href="return" class="nav-link pl-4">
                                    <i class="fas fa-undo-alt"></i> Returned
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a href="Category">
                           <i class="fas fa-th-large"></i> Category
                        </a>
                    </li>
                    <li>
                        <a href="reports">
                            <i class="fas fa-info-circle"></i> Reports
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        <!-- END MENU SIDEBAR-->

        <!-- PAGE CONTAINER-->
        <div class="page-container">
            <!-- HEADER DESKTOP-->
            <header class="header-desktop">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <div class="header-wrap">
                            <form class="form-header" action="" method="POST">
                        
                            </form>
                            <div class="header-button">
                                <!-- Date and Time Display + Notification Icon -->
                                <div class="flex items-center datetime-notif-wrap" style="display: flex; align-items: center; gap: 0.75rem;">
                                    <!-- Notification Icon beside date/time, links to notification_view.php -->
                                    <div class="notif-icon-wrap" style="display: flex; align-items: center;">
                                        <a href="notification_view.php" title="Notifications" style="position: relative; color: #333; display: flex; align-items: center;">
                                            <i class="fas fa-bell fa-lg"></i>
                                            <?php if ($unreadCount > 0): ?>
                                                <span style="position: absolute; top: -8px; right: -10px; background: #f59e42; color: #fff; border-radius: 50%; font-size: 12px; padding: 2px 6px; min-width: 20px; text-align: center;">
                                                    <?php echo $unreadCount; ?>
                                                </span>
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                    <div class="datetime-display" style="margin-left: 0;">
                                        <div class="date"><?php echo $current_date; ?></div>
                                        <div class="time"><?php echo $current_time; ?></div>
                                        <div class="timezone"><?php echo $timezone; ?></div>
                                    </div>
                                </div>

                                <div class="account-wrap">
                                    <div class="account-item clearfix js-item-menu">
                                        <div class="image">
                                            <img src="images/icon/avatar-05.jpg" alt="User" />
                                        </div>
                                        <div class="content">
                                            <a class="js-acc-btn" href="#"><?php echo htmlspecialchars($user_name); ?></a>
                                        </div>
                                        <div class="account-dropdown js-dropdown">
                                            <div class="info clearfix">
                                                <div class="image">
                                                    <a href="#"><img src="images/icon/avatar-05.jpg" alt="User" /></a>
                                                </div>
                                                <div class="content">
                                                    <h5 class="name"><a href="#"><?php echo htmlspecialchars($user_name); ?></a></h5>
                                                    <span class="email"><?php echo htmlspecialchars($email); ?></span>
                                                </div>
                                            </div>
                                           <div class="account-dropdown__footer">
                                                <a href="#" id="logout-btn"><i class="zmdi zmdi-power"></i> Logout</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <!-- END HEADER DESKTOP-->
        </div>
        <!-- END PAGE CONTAINER-->
    </div>

    <!-- Jquery JS-->
    <script src="vendor/jquery-3.2.1.min.js"></script>
    <!-- Bootstrap JS-->
    <script src="vendor/bootstrap-4.1/popper.min.js"></script>
    <script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
    <!-- Vendor JS-->
    <script src="vendor/slick/slick.min.js"></script>
    <script src="vendor/wow/wow.min.js"></script>
    <script src="vendor/animsition/animsition.min.js"></script>
    <script src="vendor/select2/select2.min.js"></script>
    
    <!-- Main JS-->
    <script src="js/main.js"></script>

    <script>
        // Logout functionality
        document.getElementById("logout-btn").addEventListener("click", function(event) {
            event.preventDefault(); // Prevent immediate redirection
          
            window.location.href = "index.php?logout=1";
        });

        // Add JavaScript for notification interaction
        document.addEventListener('DOMContentLoaded', function() {
            // Make sure the notification dropdown is working
            const notificationItems = document.querySelectorAll('.noti__item.js-item-menu');
            
            notificationItems.forEach(item => {
                const dropdown = item.querySelector('.js-dropdown');
                
                item.addEventListener('click', function(e) {
                    if (e.target.closest('.js-dropdown') === null) {
                        e.preventDefault();
                        dropdown.classList.toggle('show-dropdown');
                    }
                });
                
                document.addEventListener('click', function(e) {
                    if (!item.contains(e.target)) {
                        dropdown.classList.remove('show-dropdown');
                    }
                });
            });
            
            const notificationLinks = document.querySelectorAll('.notifi__item');
            notificationLinks.forEach(link => {
                link.addEventListener('click', function() {
                    const notificationId = this.id.split('_')[1];

                    fetch('mark_notification_read.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id=' + notificationId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Visual indication that notification is read
                            this.classList.remove('unread');
                            this.classList.add('read');

                            // Update the counter
                            const counter = document.querySelector('.noti__item .quantity');
                            let count = parseInt(counter.textContent);
                            if (count > 0) {
                                counter.textContent = count - 1;
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error marking notification as read:', error);
                    });
                });
            });
        });

        // Updated reservation dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const reservationToggle = document.getElementById('reservationToggle');
            const reservationSubmenu = document.getElementById('reservationSubmenu');
            
            if (reservationToggle && reservationSubmenu) {
                reservationToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Toggle the dropdown
                    const isOpen = reservationSubmenu.classList.contains('show');
                    
                    if (isOpen) {
                        // Close dropdown
                        reservationSubmenu.classList.remove('show');
                        reservationToggle.classList.remove('active');
                        reservationToggle.setAttribute('aria-expanded', 'false');
                    } else {
                        // Open dropdown
                        reservationSubmenu.classList.add('show');
                        reservationToggle.classList.add('active');
                        reservationToggle.setAttribute('aria-expanded', 'true');
                    }
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!reservationToggle.contains(e.target) && !reservationSubmenu.contains(e.target)) {
                        reservationSubmenu.classList.remove('show');
                        reservationToggle.classList.remove('active');
                        reservationToggle.setAttribute('aria-expanded', 'false');
                    }
                });
                
                // Prevent submenu links from closing the dropdown when clicked
                reservationSubmenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });

        // Update time functionality
        function updateTime() {
            const now = new Date();
            const options = { hour: 'numeric', minute: 'numeric', hour12: true };
            const timeElement = document.querySelector('.datetime-display .time');
            if (timeElement) {
                timeElement.textContent = now.toLocaleTimeString([], options);
            }
        }
        
        // Update time every second
        setInterval(updateTime, 1000);
    </script>
</body>
</html>