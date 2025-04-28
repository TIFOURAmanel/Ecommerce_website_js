<?php
session_start();
require_once('connection.php'); // Database connection file

// Initialize variables
$searchResults = [];
$categoryFilter = '';
$productFilter = '';

// Handle search form submission
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['category_search']) || isset($_GET['product_search']))) {
    $categoryFilter = isset($_GET['category_search']) ? trim($_GET['category_search']) : '';
    $productFilter = isset($_GET['product_search']) ? trim($_GET['product_search']) : '';
    
    try {
        $pdo = getConnection();
        
        // Build search query
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($categoryFilter)) {
            $sql .= " AND c.name LIKE ?";
            $params[] = "%$categoryFilter%";
        }
        
        if (!empty($productFilter)) {
            $sql .= " AND p.name LIKE ?";
            $params[] = "%$productFilter%";
        }
        
        $sql .= " LIMIT 12"; // Limit results
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Search error: " . $e->getMessage());
        $_SESSION['error'] = "Error performing search. Please try again.";
    }
}

// Get featured categories for catalog section
$featuredCategories = [];
try {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT id, name FROM categories WHERE featured = 1 LIMIT 3");
    $featuredCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Category fetch error: " . $e->getMessage());
}

// Get basket item count if user is logged in
$basketCount = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart_items WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $basketCount = $result['count'];
    } catch (PDOException $e) {
        error_log("Basket count error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meuble Confort</title>
    <link rel="stylesheet" href="landingStyle.css">
    
</head>
<body>
    <header class="header">
        <div class="header-container">
            <img src="images/logo.png" alt="Gateaux gourmands logo" class="logo">
            
            <div class="search-container">
                <div class="search-group">
                    <input type="text" placeholder="Search by category..." class="search-input">
                    <button class="search-btn">
                        <img src="images/Search.png" alt="Search" class="search-icon">
                    </button>
                </div>
                <div class="search-group">
                    <input type="text" placeholder="Search by product..." class="search-input">
                    <button class="search-btn">
                        <img src="images/Search.png" alt="Search" class="search-icon">
                    </button>
                </div>
            </div>
            
            <nav class="main-nav">
                <ul class="nav-list">
                    <li class="nav-item"><a href="#home" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="#catalog" class="nav-link">Catalog</a></li>
                    <li class="nav-item"><a href="#us" class="nav-link">About Us</a></li>
                    <li class="nav-item basket">
                        <a href="page2.html" class="nav-link">Basket</a>
                        <img src="images/basket.png" alt="Basket" class="basket-icon">
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <section class="hero">
            <div class="hero-content">
                <h1 class="hero-title">Welcome to our world</h1>
                <img src="images/welcom.png" alt="Welcome illustration" class="hero-image">
            </div>
        </section>

        <section class="catalog-section" id="catalog">
            <h2 class="section-title">Our Catalog</h2>
            <ul class="category-list">
                <li class="category-item"><a href="#" class="category-link">Traditionals</a></li>
                <li class="category-item"><a href="#" class="category-link">Moderns</a></li>
                <li class="category-item"><a href="#" class="category-link">Cakes</a></li>
            </ul>
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