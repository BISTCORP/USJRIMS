<?php
session_start();
?>

<?php
// Include your header and navigation files
include 'config.php'; // Ensure the user is authenticated and database is connected

// Use the monitoring database for all dashboard queries
mysqli_select_db($conn, 'monitoring');

include 'index/header.php';
include 'index/navigation.php';

// Dashboard data from database
function getTotalProducts($conn) {
        // Connects to inventory_products table
    $sql = "SELECT COUNT(*) as total FROM inventory_products";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}
function getLowStock($conn) {
    // Connects to inventory_products table for low stock
    $sql = "SELECT COUNT(*) as low_stock FROM inventory_products WHERE quantity_in_stock <= reorder_level";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['low_stock'];
}
function getReservationCount($conn) {
    // Connects to reservation table for total reservations
    $sql = "SELECT COUNT(*) as total FROM reservation";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}
function getReservationStatusCount($conn, $status) {
    // Connects to reservation table for specific status count
    $sql = "SELECT COUNT(*) as total FROM reservation WHERE status = '$status'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}
function getCartCount($conn) {
    // Connects to cart table for total cart items
    $sql = "SELECT COUNT(*) as total FROM cart";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// --- Add Damage Reports Overview Count ---
function getDamageReportCount($conn) {
    $sql = "SELECT COUNT(*) as total FROM damage_reports";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}
$totalDamageReports = getDamageReportCount($conn);

// Add this function to calculate system performance percentage
function getSystemPerformance($conn) {
    // Use actual returns count for "returned"
    $returnsResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM returns");
    $returnsRow = mysqli_fetch_assoc($returnsResult);
    $returned = intval($returnsRow['total']);

    $borrowed = getReservationStatusCount($conn, 'borrowed');
    $pending = getReservationStatusCount($conn, 'pending');
    $damage = getDamageReportCount($conn);

    // Use total transactions as sum of returns, borrowed, and damage (exclude pending)
    $total = $returned + $borrowed + $damage;

    // If there are no transactions, show 100%
    if ($total == 0) return 100;

    // Performance: returned + borrowed as a percentage of all transactions (exclude pending)
    $performance = (($returned + $borrowed) / $total) * 100;

    // Clamp between 0 and 100, round to 2 decimals
    $performance = max(0, min(100, round($performance, 2)));
    return $performance;
}

$systemPerformance = getSystemPerformance($conn);

// AJAX endpoint for live system status
if (isset($_GET['ajax']) && $_GET['ajax'] === 'system_status') {
    header('Content-Type: application/json');
    $data = [
        'systemPerformance' => $systemPerformance,
        'returnedCount' => $returnedCount,
        'borrowedCount' => $borrowedCount,
        'pendingCount' => $pendingCount,
        'reservationCount' => $reservationCount,
        'damageCount' => $totalDamageReports,
        'lowStock' => $lowStock
    ];
    echo json_encode($data);
    exit();
}

// Fetch values directly from the database
$totalProducts = getTotalProducts($conn);      // inventory_products
$lowStock = getLowStock($conn);                // inventory_products (low stock)
$reservationCount = getReservationCount($conn); // reservation

// --- Fix: Use borrowed_items and returns tables for borrowed and returned counts ---
$borrowedCount = 0;
$borrowedResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM borrowed_items");
if ($borrowedResult) {
    $row = mysqli_fetch_assoc($borrowedResult);
    $borrowedCount = intval($row['total']);
}

$returnedCount = 0;
$returnedResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM returns");
if ($returnedResult) {
    $row = mysqli_fetch_assoc($returnedResult);
    $returnedCount = intval($row['total']);
}

$pendingCount = getReservationStatusCount($conn, 'pending');   // reservation (pending)
$cartCount = getCartCount($conn);              // cart

// Function to generate overview boxes with modern design
function generateOverviewBoxes(
    $totalProducts, $lowStock,
    $reservationCount, $returnedCount, $borrowedCount, $pendingCount, $cartCount,
    $totalDamageReports
) {
    echo "
    <div class='dashboard-grid'>
        <div class='metric-card metric-card--primary'>
            <div class='metric-card__content'>
                <div class='metric-card__icon'>
                    <i class='fa fa-cubes'></i>
                </div>
                <div class='metric-card__info'>
                    <div class='metric-card__number'>$totalProducts</div>
                    <div class='metric-card__label'>Total Products</div>
                </div>
            </div>
            <div class='metric-card__footer'>
                <span class='metric-card__trend metric-card__trend--positive'>
                    <i class='fa fa-arrow-up'></i>
                    Added this month
                </span>
            </div>
        </div>
        
        <div class='metric-card metric-card--danger'>
            <div class='metric-card__content'>
                <div class='metric-card__icon'>
                    <i class='fa fa-exclamation-triangle'></i>
                </div>
                <div class='metric-card__info'>
                    <div class='metric-card__number'>$lowStock</div>
                    <div class='metric-card__label'>Low Stock</div>
                </div>
            </div>
            <div class='metric-card__footer'>
                <span class='metric-card__trend metric-card__trend--warning'>
                    <i class='fa fa-exclamation-circle'></i>
                    Needs attention
                </span>
            </div>
        </div>

        <!-- Damage Reports Overview (Single Box) -->
        <div class='metric-card metric-card--danger'>
            <div class='metric-card__content'>
                <div class='metric-card__icon'>
                    <i class='fa fa-bolt'></i>
                </div>
                <div class='metric-card__info'>
                    <div class='metric-card__number'>$totalDamageReports</div>
                    <div class='metric-card__label'>Damage Reports</div>
                </div>
            </div>
            <div class='metric-card__footer'>
                <span class='metric-card__trend metric-card__trend--warning'>
                    <i class='fa fa-exclamation-triangle'></i>
                    Total
                </span>
            </div>
        </div>
        <!-- End Damage Reports Overview -->

        <div class='metric-card metric-card--primary'>
            <div class='metric-card__content'>
                <div class='metric-card__icon'>
                    <i class='fa fa-calendar'></i>
                </div>
                <div class='metric-card__info'>
                    <div class='metric-card__number'>$reservationCount</div>
                    <div class='metric-card__label'>Reservations</div>
                </div>
            </div>
            <div class='metric-card__footer'>
                <span class='metric-card__trend metric-card__trend--positive'>
                    <i class='fa fa-calendar'></i>
                    All time
                </span>
            </div>
        </div>
        
        <div class='metric-card metric-card--success'>
            <div class='metric-card__content'>
                <div class='metric-card__icon'>
                    <i class='fa fa-check-circle'></i>
                </div>
                <div class='metric-card__info'>
                    <div class='metric-card__number'>$returnedCount</div>
                    <div class='metric-card__label'>Returned</div>
                </div>
            </div>
            <div class='metric-card__footer'>
                <span class='metric-card__trend metric-card__trend--positive'>
                    <i class='fa fa-check'></i>
                    Completed
                </span>
            </div>
        </div>
        
        <div class='metric-card metric-card--info'>
            <div class='metric-card__content'>
                <div class='metric-card__icon'>
                    <i class='fa fa-arrow-circle-right'></i>
                </div>
                <div class='metric-card__info'>
                    <div class='metric-card__number'>$borrowedCount</div>
                    <div class='metric-card__label'>Borrowed</div>
                </div>
            </div>
            <div class='metric-card__footer'>
                <span class='metric-card__trend metric-card__trend--warning'>
                    <i class='fa fa-user'></i>
                    Active loans
                </span>
            </div>
        </div>
        
        <div class='metric-card metric-card--danger'>
            <div class='metric-card__content'>
                <div class='metric-card__icon'>
                    <i class='fa fa-clock-o'></i>
                </div>
                <div class='metric-card__info'>
                    <div class='metric-card__number'>$pendingCount</div>
                    <div class='metric-card__label'>Pending</div>
                </div>
            </div>
            <div class='metric-card__footer'>
                <span class='metric-card__trend metric-card__trend--warning'>
                    <i class='fa fa-exclamation-circle'></i>
                    Needs attention
                </span>
            </div>
        </div>
        
        <div class='metric-card metric-card--secondary'>
            <div class='metric-card__content'>
                <div class='metric-card__icon'>
                    <i class='fa fa-shopping-cart'></i>
                </div>
                <div class='metric-card__info'>
                    <div class='metric-card__number'>$cartCount</div>
                    <div class='metric-card__label'>Cart Items</div>
                </div>
            </div>
            <div class='metric-card__footer'>
                <span class='metric-card__trend metric-card__trend--positive'>
                    <i class='fa fa-shopping-cart'></i>
                    In cart
                </span>
            </div>
        </div>
    </div>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dashboard</title>
    <link rel="icon" type="image/x-icon" href="images/logo.ico">
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        :root {
            --primary-color: #667eea;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --danger-color: #f56565;
            --danger-gradient: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
            --warning-color: #ed8936;
            --warning-gradient: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
            --success-color: #48bb78;
            --success-gradient: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            --secondary-color: #9f7aea;
            --secondary-gradient: linear-gradient(135deg, #9f7aea 0%, #805ad5 100%);
            --info-color: #4299e1;
            --info-gradient: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            --background-color: #f8fafc;
            --card-background: #ffffff;
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --border-radius: 12px;
            --border-radius-lg: 16px;
        }

        body {
            background-color: var(--background-color);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        h1, h2 {
            margin-top: 0 !important;
            padding-top: 0 !important;
            color: var(--text-primary);
            font-weight: 700;
        }

        .section__content--p30 {
            padding: 2rem !important;
        }

        .overview-wrap {
            margin: 0 0 2rem 0 !important;
            padding: 0 !important;
        }

        .main-content {
            padding-top: 0 !important;
            min-height: 100vh;
        }

        .title-1 {
            font-size: 2rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        /* Modern Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        /* Modern Metric Cards */
        .metric-card {
            background: var(--card-background);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(226, 232, 240, 0.8);
            overflow: hidden;
            position: relative;
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .metric-card--primary::before { background: var(--primary-gradient); }
        .metric-card--danger::before { background: var(--danger-gradient); }
        .metric-card--warning::before { background: var(--warning-gradient); }
        .metric-card--success::before { background: var(--success-gradient); }
        .metric-card--secondary::before { background: var(--secondary-gradient); }
        .metric-card--info::before { background: var(--info-gradient); }

        .metric-card__content {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .metric-card__icon {
            width: 64px;
            height: 64px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            flex-shrink: 0;
        }

        .metric-card--primary .metric-card__icon { background: var(--primary-gradient); }
        .metric-card--danger .metric-card__icon { background: var(--danger-gradient); }
        .metric-card--warning .metric-card__icon { background: var(--warning-gradient); }
        .metric-card--success .metric-card__icon { background: var(--success-gradient); }
        .metric-card--secondary .metric-card__icon { background: var(--secondary-gradient); }
        .metric-card--info .metric-card__icon { background: var(--info-gradient); }

        .metric-card__info {
            flex: 1;
        }

        .metric-card__number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .metric-card__label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .metric-card__footer {
            padding: 1rem 1.5rem;
            background-color: rgba(248, 250, 252, 0.8);
            border-top: 1px solid rgba(226, 232, 240, 0.5);
        }

        .metric-card__trend {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .metric-card__trend--positive { color: var(--success-color); }
        .metric-card__trend--negative { color: var(--danger-color); }
        .metric-card__trend--warning { color: var(--warning-color); }

        /* Modern Chart Card */
        .modern-card {
            background: var(--card-background);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(226, 232, 240, 0.8);
            overflow: hidden;
        }

        .modern-card__header {
            padding: 1.5rem 1.5rem 1rem 1.5rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        }

        .modern-card__title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .modern-card__body {
            padding: 1.5rem;
        }

        /* Charts and Status Layout */
        .charts-status-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        /* System Status Card */
        .status-card {
            background: var(--card-background);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(226, 232, 240, 0.8);
            overflow: hidden;
        }

        .status-card__header {
            padding: 1.5rem 1.5rem 1rem 1.5rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-card__title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--success-gradient);
            color: white;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-badge::before {
            content: '';
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .status-card__body {
            padding: 1.5rem;
        }

        .status-metric {
            text-align: center;
            margin-bottom: 2rem;
        }

        .status-circle {
            width: 90px;
            height: 90px;
            margin: 0 auto 1rem;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .status-circle svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }

        .status-circle__bg {
            fill: none;
            stroke: #e2e8f0;
            stroke-width: 8;
        }

        .status-circle__progress {
            fill: none;
            stroke: var(--success-color);
            stroke-width: 8;
            stroke-linecap: round;
            stroke-dasharray: 377;
            stroke-dashoffset: 377;
            transition: stroke-dashoffset 0.5s ease;
        }

        .status-percentage {
            position: absolute;
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: 0.01em;
        }

        @media (max-width: 991.98px) {
            .status-circle {
                width: 70px;
                height: 70px;
            }
            .status-percentage {
                font-size: 1rem;
            }
        }
        @media (max-width: 575.98px) {
            .status-circle {
                width: 54px;
                height: 54px;
            }
            .status-percentage {
                font-size: 0.85rem;
            }
        }

        .status-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .status-items {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
        }

        .status-item:last-child {
            border-bottom: none;
        }

        .status-item__info h4 {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0 0 0.25rem 0;
        }

        .status-item__info p {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin: 0;
        }

        .status-item__time {
            font-size: 0.75rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* Copyright Footer */
        .copyright {
            margin-top: 3rem;
            padding: 2rem 0;
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.875rem;
            border-top: 1px solid rgba(226, 232, 240, 0.5);
        }

        /* Responsive Design */
        @media (max-width: 767.98px) {
            .section__content--p30 {
                padding: 1rem !important;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .charts-status-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .metric-card__content {
                padding: 1.25rem;
            }
            
            .metric-card__number {
                font-size: 1.75rem;
            }
            
            .metric-card__icon {
                width: 56px;
                height: 56px;
                font-size: 1.25rem;
            }
            
            .title-1 {
                font-size: 1.75rem;
            }

            .status-circle {
                width: 100px;
                height: 100px;
            }

            .status-percentage {
                font-size: 1.5rem;
            }
        }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 400px;
        }

        @media (max-width: 767.98px) {
            .chart-container {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="page-container">
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <!-- Header -->
                        <div class="overview-wrap">
                            <h2 class="title-1">Welcome Admin!</h2>
                        </div>



                        <!-- Modern Dashboard Metrics -->
                        <?php generateOverviewBoxes(
                            $totalProducts, $lowStock,
                            $reservationCount, $returnedCount, $borrowedCount, $pendingCount, $cartCount,
                            $totalDamageReports
                        ); ?>

                        <!-- Charts and Status Grid -->
                        <div class="charts-status-grid">
                            <!-- Modern Chart Card -->
                            <div class="modern-card">
                                <div class="modern-card__header">
                                    <h3 class="modern-card__title">Inventory Overview</h3>
                                </div>
                                <div class="modern-card__body">
                                    <div class="chart-container">
                                        <canvas id="inventoryOverviewChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <!-- System Status Card -->
                            <div class="status-card">
                                <div class="status-card__header">
                                    <h3 class="status-card__title">System Status</h3>
                                    <div class="status-badge" id="systemStatusBadge">
                                        <?php
                                            // You can set status text based on performance or other logic
                                            echo ($systemPerformance >= 80) ? "OPTIMAL" : (($systemPerformance >= 50) ? "WARNING" : "CRITICAL");
                                        ?>
                                    </div>
                                </div>
                                <div class="status-card__body">
                                    <!-- System Performance Metric -->
                                    <div class="status-metric">
                                        <div class="status-circle">
                                            <svg viewBox="0 0 120 120">
                                                <circle class="status-circle__bg" cx="60" cy="60" r="52"></circle>
                                                <circle class="status-circle__progress" id="statusCircleProgress" cx="60" cy="60" r="52"
                                                    style="stroke-dashoffset: <?php echo 377 - (377 * $systemPerformance / 100); ?>;">
                                                </circle>
                                            </svg>
                                            <div class="status-percentage" id="statusPercentage">
                                                <?php echo number_format($systemPerformance, 2); ?>%
                                            </div>
                                        </div>
                                        <div class="status-label" id="statusLabel">
                                            <?php
                                                if ($systemPerformance >= 80) {
                                                    echo "System is running optimally.";
                                                } elseif ($systemPerformance >= 50) {
                                                    echo "System is experiencing some issues.";
                                                } else {
                                                    echo "System requires immediate attention!";
                                                }
                                            ?>
                                        </div>
                                    </div>

                                    <!-- Status Items -->
                                    <ul class="status-items">
                                        <li class="status-item">
                                            <div class="status-item__info">
                                                <h4>Items Returned</h4>
                                                <p>Equipment returned successfully</p>
                                            </div>
                                            <div class="status-item__time">
                                                <?php
                                                // Fetch count from returns table instead of reservation
                                                $returnsResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM returns");
                                                $returnsRow = mysqli_fetch_assoc($returnsResult);
                                                echo intval($returnsRow['total']); ?> total
                                            </div>
                                        </li>
                                        <li class="status-item">
                                            <div class="status-item__info">
                                                <h4>Low Stock Alert</h4>
                                                <p>Items below reorder level</p>
                                            </div>
                                            <div class="status-item__time"><?php echo $lowStock; ?> items</div>
                                        </li>
                                        <li class="status-item">
                                            <div class="status-item__info">
                                                <h4>New Reservations</h4>
                                                <p>Equipment bookings processed</p>
                                            </div>
                                            <div class="status-item__time"><?php echo $reservationCount; ?> total</div>
                                        </li>
                                        <li class="status-item">
                                            <div class="status-item__info">
                                                <h4>Damage Report</h4>
                                                <p>Equipment damage reported</p>
                                            </div>
                                            <div class="status-item__time"><?php echo $totalDamageReports; ?> total</div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php if ($lowStock > 0): ?>
                        <div class="alert alert-danger" style="font-size:1.1em; font-weight:600; margin-bottom:1.5rem;">
                            <i class="fa fa-exclamation-triangle"></i> <b>Low Stock Alert:</b> There are <?php echo $lowStock; ?> product(s) at or below their reorder level. Please restock soon!
                        </div>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-sm" style="background:#fff;">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Product ID</th>
                                        <th>Product Name</th>
                                        <th>Quantity In Stock</th>
                                        <th>Reorder Level</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $sqlLow = "SELECT product_id, product_name, quantity_in_stock, reorder_level, status FROM inventory_products WHERE quantity_in_stock <= reorder_level ORDER BY quantity_in_stock ASC";
                                $resultLow = mysqli_query($conn, $sqlLow);
                                while ($rowLow = mysqli_fetch_assoc($resultLow)) {
                                    echo "<tr>
                                        <td>{$rowLow['product_id']}</td>
                                        <td>{$rowLow['product_name']}</td>
                                        <td>{$rowLow['quantity_in_stock']}</td>
                                        <td>{$rowLow['reorder_level']}</td>
                                        <td>{$rowLow['status']}</td>
                                    </tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                        <!-- Footer -->
                        <div class="copyright">
                            <p>Copyright © 2025 Biometrix System & Trading Corp. All rights reserved.</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="vendor/jquery-3.2.1.min.js"></script>
    <script src="vendor/bootstrap-4.1/popper.min.js"></script>
    <script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/main.js"></script>
    <script>
        // Modern Chart Configuration with NO animations
        const ctx = document.getElementById('inventoryOverviewChart').getContext('2d');
        const inventoryOverviewChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    'Products',
                    'Low Stock',
                    'Reservations',
                    'Returned',
                    'Borrowed',
                    'Pending',
                    'Cart',
                    'Damage'
                ],
                datasets: [{
                    label: 'Inventory Metrics',
                    data: [
                        <?php echo $totalProducts; ?>,
                        <?php echo $lowStock; ?>,
                        <?php echo $reservationCount; ?>,
                        <?php echo $returnedCount; ?>,
                        <?php echo $borrowedCount; ?>,
                        <?php echo $pendingCount; ?>,
                        <?php echo $cartCount; ?>,
                        <?php echo $totalDamageReports; ?>
                    ],
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(249, 0, 0, 0.8)',
                        'rgba(118, 75, 162, 0.8)',
                        'rgb(41, 193, 220)',
                        'rgba(255, 99, 132, 0.8)', 
                        'rgb(92, 194, 3)',
                         'rgb(215, 199, 22)',
                        'rgba(255, 0, 0, 0.8)' 
                    ],
                    borderColor: [
                        'rgba(102, 126, 234, 1)',
                        'rgba(249, 0, 0, 0.8)',
                        'rgba(118, 75, 162, 1)',
                        'rgb(41, 193, 220)',
                        'rgba(255, 99, 132, 1)', 
                        'rgb(92, 194, 3)',
                        'rgb(215, 199, 22)',
                        'rgba(255, 0, 0, 1)' 
                    ],
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 60
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                // DISABLE ALL ANIMATIONS
                animation: false,
                animations: {
                    colors: false,
                    y: false,
                    x: false,
                    borderColor: false,
                    borderWidth: false
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(45, 55, 72, 0.95)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(102, 126, 234, 0.5)',
                        borderWidth: 1,
                        padding: 12,
                        caretSize: 8,
                        cornerRadius: 8
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#2d3748',
                            font: {
                                weight: 'bold'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(226, 232, 240, 0.7)'
                        },
                        ticks: {
                            color: '#718096',
                            font: {
                                weight: 'bold'
                            },
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>

    <!-- Responsive System Performance JS (optional for dynamic resizing) -->
    <script>
        function updateStatusCircle(percent) {
            const circle = document.getElementById('statusCircleProgress');
            const offset = 377 - (377 * percent / 100);
            circle.style.strokeDashoffset = offset;
            document.getElementById('statusPercentage').textContent = percent + '%';

            // Optionally update badge color/text based on performance
            const badge = document.getElementById('systemStatusBadge');
            if (percent >= 80) {
                badge.textContent = 'OPTIMAL';
                badge.style.background = 'var(--success-gradient)';
            } else if (percent >= 50) {
                badge.textContent = 'WARNING';
                badge.style.background = 'var(--warning-gradient)';
            } else {
                badge.textContent = 'CRITICAL';
                badge.style.background = 'var(--danger-gradient)';
            }
        }

        // Responsive: update on resize (if you want to recalculate or redraw)
        window.addEventListener('resize', function() {
            updateStatusCircle(<?php echo $systemPerformance; ?>);
        });

        // Initial update
        document.addEventListener('DOMContentLoaded', function() {
            updateStatusCircle(<?php echo $systemPerformance; ?>);
        });
    </script>
    <script>
    // Responsive system status update
    function updateStatusCircle(percent) {
        const circle = document.getElementById('statusCircleProgress');
        const offset = 377 - (377 * percent / 100);
        circle.style.strokeDashoffset = offset;
        document.getElementById('statusPercentage').textContent = percent.toFixed(2) + '%';

        // Update badge and label
        const badge = document.getElementById('systemStatusBadge');
        const label = document.getElementById('statusLabel');
        if (percent >= 80) {
            badge.textContent = 'OPTIMAL';
            badge.style.background = 'var(--success-gradient)';
            label.textContent = 'System is running optimally.';
        } else if (percent >= 50) {
            badge.textContent = 'WARNING';
            badge.style.background = 'var(--warning-gradient)';
            label.textContent = 'System is experiencing some issues.';
        } else {
            badge.textContent = 'CRITICAL';
            badge.style.background = 'var(--danger-gradient)';
            label.textContent = 'System requires immediate attention!';
        }
    }

    // Poll for live status (AJAX) every 30 seconds
    function pollSystemStatus() {
        fetch('dashboard.php?ajax=system_status')
            .then(res => res.json())
            .then(data => {
                if (typeof data.systemPerformance !== 'undefined') {
                    updateStatusCircle(data.systemPerformance);
                }
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateStatusCircle(<?php echo $systemPerformance; ?>);
        setInterval(pollSystemStatus, 30000); // Poll every 30 seconds
    });
</script>
</body>
</html>