<?php

session_start();

include 'index/header.php';
include 'index/navigation.php';
include 'config.php';


$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

// Get unread count (user-specific and global)
$unread_query = "SELECT COUNT(*) as unread_count FROM notifications WHERE is_read = 0 AND (user_id = ? OR user_id IS NULL)";
$unread_stmt = $conn->prepare($unread_query);
$unread_stmt->bind_param('i', $user_id);
$unread_stmt->execute();
$unread_result = $unread_stmt->get_result();
$unread_count = 0;
if ($unread_result && $row = $unread_result->fetch_assoc()) {
    $unread_count = $row['unread_count'];
}
$unread_stmt->close();

// Determine if "See All" is requested
$show_all = isset($_GET['all']) && $_GET['all'] == '1';

// Get total notification count for the user and global
$total_query = "SELECT COUNT(*) as total_count FROM notifications WHERE (user_id = ? OR user_id IS NULL)";
$total_stmt = $conn->prepare($total_query);
$total_stmt->bind_param('i', $user_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_count = 0;
if ($total_result && $row = $total_result->fetch_assoc()) {
    $total_count = $row['total_count'];
}
$total_stmt->close();

// Fetch notifications for the user and global (limit to 10 unless "See All" is set)
if ($show_all) {
    $query = "SELECT id, user_id, message, is_read, created_at, reservation_code, product_id FROM notifications WHERE (user_id = ? OR user_id IS NULL) ORDER BY created_at DESC";
} else {
    $query = "SELECT id, user_id, message, is_read, created_at, reservation_code, product_id FROM notifications WHERE (user_id = ? OR user_id IS NULL) ORDER BY created_at DESC LIMIT 10";
}
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Notif</title>
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <style>
        :root {
            --sidebar-width: 220px;
            --header-height: 60px;
            --border-radius-lg: 18px;
            --border-radius-md: 12px;
            --border-radius-sm: 8px;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.03);
            --shadow-md: 0 4px 16px rgba(13,110,253,0.08);
            --primary-color: #0d6efd;
            --danger-color: #dc3545;
            --text-muted: #6c757d;
            --bg-light: #f8fafc;
            --bg-secondary: #f7f9fb;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, var(--bg-light) 0%, #e9ecef 100%);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .main-content {
            margin-left: 0;
            padding: 15px;
            min-height: 100vh;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        /* Desktop Layout */
        @media (min-width: 992px) {
            .main-content {
                margin-left: var(--sidebar-width);
                width: calc(100vw - var(--sidebar-width));
                padding: 25px;
            }
        }

        /* Container Responsive Design */
        .notifications-container {
            max-width: 900px;         /* Set a reasonable max width */
            margin: 0 auto;           /* Center the container */
            width: 90%;
            padding: 0 20px;          /* Add horizontal padding */
            box-sizing: border-box;
        }

        @media (min-width: 1400px) {
            .notifications-container {
                max-width: 1100px;    /* Adjust for very large screens */
            }
        }

        @media (min-width: 1200px) {
            .notifications-container {
                max-width: 1000px;
            }
        }

        /* Medium screens */
        @media (min-width: 992px) and (max-width: 1199.98px) {
            .notifications-container {
                max-width: 900px;
            }
        }

        /* Tablet screens */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .main-content {
                margin-left: 0;
                padding: 20px 15px;
            }
            .notifications-container {
                max-width: 100%;
                padding: 0 10px;
            }
        }

        /* Small tablets and large phones */
        @media (min-width: 576px) and (max-width: 767.98px) {
            .main-content {
                padding: 15px 10px;
            }
            .notifications-container {
                padding: 0 5px;
            }
        }

        /* Mobile phones */
        @media (max-width: 575.98px) {
            .main-content {
                padding: 10px 5px;
            }
            .notifications-container {
                padding: 0;
            }
        }

        /* Card Responsive Design */
        .card.shadow-sm {
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            border: none;
            background: #fff;
            overflow: hidden;
        }

        @media (max-width: 575.98px) {
            .card.shadow-sm {
                border-radius: var(--border-radius-md);
                margin: 0 2px;
            }
        }

        /* Header Responsive Design */
        .card-header {
            background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
            border-bottom: 1px solid #e9ecef;
            padding: 20px 25px;
        }

        @media (max-width: 767.98px) {
            .card-header {
                padding: 15px 20px;
            }
        }

        @media (max-width: 575.98px) {
            .card-header {
                padding: 12px 15px;
            }
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        @media (max-width: 767.98px) {
            .header-content {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
        }

        @media (max-width: 767.98px) {
            .header-left {
                justify-content: space-between;
                width: 100%;
            }
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.25rem;
            font-weight: 700;
            color: #2c3e50;
        }

        @media (max-width: 767.98px) {
            .header-title {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 575.98px) {
            .header-title {
                font-size: 1rem;
            }
        }

        /* Badge Responsive */
        .badge {
            font-size: 0.7rem;
            padding: 4px 8px;
        }

        @media (max-width: 575.98px) {
            .badge {
                font-size: 0.65rem;
                padding: 3px 6px;
            }
        }

        /* Button Responsive */
        .btn {
            border-radius: var(--border-radius-sm);
            font-weight: 500;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }

        @media (max-width: 575.98px) {
            .btn-sm {
                padding: 5px 10px;
                font-size: 0.8rem;
            }
        }

        /* Search Container Responsive */
        .search-container {
            position: relative;
            margin: 20px 25px 0 25px;
        }

        @media (max-width: 767.98px) {
            .search-container {
                margin: 15px 20px 0 20px;
            }
        }

        @media (max-width: 575.98px) {
            .search-container {
                margin: 12px 15px 0 15px;
            }
        }
        
        .search-input {
            width: 100%;
            padding: 12px 45px 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-md);
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #fff;
        }

        @media (max-width: 575.98px) {
            .search-input {
                padding: 10px 40px 10px 14px;
                font-size: 0.9rem;
            }
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }
        
        .search-icon, .clear-search {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        @media (max-width: 575.98px) {
            .search-icon, .clear-search {
                right: 12px;
                font-size: 1rem;
            }
        }
        
        .clear-search {
            background: none;
            border: none;
            cursor: pointer;
            display: none;
        }
        
        .clear-search:hover {
            color: var(--danger-color);
        }
        
        /* Notification Container Responsive */
        .notification-container {
            max-height: 70vh;
            overflow-y: auto;
            background: var(--bg-secondary);
        }

        @media (max-width: 767.98px) {
            .notification-container {
                max-height: 65vh;
            }
        }

        @media (max-width: 575.98px) {
            .notification-container {
                max-height: 60vh;
            }
        }
        
        /* Notification Item Responsive */
        .notification-item {
            padding: 20px 25px;
            border-bottom: 1px solid #f0f0f0;
            position: relative;
            transition: all 0.3s ease;
            background: #fff;
            border-radius: var(--border-radius-md);
            margin: 0 0 18px 0;       /* Only bottom margin for spacing between cards */
            box-shadow: var(--shadow-sm);
        }

        @media (max-width: 767.98px) {
            .notification-item {
                padding: 15px 20px;
                margin: 12px;
            }
        }

        @media (max-width: 575.98px) {
            .notification-item {
                padding: 12px 15px;
                margin: 8px;
                border-radius: var(--border-radius-sm);
            }
        }
        
        .notification-item:hover {
            background: #f0f4ff;
            box-shadow: var(--shadow-md);
            transform: translateY(-2px) scale(1.01);
        }

        @media (max-width: 767.98px) {
            .notification-item:hover {
                transform: translateY(-1px) scale(1.005);
            }
        }
        
        .notification-item:last-child {
            border-bottom: none;
            margin-bottom: 15px;
        }
        
        .notification-item.unread {
            background: linear-gradient(90deg, #eaf1ff 80%, var(--bg-secondary) 100%);
            border-left: 5px solid var(--primary-color);
        }
        
        .notification-item.read {
            opacity: 0.85;
        }
        
        .notification-item.hidden {
            display: none;
        }
        
        /* Notification Header Responsive */
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
            flex-wrap: wrap;
            gap: 10px;
        }

        @media (max-width: 767.98px) {
            .notification-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }

        @media (max-width: 575.98px) {
            .notification-header {
                gap: 6px;
            }
        }
        
        .unread-indicator {
            width: 10px;
            height: 10px;
            background: var(--danger-color);
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            box-shadow: 0 0 6px var(--danger-color);
        }

        @media (max-width: 575.98px) {
            .unread-indicator {
                width: 8px;
                height: 8px;
                margin-right: 6px;
            }
        }
        
        .notification-role {
            font-size: 0.8rem;
            padding: 5px 14px;
            border-radius: 16px;
            font-weight: 700;
            letter-spacing: 0.5px;
            background: linear-gradient(90deg, #ff5e62 0%, #ff9966 100%);
            color: #fff;
            box-shadow: 0 2px 8px rgba(255,94,98,0.08);
        }

        @media (max-width: 575.98px) {
            .notification-role {
                font-size: 0.75rem;
                padding: 4px 12px;
                letter-spacing: 0.3px;
            }
        }
        
        .notification-role.bg-primary {
            background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%);
        }
        
        .notification-role.bg-danger {
            background: linear-gradient(90deg, #ff5e62 0%, #ff9966 100%);
        }
        
        .notification-date {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        @media (max-width: 767.98px) {
            .notification-date {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 575.98px) {
            .notification-date {
                font-size: 0.75rem;
            }
        }
        
        /* Notification Body Responsive */
        .notification-body {
            margin-top: 8px;
        }
        
        .notification-message {
            font-size: 1rem;
            color: #222;
            line-height: 1.6;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        @media (max-width: 767.98px) {
            .notification-message {
                font-size: 0.95rem;
                line-height: 1.5;
            }
        }

        @media (max-width: 575.98px) {
            .notification-message {
                font-size: 0.9rem;
            }
        }
        
        .notification-message a.btn-outline-primary {
            font-size: 0.9rem;
            padding: 6px 16px;
            border-radius: var(--border-radius-sm);
            margin-top: 8px;
            display: inline-block;
        }

        @media (max-width: 575.98px) {
            .notification-message a.btn-outline-primary {
                font-size: 0.85rem;
                padding: 5px 12px;
                margin-top: 6px;
            }
        }
        
        /* Empty States Responsive */
        .empty-notifications, .no-results {
            padding: 60px 20px;
            background: var(--bg-secondary);
            text-align: center;
        }

        @media (max-width: 767.98px) {
            .empty-notifications, .no-results {
                padding: 40px 15px;
            }
        }

        @media (max-width: 575.98px) {
            .empty-notifications, .no-results {
                padding: 30px 10px;
            }
        }

        .empty-notifications {
            border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);
        }

        @media (max-width: 575.98px) {
            .empty-notifications {
                border-radius: 0 0 var(--border-radius-md) var(--border-radius-md);
            }
        }
        
        /* See All Container Responsive */
        .see-all-container {
            border-top: 1px solid #f0f0f0;
            background: var(--bg-secondary);
            padding: 18px 0 10px 0;
            border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);
            text-align: center;
        }

        @media (max-width: 575.98px) {
            .see-all-container {
                padding: 15px 0 8px 0;
                border-radius: 0 0 var(--border-radius-md) var(--border-radius-md);
            }
        }
        
        /* Scrollbar Styling */
        .notification-container::-webkit-scrollbar {
            width: 6px;
        }

        @media (max-width: 575.98px) {
            .notification-container::-webkit-scrollbar {
                width: 4px;
            }
        }
        
        .notification-container::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .notification-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        .notification-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Animation */
        .notification-item {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Search Highlight */
        .search-highlight {
            background-color: #ffeb3b;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: 600;
        }

        /* Touch Improvements for Mobile */
        @media (hover: none) and (pointer: coarse) {
            .notification-item:hover {
                transform: none;
                background: #fff;
            }
            
            .notification-item:active {
                background: #f0f4ff;
                transform: scale(0.98);
            }
            
            .btn:hover {
                transform: none;
            }
            
            .btn:active {
                transform: scale(0.95);
            }
        }

        /* Landscape Phone Adjustments */
        @media (max-width: 767.98px) and (orientation: landscape) {
            .notification-container {
                max-height: 50vh;
            }
            
            .notification-item {
                padding: 10px 15px;
                margin: 6px;
            }
        }

        /* High DPI Display Adjustments */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .unread-indicator {
                box-shadow: 0 0 4px var(--danger-color);
            }
            
            .notification-item {
                box-shadow: 0 1px 4px rgba(0,0,0,0.05);
            }
        }

        /* Accessibility Improvements */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
            
            .notification-item:hover {
                transform: none;
            }
            
            .btn:hover {
                transform: none;
            }
        }

        /* Focus Styles for Keyboard Navigation */
        .btn:focus,
        .search-input:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        /* Print Styles */
        @media print {
            .search-container,
            .card-header .btn,
            .see-all-container {
                display: none !important;
            }
            
            .notification-container {
                max-height: none !important;
                overflow: visible !important;
            }
            
            .notification-item {
                break-inside: avoid;
                margin: 10px 0;
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }

        /* Custom Modal Width */
        .modal-dialog {
            max-width: 900px;
            width: 95%;
            margin: 1.75rem auto;
        }
        @media (max-width: 991.98px) {
            .modal-dialog {
                max-width: 98vw;
                width: 98vw;
            }
        }
        @media (max-width: 575.98px) {
            .modal-dialog {
                max-width: 100vw;
                width: 100vw;
                margin: 0;
            }
        }
    </style>
</head>
<body class="animsition">
    <div class="main-content">
        <div class="notifications-container">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="header-content">
                        <div class="header-left">
                            <button class="btn btn-sm btn-outline-grey" onclick="location.href='dashboard.php'" aria-label="Back to dashboard">
                                <i class="fa fa-arrow-left"></i>
                            </button>
                            <div class="header-title">
                                <i class="fa fa-bell"></i>
                                <span>Notifications</span>
                                <?php if ($unread_count > 0): ?>
                                    <span class="badge bg-danger rounded-pill" id="unread-badge"><?php echo $unread_count; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($unread_count > 0): ?>
                        <button id="markAllRead" class="btn btn-sm btn-outline-grey">
                            <i class="fa fa-check-double me-1"></i> 
                            <span class="d-none d-sm-inline">Mark All as Read</span>
                            <span class="d-sm-none">Mark All</span>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="search-container">
                    <input type="text" id="searchInput" class="search-input" placeholder="Search notifications..." aria-label="Search notifications">
                    <i class="fa fa-search search-icon" aria-hidden="true"></i>
                    <button class="clear-search" id="clearSearch" aria-label="Clear search">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <div class="notification-container" id="notificationContainer" role="main" aria-live="polite">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="notification-item <?php echo ($row['is_read'] ? 'read' : 'unread'); ?>" 
                                 data-id="<?php echo (int) $row['id']; ?>"
                                 data-read="<?php echo $row['is_read'] ? '1' : '0'; ?>"
                                 data-search-content="<?php echo strtolower(htmlspecialchars($row['message']) . ' ' . (is_null($row['user_id']) ? 'admin' : 'personal')); ?>"
                                 role="article"
                                 aria-label="<?php echo $row['is_read'] ? 'Read notification' : 'Unread notification'; ?>">
                                <div class="notification-content">
                                    <div class="notification-header">
                                        <div class="d-flex align-items-center flex-wrap">
                                            <?php if (!$row['is_read']): ?>
                                                <div class="unread-indicator" aria-label="Unread"></div>
                                            <?php endif; ?>
                                            <span class="notification-role badge <?php echo is_null($row['user_id']) ? 'bg-danger' : 'bg-primary'; ?>">
                                                <?php echo is_null($row['user_id']) ? 'Admin' : 'Personal'; ?>
                                            </span>
                                        </div>
                                        <span class="notification-date text-muted">
                                            <i class="fa fa-clock me-1" aria-hidden="true"></i>
                                            <time datetime="<?php echo date('c', strtotime($row['created_at'])); ?>">
                                                <?php echo date('M j, Y g:ia', strtotime($row['created_at'])); ?>
                                            </time>
                                        </span>
                                    </div>
                                    <div class="notification-body">
                                        <div class="notification-message">
                                            <?php echo htmlspecialchars_decode($row['message']); ?>
                                            <?php if (!empty($row['reservation_code'])): ?>
                                                <div class="mt-2">
                                                    <a href="reservation.php?code=<?php echo htmlspecialchars($row['reservation_code']); ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fa fa-eye me-1" aria-hidden="true"></i>
                                                        <span class="d-none d-sm-inline">View Reservation</span>
                                                        <span class="d-sm-none">View</span>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        
                        <div class="no-results" id="noResults" style="display: none;" role="status" aria-live="polite">
                            <i class="fa fa-search fa-3x text-muted mb-3" aria-hidden="true"></i>
                            <h5 class="text-muted">No notifications found</h5>
                            <p class="text-muted">Try adjusting your search terms</p>
                        </div>
                        
                        <?php if (!$show_all && $total_count > 10): ?>
                            <div class="see-all-container text-center py-3">
                                <a href="?all=1" class="btn btn-outline-primary">
                                    <i class="fa fa-list me-1" aria-hidden="true"></i>
                                    <span class="d-none d-sm-inline">See All Notifications (<?php echo $total_count; ?>)</span>
                                    <span class="d-sm-none">See All (<?php echo $total_count; ?>)</span>
                                </a>
                            </div>
                        <?php elseif ($show_all && $total_count > 10): ?>
                            <div class="see-all-container text-center py-3">
                                <a href="notification_view.php" class="btn btn-outline-secondary">
                                    <i class="fa fa-compress me-1" aria-hidden="true"></i>
                                    <span class="d-none d-sm-inline">Show Recent Only</span>
                                    <span class="d-sm-none">Recent Only</span>
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="empty-notifications text-center py-5" role="status">
                            <i class="fa fa-bell-slash fa-3x text-muted mb-3" aria-hidden="true"></i>
                            <h5 class="text-muted">No notifications yet</h5>
                            <p class="text-muted">You're all caught up!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Search functionality
        const searchInput = $('#searchInput');
        const clearButton = $('#clearSearch');
        const notificationItems = $('.notification-item');
        const noResults = $('#noResults');
        
        searchInput.on('input', function() {
            const searchTerm = $(this).val().toLowerCase().trim();
            
            if (searchTerm === '') {
                clearButton.hide();
                notificationItems.removeClass('hidden');
                noResults.hide();
                removeHighlights();
                return;
            }
            
            clearButton.show();
            let visibleCount = 0;
            
            notificationItems.each(function() {
                const $item = $(this);
                const content = $item.data('search-content');
                
                if (content.includes(searchTerm)) {
                    $item.removeClass('hidden');
                    highlightText($item, searchTerm);
                    visibleCount++;
                } else {
                    $item.addClass('hidden');
                }
            });
            
            if (visibleCount === 0) {
                noResults.show();
            } else {
                noResults.hide();
            }
        });
        
        clearButton.on('click', function() {
            searchInput.val('');
            $(this).hide();
            notificationItems.removeClass('hidden');
            noResults.hide();
            removeHighlights();
        });
        
        // Highlight search terms
        function highlightText($item, searchTerm) {
            const $message = $item.find('.notification-message');
            let html = $message.html();
            
            // Remove existing highlights
            html = html.replace(/<span class="search-highlight">(.*?)<\/span>/gi, '$1');
            
            // Add new highlights
            const regex = new RegExp(`(${escapeRegExp(searchTerm)})`, 'gi');
            html = html.replace(regex, '<span class="search-highlight">$1</span>');
            
            $message.html(html);
        }
        
        function removeHighlights() {
            $('.notification-message').each(function() {
                let html = $(this).html();
                html = html.replace(/<span class="search-highlight">(.*?)<\/span>/gi, '$1');
                $(this).html(html);
            });
        }
        
        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }
        
        // Auto-mark unread notifications as read when they come into view
        function markAsReadOnView() {
            $('.notification-item.unread:not(.hidden)').each(function() {
                var $item = $(this);
                var notificationId = $item.data('id');
                
                // Check if the notification is visible in viewport
                if (isInViewport($item[0])) {
                    markSingleAsRead(notificationId, $item);
                }
            });
        }

        // Check if element is in viewport
        function isInViewport(element) {
            var rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }

        // Mark single notification as read
        function markSingleAsRead(notificationId, $item) {
            $.ajax({
                type: 'POST',
                url: 'mark_single_read.php',
                data: { notification_id: notificationId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $item.removeClass('unread').addClass('read');
                        $item.find('.unread-indicator').fadeOut();
                        $item.attr('data-read', '1');
                        
                        // Update unread badge
                        updateUnreadBadge(-1);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error marking notification as read:", error);
                }
            });
        }

        // Update unread badge count
        function updateUnreadBadge(change) {
            var $badge = $('#unread-badge');
            if ($badge.length) {
                var currentCount = parseInt($badge.text(), 10) || 0;
                var newCount = currentCount + change;
                
                if (newCount <= 0) {
                    $badge.fadeOut(function() {
                        $(this).remove();
                        $('#markAllRead').fadeOut();
                    });
                } else {
                    $badge.text(newCount);
                }
            }
        }

        // Mark all notifications as read with SweetAlert confirmation
        $('#markAllRead').click(function() {
            var $button = $(this);
            Swal.fire({
                title: 'Mark all as read?',
                text: "This will mark all notifications as read.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Yes, mark all!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Processing...');
                    $.ajax({
                        type: 'POST',
                        url: 'mark_all_as_read.php',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                $('.notification-item.unread').removeClass('unread').addClass('read');
                                $('.unread-indicator').fadeOut();
                                $('#unread-badge').fadeOut(function() {
                                    $(this).remove();
                                });
                                $button.fadeOut();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Marked as read!',
                                    text: `Marked ${response.affected_rows} notification(s) as read`,
                                    timer: 2000,
                                    showConfirmButton: false,
                                    toast: true,
                                    position: 'top-end'
                                });
                            } else {
                                let msg = response.error || 'Failed to mark notifications as read. Please try again.';
                                if (response.error1) msg += ' | ' + response.error1;
                                if (response.error2) msg += ' | ' + response.error2;
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: msg,
                                    confirmButtonColor: '#00016b'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to mark notifications as read. Please try again.',
                                confirmButtonColor: '#00016b'
                            });
                        },
                        complete: function() {
                            $button.prop('disabled', false).html('<i class="fa fa-check-double me-1"></i> Mark All as Read');
                        }
                    });
                }
            });
        });

        // Initial check and setup scroll listener
        markAsReadOnView();
        
        // Check for new notifications coming into view on scroll
        $('.notification-container').on('scroll', function() {
            markAsReadOnView();
        });
        
        // Also check on window scroll
        $(window).on('scroll', function() {
            markAsReadOnView();
        });
    });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>