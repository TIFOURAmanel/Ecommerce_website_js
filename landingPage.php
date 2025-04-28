<?php
session_start();
require_once('connection.php'); // Database connection file

// Initialize variables
$selectedCategory = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
$products = [];
$categories = [];

try {
    $pdo = getConnection();
    
    // Get all categories
    $stmt = $pdo->query("SELECT category_id, name_categ FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If a category is selected, get its products
    if ($selectedCategory) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ?");
        $stmt->execute([$selectedCategory]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading data. Please try again.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meuble Confort</title>
    <link rel="stylesheet" href="landingStyle.css">
    <style>
        /* Add these styles to your existing CSS */
        .category-list {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            padding: 0;
            list-style: none;
        }
        
        .category-btn {
            padding: 10px 20px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
        }
        
        .category-btn:hover, .category-btn.active {
            background-color: #333;
            color: white;
        }
        
        .products-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            transition: transform 0.3s ease;
            background: white;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .no-products {
            text-align: center;
            padding: 20px;
            grid-column: 1 / -1;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <img src="images/MeubleConfort.png" alt="Meuble Confort logo" class="logo"> 
            <nav class="main-nav">
                <ul class="nav-list">
                    <li class="nav-item"><a href="#home" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="#catalog" class="nav-link">Catalog</a></li>
                    <li class="nav-item"><a href="#us" class="nav-link">About Us</a></li>
                    <li class="nav-item"><a href="basket.html" class="nav-link">Basket</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <section class="hero">
            <div class="hero-content">
                <h1 class="hero-title">Make Yourself at Home</h1>
                <img src="images/welcome.png" alt="Welcome illustration" class="hero-image">
            </div>
        </section>

        <section class="catalog-section" id="catalog">
            <h2 class="section-title">Our Catalog</h2>
            <ul class="category-list">
                <?php foreach ($categories as $category): ?>
                    <li>
                        <button class="category-btn <?= $selectedCategory == $category['category_id'] ? 'active' : '' ?>" 
                                onclick="window.location.href='?category_id=<?= $category['category_id'] ?>'">
                            <?= htmlspecialchars($category['name_categ']) ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <div class="products-container" id="products-container">
                <?php if ($selectedCategory): ?>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($product['name_prod']) ?>" class="product-image">
                                <h3><?= htmlspecialchars($product['name_prod']) ?></h3>
                                <p><?= htmlspecialchars($product['description_prod']) ?></p>
                                <p>Price: $<?= number_format($product['price'], 2) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-products">No products found in this category.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="no-products">Please select a category to view products.</p>
                <?php endif; ?>
            </div>
        </section>
        
        <section class="about-section" id="us">
            <h2 class="section-title">About Us</h2>
            <div class="about-container">
                <img src="images/logocake.png" alt="Cake logo" class="about-logo">
                
                <div class="about-content">
                    <h3 class="about-subtitle">Our Shop</h3>
                    <p class="about-text">
                        Here you can add a description about the shop, what it can offer, 
                        when it was created, information about the owner, where it's located, 
                        and any information that makes your customers trust you.
                    </p>
                    <button class="cta-button">Contact Us</button>
                </div>
                
                <img src="images/map.png" alt="Location map" class="about-map">
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-container">
            <h2 class="footer-title">Contact Us</h2>
            <div class="contact-info">
                <p class="phone-number">+213 (0) 657987786</p>
                <div class="social-icons">
                    <img src="images/instagram.png" alt="Instagram" class="social-icon">
                    <!-- Add other social icons here -->
                </div>
            </div>
            <div class="divider"></div>
        </div>
    </footer>
</body>
</html>