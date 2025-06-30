<?php
include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Inventory Report</title>
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            @page {
                margin: 0;
                size: portrait;
            }
            
            /* Hide browser generated content */
            body {
                -webkit-print-color-adjust: exact;
                margin: 1cm;
            }
            
            /* Hide page footer */
            #pageFooter {
                display: none !important;
            }
        }

        body {
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th, .table td {
            border: 1px solid black;
            padding: 10px;
            text-align: left;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .header-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-container h2 {
            margin: 0;
            font-size: 24px;
        }

        .logo {
            width: 120px;
            height: auto;
        }

        .company-details {
            text-align: left;
            font-size: 17px;
            line-height: 1.4;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .freight-note {
            border: 1px solid #000;
            padding: 6px 10px;
            margin-bottom: 0;
            font-size: 14px;
            font-weight: 500;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .signature-table th, .signature-table td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 14px;
        }

        .date-container {
            text-align: right;
            margin-top: 10px;
            font-size: 14px;
        }

        h4 {
            margin-top: 20px;
            text-align: center;
        }
        
    </style>
</head>
<body onload="window.print(); setTimeout(() => window.close(), 1000);">
    <div class="header-container">
        <img src="images/USJRlogo.png" alt="University of San Jose - Recoletos Logo" class="logo">
        <div class="company-details">
            University of San Jose - Recoletos<br>
            Cebu City
        </div>
    </div>

    <h4>Products Inventory</h4>

    <div class="date-container">
        <p>Date: <?php echo date('Y-m-d'); ?></p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Description</th>
                <th>Quantity in Stock</th>
                <th>Reorder Level</th>
                <th>Unit Price</th>
                <th>Status</th>
                <th>Date Added</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Add status filter if provided
            $statusFilter = '';
            if (isset($_GET['status']) && in_array($_GET['status'], ['Available', 'Not Available'])) {
                $status = $conn->real_escape_string($_GET['status']);
                $statusFilter = "WHERE status = '$status'";
            }
            $result = $conn->query("SELECT * FROM inventory_products $statusFilter ORDER BY product_id");
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['product_id']}</td>
                        <td>" . htmlspecialchars($row['product_name']) . "</td>
                        <td>" . htmlspecialchars($row['product_description']) . "</td>
                        <td>{$row['quantity_in_stock']}</td>
                        <td>{$row['reorder_level']}</td>
                        <td>₱" . number_format($row['unit_price'], 2) . "</td>
                        <td>" . htmlspecialchars($row['status']) . "</td>
                        <td>{$row['date_added']}</td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>


</body>
</html>