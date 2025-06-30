<?php
session_start();

include 'config.php';

// Check if the user is logging out
if (isset($_GET['logout'])) {
    session_destroy(); // Destroy the session
    header("Location: index.php"); // Redirect to the login page
    exit();
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
  
  <style>
:root {
    --primary-dark: #000000;
    --primary-green: #23c552;
    --accent-gold: #ffb43a;
    --text-white: #ffffff;
    --text-gray: #888888;
    --bg-darker: #1a1a1a;
}

/* Header Styles */
.header-bar {
    background: var(--primary-dark);
    padding: 12px 24px;
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1000;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.header-logo-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-title {
    color: var(--primary-green);
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    font-size: 1.25rem;
}

.header-nav a, 
.header-nav button {
    color: var(--text-white);
    padding: 8px 16px;
    border-radius: 4px;
    transition: all 0.2s;
}

.header-nav a:hover, 
.header-nav button:hover {
    background: rgba(255,255,255,0.1);
    color: var(--primary-green);
}

/* Footer Styles */
footer {
    background: var(--primary-dark);
    color: var(--text-white);
    padding: 60px 0 20px;
}

footer h4 {
    color: var(--primary-green);
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

footer .link-title {
    color: var(--accent-gold);
    font-size: 1.2rem;
    font-weight: 500;
    margin-bottom: 1rem;
}

footer p, footer a {
    color: var(--text-gray);
    transition: color 0.2s;
}

footer a:hover {
    color: var(--primary-green);
    text-decoration: none;
}

footer .social-links a {
    color: var(--text-white);
    font-size: 1.5rem;
    margin-right: 1rem;
}

footer .copyright {
    color: var(--text-gray);
    text-align: center;
    padding-top: 2rem;
    margin-top: 3rem;
    border-top: 1px solid rgba(255,255,255,0.1);
}

/* Main Content Background */
body {
    background: var(--bg-darker);
    color: var(--text-white);
}

/* Card Styles */
.card {
    background: var(--primary-dark);
    border: 1px solid rgba(255,255,255,0.1);
}

.card-body {
    background: var(--primary-dark);
}

.card-title {
    color: var(--text-white);
}

.card-text {
    color: var(--text-gray);
}

.price-tag {
    color: var(--primary-green);
}

/* Button Styles */
.btn-success {
    background: var(--primary-green);
    border: none;
}

.btn-primary {
    background: var(--accent-gold);
    border: none;
    color: var(--primary-dark);
}

/* General Styles */
    body { font-family: 'Arial', sans-serif; }
    .navbar a, .navigation a {
    color: #198754 !important; /* Bootstrap green or use your preferred green */
}
/* Style the search modal for better fit */
#search-bar-overlay .bg-white {
    width: 700px;                /* 3 cards * 260px + 2*16px gap + padding */
    max-width: 80vw;
    min-width: 340px;
    max-height: 80vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
    padding: 24px 18px 18px 18px;
    position: relative;
}
#close-search-btn {
    position: absolute;
    top: 0px;          /* Adjust top position */
    right: 0px;        /* Adjust right position */
    z-index: 10;
    height: 36px;
    width: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    background: rgba(0,0,0,0.05);  /* Slight background */
    border: none;
    border-radius: 50%;            /* Make it round */
    font-size: 20px;
    color: #666;
    cursor: pointer;
    transition: all 0.2s ease;
}
#close-search-btn:hover {
    background: rgba(0,0,0,0.1);
    color: #e3342f;
    transform: rotate(90deg);       /* Rotate effect on hover */
}
#search-results-header {
    flex: 1 1 auto;
    overflow-y: auto;
    max-height: 55vh;
    margin-bottom: 0;
    width: 100%;
}
.search-card-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* Always 3 columns */
    gap: 16px;
    width: 100%;
    margin: 0;
    box-sizing: border-box;
}
@media (max-width: 900px) {
    #search-bar-overlay .bg-white { width: 570px; }
    .search-card-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
    #search-bar-overlay .bg-white { width: 98vw; min-width: unset; }
    .search-card-grid { grid-template-columns: 1fr; }
}
.search-card-grid .search-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-width: 0;
    margin: 0;
}
/* Responsive header adjustments */
.header-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 8px;
  background: #fff;
  position: fixed;
  top: 0; left: 0; width: 100%;
  z-index: 50;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.header-logo-title {
  display: flex;
  align-items: center;
  gap: 8px;
}
.header-logo-title img {
  height: 32px;
  width: 32px;
}
.header-title {
  font-family: Algerian, serif;
  color: #1a5f3f;
  font-weight: bold;
  font-size: 1.05rem;
  white-space: nowrap;
}
.header-nav {
  display: flex;
  align-items: center;
  gap: 4px;
}
.header-nav a, .header-nav button {
  background: none;
  border: none;
  padding: 7px 10px;
  border-radius: 6px;
  font-size: 1.1rem;
  color: #1a5f3f;
  display: flex;
  align-items: center;
  transition: background 0.2s;
}
.header-nav a:hover, .header-nav button:hover {
  background: #e6f4ea;
}
@media (min-width: 480px) {
  .header-logo-title img { height: 40px; width: 40px; }
  .header-title { font-size: 1.25rem; }
  .header-nav a, .header-nav button { font-size: 1.25rem; }
}
@media (min-width: 768px) {
  .header-bar { padding: 14px 32px; }
  .header-title { font-size: 1.5rem; }
  .header-nav { gap: 12px; }
}
@media (max-width: 350px) {
  .header-title { font-size: 0.85rem; }
}

