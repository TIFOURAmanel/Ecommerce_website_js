<?php
session_start();
require_once('connection.php');

// Initialize variables and fetch data
$selectedCategory = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
$selectedProduct = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
$products = [];
$categories = [];
$productDetails = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['logout'])) {
        $_SESSION = array();      // Clears all session data
        session_destroy();        // Destroys the session
        
        header("Location: sign.php"); // Redirects                  
    }
}

try {
    $pdo = getConnection();
    
    // Fetch categories
    $stmt = $pdo->query("SELECT category_id, name_categ FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch products if category selected
    if ($selectedCategory) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ?");
        $stmt->execute([$selectedCategory]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Fetch product details if product selected
    if ($selectedProduct) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->execute([$selectedProduct]);
        $productDetails = $stmt->fetch(PDO::FETCH_ASSOC);
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
        background-color: var(--gray-light);
        border: 1px solid var(--primary-light);
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition);
        font-size: 16px;
        color: var(--primary-color);
        font-weight: 500;
    }
    .category-btn:hover, .category-btn.active {
        background-color: var(--primary-color);
        color: var(--text-light);
        border-color: var(--primary-color);
        transform: translateY(-2px);
    }
    .products-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        padding: 20px;
        margin-top: 20px;
    }
    .product-card {
        border: 1px solid var(--primary-light);
        border-radius: var(--border-radius);
        padding: 15px;
        transition: var(--transition);
        background: white;
        box-shadow: var(--shadow);
        cursor: pointer;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        border-color: var(--accent-color);
    }
    .product-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid var(--primary-light);
    }
    .no-products {
        text-align: center;
        padding: 20px;
        grid-column: 1 / -1;
        color: var(--primary-color);
    }
    
    /* Product Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0,0,0,0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    .product-modal {
        background: white;
        border-radius: var(--border-radius);
        width: 90%;
        max-width: 800px;
        padding: 2rem;
        position: relative;
        box-shadow: var(--shadow);
    }
    .close-modal {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 1.5rem;
        background: none;
        border: none;
        cursor: pointer;
        color: var(--primary-color);
    }
    .modal-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
    .modal-image {
        width: 100%;
        max-height: 400px;
        object-fit: contain;
        border-radius: var(--border-radius);
    }
    .modal-details h2 {
        color: var(--primary-color);
        margin-bottom: 1rem;
    }
    .modal-price {
        font-size: 1.5rem;
        color: var(--accent-color);
        font-weight: bold;
        margin: 1rem 0;
    }
    .modal-description {
        color: var(--text-dark);
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }
    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin: 1.5rem 0;
    }
    .quantity-input {
        width: 50px;
        text-align: center;
        border: 1px solid var(--primary-light);
        border-radius: var(--border-radius);
        padding: 0.5rem;
    }
    .cart-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
    }
    .add-to-cart, .remove-from-cart {
        flex: 1;
        padding: 0.8rem;
        border-radius: var(--border-radius);
        font-weight: 500;
        cursor: pointer;
        border: none;
    }
    .add-to-cart {
        background-color: var(--primary-color);
        color: white;
    }
    .add-to-cart:hover {
        background-color: var(--accent-color);
    }
    .remove-from-cart {
        background-color: var(--gray-light);
        color: var(--text-dark);
        border: 1px solid var(--primary-light);
    }
    .remove-from-cart:hover {
        background-color: #e0e0e0;
    }
    

    </style>
</head>
<body>
<?php include('header.php'); ?>
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
                <a href="#catalog?category_id=<?= $category['category_id'] ?>" 
                   class="category-btn <?= $selectedCategory == $category['category_id'] ? 'active' : '' ?>"
                   onclick="event.preventDefault(); window.location.href='?category_id=<?= $category['category_id'] ?>#catalog'">
                    <?= htmlspecialchars($category['name_categ']) ?>
                </a>
            </li>
                <?php endforeach; ?>
            </ul>
            
            <div class="products-container">
                <?php if ($selectedCategory): ?>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <a href="?category_id=<?= $selectedCategory ?>&product_id=<?= $product['product_id'] ?>" class="product-card">
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($product['name_prod']) ?>" class="product-image">
                                <h3><?= htmlspecialchars($product['name_prod']) ?></h3>
                                <p class="price">$<?= number_format($product['price'], 2) ?></p>
                            </a>
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
                    Meuble Confort offers exquisite, handcrafted furniture designed to bring comfort and elegance to your home. Specializing in premium sofas, chairs, tables, and bedroom sets, we combine quality craftsmanship with stylish designs to transform your living spaces. Conveniently located in Draria (Algiers), our showroom welcomes you to experience the perfect blend of functionality and aesthetic appeal. Whether you're furnishing a modern apartment or a classic home, Meuble Confort provides durable, beautiful pieces that enhance your everyday living. Visit us or explore our online catalog to discover furniture that reflects your unique taste and lifestyle.
                    </p>
                    <button class="cta-button">Contact Us</button>
                </div>
                <img src="images/map.png" alt="Location map" class="about-map">
            </div>
        </section>
    </main>

    <!-- Product Modal (shown when product_id is in URL) -->
    <?php if ($productDetails): ?>
    <div class="modal-overlay">
        <div class="product-modal">
            <a href="?category_id=<?= $selectedCategory ?>" class="close-modal">&times;</a>
            <div class="modal-content">
                <div>
                    <img src="<?= htmlspecialchars($productDetails['image_url']) ?>" 
                         alt="<?= htmlspecialchars($productDetails['name_prod']) ?>" class="modal-image">
                </div>
                <div class="modal-details">
                    <h2><?= htmlspecialchars($productDetails['name_prod']) ?></h2>
                    <div class="modal-price">$<?= number_format($productDetails['price'], 2) ?></div>
                    <p class="modal-description"><?= htmlspecialchars($productDetails['description_prod'] ) ?></p>
                    
                    <form method="post" action="cart.php">
                        <input type="hidden" name="product_id" value="<?= $productDetails['product_id'] ?>">
                        <input type="hidden" name="price" value="<?= $productDetails['price'] ?>">
                        <div class="quantity-controls">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" class="quantity-input">
                        </div>
                        
                        <div class="cart-actions">
                            <button type="submit" name="action" value="add" class="add-to-cart">Add to Basket</button>
                            <button type="submit" name="action" value="remove" class="remove-from-cart">Remove</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
<?php include('footer.php'); ?>

    <script>

</script>
</body>
</html>