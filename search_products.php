<?php
include 'config.php';

$search = isset($_POST['search']) ? trim($_POST['search']) : '';
if ($search === '') {
    echo '<div class="text-center text-gray-500 py-8">Please enter a search term.</div>';
    exit;
}

$search = mysqli_real_escape_string($conn, $search);

$sql = "
SELECT p.*, c.category_name 
FROM inventory_products p 
LEFT JOIN category c ON p.category_id = c.category_id 
WHERE 
    p.product_name LIKE '%$search%' 
    OR p.product_description LIKE '%$search%' 
    OR c.category_name LIKE '%$search%'
ORDER BY p.date_added DESC
LIMIT 20
";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    // Responsive grid for search results
    echo '<div style="display: flex; flex-wrap: wrap; gap: 16px; justify-content: flex-start; max-height: 400px; overflow-y: auto;">';
    while($row = $result->fetch_assoc()) {
        $img = !empty($row['image']) && file_exists($row['image']) ? $row['image'] : "images/no-image.png";
        ?>
        <div class="search-card flex flex-col" style="flex: 0 0 200px; max-width: 200px; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 8px; overflow: hidden; display: flex; flex-direction: column;">
            <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" class="w-full h-40 object-cover rounded-t" style="height:120px;overflow:hidden;">
            <div class="p-4 flex flex-col flex-1" style="padding: 10px; flex:1 1 auto; display:flex; flex-direction:column;">
                <h5 class="font-bold text-lg mb-1" style="font-size:1rem;font-weight:bold;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($row['product_name']); ?></h5>
                <div class="text-green-700 text-sm mb-1" style="font-size:0.9rem;color:#1a5f3f;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($row['category_name']); ?></div>
                <div class="text-gray-600 text-sm mb-2" style="font-size:0.85rem;color:#666;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars(mb_strimwidth($row['product_description'], 0, 60, '...')); ?></div>
                <div class="font-bold text-green-700 mb-1" style="font-size:1rem;color:#28a745;font-weight:bold;margin-bottom:2px;">₱<?php echo number_format($row['unit_price'], 2); ?></div>
                <div class="flex items-center mb-2" style="font-size:0.85rem;margin-bottom:4px;">
                    <span class="badge badge-success mr-2" style="color:<?php echo $row['status']=='Available'?'#28a745':'#888'; ?>;font-weight:600;"><?php echo htmlspecialchars($row['status']); ?></span>
                    <span class="ml-2 text-secondary" style="font-size:0.95rem;color:#888;">
                        <i class="fas fa-box"></i> <?php echo (int)$row['quantity_in_stock']; ?>
                    </span>
                </div>
                <div class="mt-auto flex gap-2" style="margin-top:auto;display:flex;gap:8px;">
                    <button onclick="reserveNow(<?php echo $row['product_id']; ?>)"
                        class="btn btn-success w-1/2"
                        style="font-size:0.95rem;padding:6px 0;"
                        <?php echo ($row['status'] != 'Available' || $row['quantity_in_stock'] <= 0) ? 'disabled' : ''; ?>>
                        <i class="fas fa-bolt"></i> 
                    </button>
                    <button onclick="addToCart(<?php echo $row['product_id']; ?>)"
                        class="btn btn-primary w-1/2"
                        style="font-size:0.95rem;padding:6px 0;"
                        <?php echo ($row['status'] != 'Available' || $row['quantity_in_stock'] <= 0) ? 'disabled' : ''; ?>>
                        <i class="fas fa-cart-plus"></i> 
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    echo '</div>';
} else {
    echo '<div class="text-center text-gray-500 py-8">No products found matching your search.</div>';
}
?>