/* Add this after your existing styles */

/* Modern Product Card Styling */
.card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.08);
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
}

.card .position-relative {
    position: relative;
    overflow: hidden;
}

.card .card-img-top {
    transition: transform 0.5s ease;
}

.card:hover .card-img-top {
    transform: scale(1.05);
}

.card-body {
    padding: 1.5rem;
    background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(249,250,251,0.5) 100%);
}

.card-title {
    color: #1a202c;
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    line-height: 1.2;
}

.card-text {
    color: #4a5568;
    font-size: 0.95rem;
    line-height: 1.5;
    margin-bottom: 1rem;
}

.text-success {
    color: #047857 !important;
}

.badge {
    padding: 0.5em 1em;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.85rem;
}

.badge-success {
    background-color: #dcfce7;
    color: #166534;
}

.badge-secondary {
    background-color: #f1f5f9;
    color: #475569;
}

.btn {
    padding: 0.6rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-success {
    background: #16a34a;
    border: none;
}

.btn-success:hover:not(:disabled) {
    background: #15803d;
    transform: translateY(-1px);
}

.btn-primary {
    background: #2563eb;
    border: none;
}

.btn-primary:hover:not(:disabled) {
    background: #1d4ed8;
    transform: translateY(-1px);
}

.btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

/* Stock indicator */
.stock-indicator {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.75rem;
    background: #f8fafc;
    border-radius: 6px;
    font-size: 0.9rem;
}

/* Price tag styling */
.price-tag {
    font-size: 1.5rem;
    font-weight: 700;
    color: #047857;
    display: block;
    margin: 0.5rem 0;
}

/* Button container */
.button-container {
    display: flex;
    gap: 0.75rem;
    margin-top: auto;
}

/* Slick slider customization */
.slick-dots li button:before {
    color: #16a34a;
}

.slick-prev:before, 
.slick-next:before {
    color: #16a34a;
}

/* Add these styles to your existing CSS */
.scroll-smooth {
    scroll-behavior: smooth;
}

.active-category {
    background-color: rgba(0, 128, 0, 0.1);
    border-color: #198754;
}

/* Optional: Add transition effect for smoother highlighting */
.block.group {
    transition: all 0.3s ease;
}

/* Loading animation styles */
.category-loader {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1000;
}

.loader-circle {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #198754;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.loader-backdrop {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    z-index: 999;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
  </style>
</head>
<body class="bg-gray-100 text-gray-800">
    <!-- Add this loader HTML -->
    <div class="loader-backdrop"></div>
    <div class="category-loader">
        <div class="loader-circle"></div>
    </div>

<!-- Top Header -->
<header class="header-bar">
  <div class="header-logo-title">
    <img src="images/USJRlogo.png" alt="USJR Logo">
    <span class="header-title">UNIVERSITY OF SAN JOSE - RECOLETOS</span>
  </div>
 <nav class="header-nav" style="
  display: flex; 
  gap: 20px; 
  font-family: 'Candara Light', Candara, Arial, sans-serif;
">
  <a href="#" title="Home" style="
    text-decoration: none; 
    color: #17432b; 
    font-weight: normal;
  ">HOME</a>

  <a href="cart.php" title="Cart" class="relative" style="
    text-decoration: none; 
    color: #17432b; 
    font-weight: normal;
  ">CART
    <?php
    // Get total items in cart
    if (isset($_SESSION['cart_session_id'])) {
        $session_id = $_SESSION['cart_session_id'];
        $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE session_id = ?");
        $stmt->bind_param("s", $session_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $total_items = $result->fetch_assoc()['total'];
        
        if ($total_items > 0) {
            echo '<span id="cart-badge" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">' . $total_items . '</span>';
        }
    }
    ?>
  </a>

  <button id="show-search-btn" title="Search Products" style="
    background: none; 
    border: none; 
    color: #17432b; 
    font-family: 'Candara Light', Candara, Arial, sans-serif;
    font-weight: normal;
    cursor: pointer;
  ">SEARCH</button>
</nav>

</header>

<!-- Search Bar Overlay (hidden by default) -->
<div id="search-bar-overlay" class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-start justify-center pt-32 hidden">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-xl mx-4 p-6 relative animate__animated animate__fadeInDown">
    <button id="close-search-btn" class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-2xl focus:outline-none" title="Close">
      <i class="fas fa-times"></i>
    </button>
    <form id="product-search-form-header" class="flex gap-2">
      <input 
        type="text" 
        id="product-search-input-header" 
        name="search" 
        class="form-input border border-gray-300 rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-green-500"
        placeholder="Search for products or help topics..."
        autocomplete="off"
      >
      <button 
        type="submit" 
        class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded transition"
      >
        <i class="fas fa-search"></i>
      </button>
    </form>
    <!-- Search Results -->
    <div id="search-results-header" class="mt-6"></div>
    <div class="mt-4 text-sm text-gray-500">
      <strong>Tip:</strong> You can search for product names, descriptions, categories, or type <span class="bg-gray-200 px-1 rounded">help</span> for assistance.
    </div>
  </div>
</div>

<script>
$(document).ready(function(){
  // Show search overlay
  $('#show-search-btn').on('click', function(){
    $('#search-bar-overlay').removeClass('hidden').hide().fadeIn(200);
    $('#product-search-input-header').focus();
  });

  // Hide search overlay and clear search results when clicking the close button
  $('#close-search-btn').on('click', function(e){
    e.preventDefault();
    $('#search-bar-overlay').fadeOut(150, function(){ 
      $(this).addClass('hidden');
      $('#search-results-header').html(''); // Clear search results
      $('#product-search-input-header').val(''); // Clear search input
    });
  });

  // Hide search overlay and clear search results when clicking outside the search box
  $('#search-bar-overlay').on('click', function(e){
    if (e.target === this) {
      $(this).fadeOut(150, function(){ 
        $(this).addClass('hidden');
        $('#search-results-header').html(''); // Clear search results
        $('#product-search-input-header').val(''); // Clear search input
      });
    }
  });

  // Prevent overlay close when clicking inside the search box
  $('#search-bar-overlay .bg-white').on('click', function(e){ e.stopPropagation(); });

  // AJAX search for products/help
  $('#product-search-form-header').on('submit', function(e){
    e.preventDefault();
    let query = $('#product-search-input-header').val().trim();
    if(query.length === 0) {
      $('#search-results-header').html('');
      return;
    }
    $('#search-results-header').html('<div class="text-center text-gray-500 py-8"><i class="fas fa-spinner fa-spin"></i> Searching...</div>');
    // If user types 'help', show help info
    if(query.toLowerCase() === 'help') {
      $('#search-results-header').html(`
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
          <h4 class="font-bold mb-2 text-green-700"><i class="fas fa-question-circle"></i> How to Search</h4>
          <ul class="list-disc pl-5 text-gray-700">
            <li>Type a product name, description, or category to find products.</li>
            <li>Example: <span class="bg-gray-200 px-1 rounded">laptop</span>, <span class="bg-gray-200 px-1 rounded">printer</span>, <span class="bg-gray-200 px-1 rounded">electronics</span></li>
            <li>Type <span class="bg-gray-200 px-1 rounded">help</span> to see this message again.</li>
          </ul>
        </div>
      `);
      return;
    }
    $.ajax({
      url: 'search_products.php',
      method: 'POST',
      data: { search: query },
      success: function(data){
        $('#search-results-header').html(data);
      },
      error: function(){
        $('#search-results-header').html('<div class="text-red-600 text-center py-8">An error occurred while searching.</div>');
      }
    });
  });
});
</script>

<!-- Add padding to top to prevent content being hidden behind fixed header -->
<div class="pt-16"></div>

<!-- Navigation and Hero Section -->
<header class="relative">
  <img src="images/image.png" alt="Warehouse Inventory" class="w-full h-64 sm:h-80 md:h-96 object-cover">
  <div class="absolute inset-0 bg-black bg-opacity-60 flex flex-col justify-center items-center text-center px-4" style="font-family: 'Candara Light', Candara, Arial, sans-serif;">
    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4 animate__animated animate__fadeInDown">
      Your Smart Solution for Ordering & Inventory Management
    </h1>
    <p class="text-xl md:text-2xl text-white mb-8 max-w-3xl animate__animated animate__fadeInUp">
      Streamlining your ordering and inventory processes with smart, reliable, and user-friendly solutions.
    </p>
  <a href="#featured-products" 
   class="inline-block bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 px-8 rounded-full shadow-md hover:shadow-lg transform hover:scale-105 transition duration-300 animate__animated animate__fadeInUp animate__delay-1s"
   onclick="scrollToFeaturedProducts(event)">
  See Our Products
</a>

  </div>
</header>

<!-- Category Showcase -->
<section class="py-16 bg-white">
  <div class="container mx-auto px-4">
    <h2 class="text-3xl font-bold text-center mb-4 text-gray-800">Browse by Category</h2>
    <p class="text-gray-600 text-center mb-12">Explore our wide range of products organized by categories</p>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php
      $cat_sql = "SELECT c.*, COUNT(p.product_id) as product_count,
                   SUM(p.quantity_in_stock) as total_stock
                   FROM category c
                   LEFT JOIN inventory_products p ON c.category_id = p.category_id
                   GROUP BY c.category_id
                   ORDER BY c.category_name";
      $cat_result = $conn->query($cat_sql);
      
      if ($cat_result && $cat_result->num_rows > 0):
        while($cat = $cat_result->fetch_assoc()):
      ?>
      <a href="#category-<?php echo $cat['category_id']; ?>" 
         class="block group scroll-smooth"
         onclick="scrollToCategory(event, <?php echo $cat['category_id']; ?>)">
        <div class="h-full rounded-lg border border-gray-200 bg-white hover:shadow-lg transition-all duration-300 hover:border-gray-300">
          <div class="p-6 h-full">
            <div class="text-gray-800">
              <h3 class="text-2xl font-bold mb-3 text-gray-900">
                <?php echo htmlspecialchars($cat['category_name']); ?>
              </h3>
              <p class="text-gray-600 mb-4 leading-relaxed">
                <?php echo htmlspecialchars($cat['description'] ?? 'Browse our selection of ' . $cat['category_name']); ?>
              </p>
              
              <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-100">
                <div class="flex gap-4 text-sm text-gray-500">
                  <span class="flex items-center">
                    <i class="fas fa-box-open mr-2"></i>
                    <?php echo $cat['product_count']; ?> Products
                  </span>
                  <span class="flex items-center">
                    <i class="fas fa-cubes mr-2"></i>
                    <?php echo number_format($cat['total_stock']); ?> in Stock
                  </span>
                </div>
                <span class="group-hover:translate-x-2 transition-transform duration-300 text-gray-400">
                  <i class="fas fa-arrow-right"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </a>
      <?php 
        endwhile;
      endif;
      ?>
    </div>
  </div>
</section>

<!-- --- FEATURED PRODUCTS SLIDER --- -->
<?php
// --- FEATURED PRODUCTS SLIDER ---
$featured_sql = "SELECT * FROM inventory_products WHERE status='Available' ORDER BY date_added DESC LIMIT 8";
$featured_result = $conn->query($featured_sql);
if ($featured_result && $featured_result->num_rows > 0):
?>
<section class="py-5" id="featured-products">
  <div class="container">
    <h2 class="mb-4 font-weight-bold" style="font-family: 'Candara', Arial, sans-serif;">
      Featured Products
    </h2>
    <div class="featured-product-slider">
      <?php while($row = $featured_result->fetch_assoc()):
        $img = !empty($row['image']) && file_exists($row['image']) ? $row['image'] : "images/no-image.png";
      ?>
        <div>
          <div class="card shadow-sm h-100 border-0 mx-2">
            <div class="position-relative" style="height:200px;overflow:hidden;">
              <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" class="card-img-top h-100 w-100" style="object-fit:cover;">
            </div>
            <div class="card-body d-flex flex-column">
              <h5 class="card-title font-weight-bold mb-2"><?php echo htmlspecialchars($row['product_name']); ?></h5>
              <p class="card-text text-muted mb-2" style="font-size:0.95rem;"><?php echo htmlspecialchars($row['product_description']); ?></p>
              <div class="mb-2">
                <span class="text-success font-weight-bold h5">₱<?php echo number_format($row['unit_price'], 2); ?></span>
              </div>
              <div class="mb-3">
                <span class="badge badge-success">Available</span>
                <span class="ml-2 text-secondary" style="font-size:0.95rem;">
                  <i class="fas fa-box"></i> In Stock: <strong><?php echo (int)$row['quantity_in_stock']; ?></strong>
                </span>
              </div>
              <div class="mt-auto">
                <div class="d-flex justify-content-between gap-2">
                  <button onclick="reserveNow(<?php echo $row['product_id']; ?>)" 
        class="btn btn-success w-50" 
        style="font-size:0.95rem;" 
        <?php echo ($row['quantity_in_stock'] <= 0) ? 'disabled' : ''; ?>>
    <i class="fas fa-bolt"></i> Reserve Now
</button>
                  <button onclick="addToCart(<?php echo $row['product_id']; ?>)" 
        class="btn btn-primary w-50" 
        style="font-size:0.95rem;" 
        <?php echo ($row['status'] != 'Available' || $row['quantity_in_stock'] <= 0) ? 'disabled' : ''; ?>>
    <i class="fas fa-cart-plus"></i> Add to Cart
</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</section>
<script>
$(document).ready(function(){
  $('.featured-product-slider').slick({
    slidesToShow: 4,
    slidesToScroll: 1,
    autoplay: true,
    autoplaySpeed: 2500,
    dots: true,
    arrows: true,
    responsive: [
      { breakpoint: 1200, settings: { slidesToShow: 3 } },
      { breakpoint: 992,  settings: { slidesToShow: 2 } },
      { breakpoint: 768,  settings: { slidesToShow: 1 } }
    ]
  });
});
</script>
<?php
endif;
?>

<!-- --- PRODUCTS BY CATEGORY SLIDER --- -->
<?php
// --- PRODUCTS BY CATEGORY SLIDER ---
$cat_sql = "SELECT category_id, category_name FROM category ORDER BY category_name";
$cat_result = $conn->query($cat_sql);

if ($cat_result && $cat_result->num_rows > 0):
  while($cat = $cat_result->fetch_assoc()):
    $category_id = $cat['category_id'];
    $category_name = $cat['category_name'];
    $prod_sql = "SELECT * FROM inventory_products WHERE category_id = $category_id";
    $prod_result = $conn->query($prod_sql);
    if ($prod_result && $prod_result->num_rows > 0):
?>
  <section class="py-5" id="category-<?php echo $category_id; ?>">
    <div class="container">
      <h2 class="mb-4 font-weight-bold" style="font-family: 'Candara', Arial, sans-serif;">
        <?php echo htmlspecialchars($category_name); ?>
      </h2>
      <div class="product-slider-<?php echo $category_id; ?>">
        <?php while($row = $prod_result->fetch_assoc()): 
          $img = !empty($row['image']) && file_exists($row['image']) ? $row['image'] : "images/no-image.png";
        ?>
          <div>
            <div class="card shadow-sm h-100 border-0 mx-2">
              <div class="position-relative" style="height:200px;overflow:hidden;">
                <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" class="card-img-top h-100 w-100" style="object-fit:cover;">
              </div>
              <div class="card-body d-flex flex-column">
                <h5 class="card-title font-weight-bold mb-2"><?php echo htmlspecialchars($row['product_name']); ?></h5>
                <p class="card-text text-muted mb-2" style="font-size:0.95rem;"><?php echo htmlspecialchars($row['product_description']); ?></p>
                <div class="mb-2">
                  <span class="text-success font-weight-bold h5">₱<?php echo number_format($row['unit_price'], 2); ?></span>
                </div>
                <div class="mb-3">
                  <span class="badge badge-<?php echo $row['status']=='Available'?'success':'secondary'; ?>">
                    <?php echo htmlspecialchars($row['status']); ?>
                  </span>
                  <span class="ml-2 text-secondary" style="font-size:0.95rem;">
                    <i class="fas fa-box"></i> In Stock: <strong><?php echo (int)$row['quantity_in_stock']; ?></strong>
                  </span>
                </div>
                <div class="mt-auto">
                  <div class="d-flex justify-content-between gap-2">
                    <button onclick="reserveNow(<?php echo $row['product_id']; ?>)" 
        class="btn btn-success w-50" 
        style="font-size:0.95rem;" 
        <?php echo ($row['status'] != 'Available' || $row['quantity_in_stock'] <= 0) ? 'disabled' : ''; ?>>
    <i class="fas fa-bolt"></i> Reserve Now
</button>
                    <button onclick="addToCart(<?php echo $row['product_id']; ?>)" 
        class="btn btn-primary w-50" 
        style="font-size:0.95rem;" 
        <?php echo ($row['status'] != 'Available' || $row['quantity_in_stock'] <= 0) ? 'disabled' : ''; ?>>
    <i class="fas fa-cart-plus"></i> Add to Cart
</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </section>
  <script>
    $(document).ready(function(){
      $('.product-slider-<?php echo $category_id; ?>').slick({
        slidesToShow: 4,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 2500,
        dots: true,
        arrows: true,
        responsive: [
          { breakpoint: 1200, settings: { slidesToShow: 3 } },
          { breakpoint: 992,  settings: { slidesToShow: 2 } },
          { breakpoint: 768,  settings: { slidesToShow: 1 } }
        ]
      });
    });
  </script>
<?php
    endif;
  endwhile;
endif;
?>

    <div id="search-results" class="mt-6"></div>
  </div>
</section>


<script>
$(document).ready(function(){
  $('#product-search-form').on('submit', function(e){
    e.preventDefault();
    let query = $('#product-search-input').val().trim();
    if(query.length === 0) {
      $('#search-results').html('');
      return;
    }
    $('#search-results').html('<div class="text-center text-gray-500 py-8"><i class="fas fa-spinner fa-spin"></i> Searching...</div>');
    $.ajax({
      url: 'search_products.php',
      method: 'POST',
      data: { search: query },
      success: function(data){
        $('#search-results').html(data);
        // Optionally, scroll to results
        $('html, body').animate({
          scrollTop: $("#search-results").offset().top - 100
        }, 400);
      },
      error: function(){
        $('#search-results').html('<div class="text-red-600 text-center py-8">An error occurred while searching.</div>');
      }
    });
  });
});
</script>
<!-- Footer -->
<footer style="background: #ffffff; color: #333; padding: 50px 20px; font-family: 'Candara', Arial, sans-serif;">
  <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px;">
    
    <!-- Logo and Short Info -->
    <div style="text-align: center;">
      <img src="images/USJRlogo.png" alt="USJR Logo" style="height: 80px; margin-bottom: 15px;">
      <h3 style="margin: 10px 0 5px; color: #17432b;">University of San Jose - Recoletos</h3>
      <p style="font-size: 0.95rem; color: #555;">Founded in 1947</p>
    </div>

    <!-- Vision Statement -->
    <div>
      <h4 style="color: #17432b; font-size: 1.2rem; margin-bottom: 12px;">Our Vision</h4>
      <p style="font-size: 1rem; line-height: 1.6; color: #555;">
        To be a premier Gospel and Community-oriented institution transforming Joseinians into proactive leaders and dynamic partners of society.
      </p>
    </div>

    <!-- Quick Links -->
    <div>
      <h4 style="color: #17432b; font-size: 1.2rem; margin-bottom: 12px;">Quick Links</h4>
      <ul style="list-style: none; padding: 0; margin: 0; font-size: 1rem;">
        <li style="margin-bottom: 10px;"><a href="#" style="color: #333; text-decoration: none;">About Us</a></li>
        <li style="margin-bottom: 10px;"><a href="#" style="color: #333; text-decoration: none;">Admissions</a></li>
        <li style="margin-bottom: 10px;"><a href="#" style="color: #333; text-decoration: none;">Academics</a></li>
        <li><a href="#" style="color: #333; text-decoration: none;">Contact</a></li>
      </ul>
    </div>

    <!-- Contact Information -->
    <div>
      <h4 style="color: #17432b; font-size: 1.2rem; margin-bottom: 12px;">Contact Us</h4>
      <p style="margin: 0 0 12px; font-size: 1rem;">
        <i class="fas fa-map-marker-alt" style="margin-right: 8px; color: #17432b;"></i>
        Magallanes Street, Cebu City, Philippines
      </p>
      <p style="margin: 0 0 12px; font-size: 1rem;">
        <i class="fas fa-envelope" style="margin-right: 8px; color: #17432b;"></i>
        external@usjr.edu.ph
      </p>
      <p style="margin: 0; font-size: 1rem;">
        <i class="fas fa-phone" style="margin-right: 8px; color: #17432b;"></i>
        (63-32) 253-7900
      </p>
    </div>
  </div>

  <!-- Footer Bottom -->
  <div style="text-align: center; margin-top: 40px; color: #555; font-size: 0.9rem;">
    &copy; <?php echo date('Y'); ?> University of San Jose - Recoletos. All rights reserved.
  </div>
</footer>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="vendor/jquery-3.2.1.min.js"></script>
<script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
<script src="vendor/jquery-3.2.1.min.js"></script>
<script src="vendor/slick/slick.js"></script>
<script>
$(document).ready(function(){
  $('.product-slider').slick({
    slidesToShow: 4,
    slidesToScroll: 1,
    autoplay: true,
    autoplaySpeed: 2500,
    dots: true,
    arrows: true,
        responsive: [
          { breakpoint: 1200, settings: { slidesToShow: 3 } },
          { breakpoint: 992,  settings: { slidesToShow: 2 } },
          { breakpoint: 768,  settings: { slidesToShow: 1 } }
        ]
      });
    });
    function smoothReload(selector = 'body', callback = null) {
    const $el = $(selector);
    $el.fadeOut(150, function() {
        location.reload();
        if (typeof callback === 'function') callback();
    });
}

    </script>
   </body>
</html>

<script>
function scrollToCategory(event, categoryId) {
    event.preventDefault();
    const element = document.getElementById(`category-${categoryId}`);
    if (element) {
        // Show loading animation
        document.querySelector('.loader-backdrop').style.display = 'block';
        document.querySelector('.category-loader').style.display = 'block';

        // Add active class to clicked category
        const links = document.querySelectorAll('.category-link');
        links.forEach(link => link.classList.remove('active-category'));
        event.currentTarget.classList.add('active-category');

        const headerOffset = 80;
        const elementPosition = element.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

        // Simulate loading time (remove this in production)
        setTimeout(() => {
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });

            // Hide loading animation after scroll
            setTimeout(() => {
                document.querySelector('.loader-backdrop').style.display = 'none';
                document.querySelector('.category-loader').style.display = 'none';
            }, 500); // Half second after scroll starts
        }, 300); // Adjust this value to control loading duration
    }
}

function scrollToFeaturedProducts(event) {
    event.preventDefault();
    const element = document.getElementById('featured-products');
    if (element) {
        // Show loading animation
        document.querySelector('.loader-backdrop').style.display = 'block';
        document.querySelector('.category-loader').style.display = 'block';

        const headerOffset = 80;
        const elementPosition = element.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

        // Simulate loading time
        setTimeout(() => {
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });

            // Hide loading animation after scroll
            setTimeout(() => {
                document.querySelector('.loader-backdrop').style.display = 'none';
                document.querySelector('.category-loader').style.display = 'none';
            }, 500);
        }, 300);
    }
}
</script>

