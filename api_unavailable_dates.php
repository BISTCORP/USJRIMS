<?php
include 'config.php';

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
if (!$product_id) {
    echo json_encode([]);
    exit;
}

$sql = "
SELECT d.reservation_date
FROM (
    SELECT CURDATE() + INTERVAL seq DAY AS reservation_date
    FROM (
        SELECT 0 AS seq UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
        UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
        UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14
        UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19
        UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24
        UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29
    ) AS days
) d
LEFT JOIN (
    SELECT reservation_date, SUM(product_qty) AS reserved_qty
    FROM reservation
    WHERE product_id = ? AND status IN ('pending', 'approved', 'borrowed')
    GROUP BY reservation_date
) r ON d.reservation_date = r.reservation_date
JOIN inventory_products p ON p.product_id = ?
WHERE COALESCE(r.reserved_qty, 0) >= p.quantity_in_stock
ORDER BY d.reservation_date ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

$dates = [];
while ($row = $result->fetch_assoc()) {
    $dates[] = $row['reservation_date'];
}
header('Content-Type: application/json');
echo json_encode($dates);
