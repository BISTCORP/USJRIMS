<?php
include 'config.php';

// Set headers to force download of the CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=inventory_report.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Write the column headers
fputcsv($output, ['ID', 'Product Name', 'Description', 'Quantity in Stock', 'Reorder Level', 'Unit Price', 'Supplier', 'Status', 'Date Added', 'Last Updated']);

// Fetch inventory data
$query = "SELECT ip.*, s.supplier_name 
          FROM inventory_products ip 
          LEFT JOIN suppliers s ON ip.supplier_id = s.supplier_id 
          ORDER BY ip.product_id ASC";
$result = mysqli_query($conn, $query);

// Write rows to the CSV file
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['product_id'],
        $row['product_name'],
        $row['product_description'],
        $row['quantity_in_stock'],
        $row['reorder_level'],
        number_format($row['unit_price'], 2),
        $row['supplier_name'],
        $row['status'],
        $row['date_added'],
        $row['last_updated']
    ]);
}

// Close output stream
fclose($output);
exit;