<script>
function addToCart(productId) {
    // Show loading state
    Swal.fire({
        title: 'Adding to cart...',
        didOpen: () => {
            Swal.showLoading();
        },
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showConfirmButton: false
    });

    // Send AJAX request
    $.ajax({
        url: 'add_to_cart.php',
        method: 'POST',
        data: { product_id: productId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                // Update cart badge if it exists
                if (response.total_items) {
                    updateCartBadge(response.total_items);
                }
                
                // Show success message and refresh page after
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    // Fade out the body, then reload
                    $('body').fadeOut(300, function() {
                        window.location.reload();
                    });
                });
            } else {
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: response.message
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something went wrong! Please try again.'
            });
        }
    });
}

function updateCartBadge(total) {
    // If you have a cart badge/icon, update it here
    // For example:
    const cartBadge = document.getElementById('cart-badge');
    if (cartBadge) {
        cartBadge.textContent = total;
        cartBadge.style.display = total > 0 ? 'block' : 'none';
    }
}

function reserveNow(productId) {
    // First show confirmation dialog
    Swal.fire({
        title: 'Direct Checkout',
        text: 'Do you want to proceed with direct checkout?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#dc3545',
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Processing...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false
            });

            // Add a temporary flag in session storage
            sessionStorage.setItem('direct_checkout_pending', 'true');

            $.ajax({
                url: 'add_to_cart.php',
                method: 'POST',
                data: { 
                    product_id: productId,
                    direct_checkout: true,
                    confirmed: true // Add this flag
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        window.location.href = 'reservation_checkout.php?direct_checkout=true';
                    } else {
                        sessionStorage.removeItem('direct_checkout_pending');
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    sessionStorage.removeItem('direct_checkout_pending');
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong! Please try again.'
                    });
                }
            });
        }
        // If cancelled, do nothing - this prevents adding to cart
    });
}
</script>

<!-- Reserve Now Modal -->
<div id="reserveNowModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center hidden">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 p-6 relative animate__animated animate__fadeInDown">
    <button id="closeReserveModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-2xl focus:outline-none" title="Close">
      <i class="fas fa-times"></i>
    </button>
    <h3 class="text-xl font-semibold mb-4 text-green-700">Reserve Product</h3>
    <form id="reserveNowForm">
      <input type="hidden" name="product_id" id="reserveProductId">
      <div class="mb-3 flex items-center gap-4">
        <img id="reserveProductImg" src="" alt="Product Image" class="w-20 h-20 object-cover rounded border">
        <div>
          <div class="font-bold text-lg" id="reserveProductName"></div>
          <div class="text-gray-600 text-sm" id="reserveProductDesc"></div>
          <div class="text-success font-bold mt-1" id="reserveProductPrice"></div>
          <div class="text-gray-500 text-xs mt-1">In Stock: <span id="reserveProductStock"></span></div>
        </div>
      </div>
      <div class="mb-4">
        <label for="reserveQty" class="block text-gray-700 mb-1">Quantity</label>
        <input type="number" min="1" value="1" name="quantity" id="reserveQty" class="form-input border border-gray-300 rounded px-3 py-2 w-full" required>
      </div>
      <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded w-full transition">
        <i class="fas fa-bolt"></i> Reserve Now
      </button>
    </form>
  </div>
</div>

<script>
$(function() {
  // Open Reserve Modal
  window.reserveNow = function(productId) {
    // Fetch product info via AJAX
    $.ajax({
      url: 'get_product_info.php',
      method: 'POST',
      data: { product_id: productId },
      dataType: 'json',
      success: function(data) {
        if (data && data.status === 'success') {
          $('#reserveProductId').val(data.product.product_id);
          $('#reserveProductImg').attr('src', data.product.image || 'images/no-image.png');
          $('#reserveProductName').text(data.product.product_name);
          $('#reserveProductDesc').text(data.product.product_description);
          $('#reserveProductPrice').text('₱' + parseFloat(data.product.unit_price).toLocaleString(undefined, {minimumFractionDigits:2}));
          $('#reserveProductStock').text(data.product.quantity_in_stock);
          $('#reserveQty').attr('max', data.product.quantity_in_stock).val(1);
          $('#reserveNowModal').removeClass('hidden').hide().fadeIn(150);
        } else {
          Swal.fire('Error', 'Product not found or unavailable.', 'error');
        }
      },
      error: function() {
        Swal.fire('Error', 'Could not fetch product info.', 'error');
      }
    });
  };

  // Close modal
  $('#closeReserveModalBtn').on('click', function() {
    $('#reserveNowModal').fadeOut(150, function(){ $(this).addClass('hidden'); });
  });
  $('#reserveNowModal').on('click', function(e){
    if (e.target === this) {
      $(this).fadeOut(150, function(){ $(this).addClass('hidden'); });
    }
  });
  $('#reserveNowModal .bg-white').on('click', function(e){ e.stopPropagation(); });

  // Handle Reserve Now form submit
  $('#reserveNowForm').on('submit', function(e){
    e.preventDefault();
    var qty = parseInt($('#reserveQty').val(), 10);
    var max = parseInt($('#reserveQty').attr('max'), 10);
    var productId = $('#reserveProductId').val();
    if (qty < 1 || qty > max) {
      Swal.fire('Invalid Quantity', 'Please enter a valid quantity.', 'warning');
      return;
    }
    // Show loading
    Swal.fire({
      title: 'Processing...',
      didOpen: () => { Swal.showLoading(); },
      allowOutsideClick: false,
      showConfirmButton: false
    });
    $.ajax({
      url: 'add_to_cart.php',
      method: 'POST',
      data: { 
        product_id: productId,
        quantity: qty,
        direct_checkout: true,
        confirmed: true
      },
      dataType: 'json',
      success: function(response) {
        if (response.status === 'success') {
          // Redirect to checkout with product_id and qty as query parameters
          window.location.href = 'reservation_checkout.php?direct_checkout=true&product_id=' + encodeURIComponent(productId) + '&qty=' + encodeURIComponent(qty);
        } else {
          Swal.fire('Error', response.message, 'error');
        }
      },
      error: function() {
        Swal.fire('Error', 'Something went wrong! Please try again.', 'error');
      }
    });
  });
});
</script>