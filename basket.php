
<?php
session_start();
require_once('connection.php'); // Include your database connection

$pdo = getConnection();
// Calculate totals
$subtotal = 0;
$deliveryFee = 1000; // Fixed delivery fee
$itemCount = 0;

if (!empty($_SESSION['basket'])) {
    foreach ($_SESSION['basket'] as $productId => $item) {
        $stmt = $pdo->prepare("SELECT price FROM products WHERE product_id = ?");
        $stmt->execute([$productId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $price = (float)$result['price'];
        $subtotal += $price * $item['quantity'];
        $itemCount += $item['quantity'];
    }
}
$total = $subtotal + $deliveryFee;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Basket - Meuble Confort</title>
    <link rel="stylesheet" href="basket.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Italianno&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="index.php" class="logo-link">
                <img src="images/MeubleConfort.png" alt="Meuble Confort logo" class="logo">
            </a>
            
            <nav class="main-nav">
                <ul class="nav-list">
                    <li class="nav-item"><a href="landingPage.php#home" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="landingPage.php#catalog" class="nav-link">Catalog</a></li>
                    <li class="nav-item"><a href="landingPage.php#us" class="nav-link">About Us</a></li>
                    <li class="nav-item basket">
                        <a href="basket.php" class="nav-link active">Basket</a>
                        <img src="images/basket.png" alt="Basket" class="basket-icon">
                        <span class="basket-count"><?= $itemCount ?></span>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <section class="basket-section">
            <h1 class="section-title">Your Basket</h1>
            
            <div class="basket-container">
                <?php if (empty($_SESSION['basket'])): ?>
                    <div class="empty-basket">
                        <p>Your basket is empty</p>
                        <a href="landingPage.php#catalog" class="continue-shopping">Continue Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="basket-items">
                        <?php foreach ($_SESSION['basket'] as $productId => $item): 
                            // Fetch product details from database for better info
                            $stmt = $pdo->prepare("SELECT name_prod, description_prod, price ,image_url FROM products WHERE product_id = ?");
                            $stmt->execute([$productId]);
                            $product = $stmt->fetch();
                        ?>
                            <div class="basket-item" data-product-id="<?= $productId ?>">
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name_prod']) ?>" class="item-image">
                                <div class="item-details">
                                    <h3 class="item-name"><?= htmlspecialchars($product['name_prod']) ?></h3>
                                    <p class="item-desc"><?= htmlspecialchars($product['description_prod']) ?></p>
                                    <div class="item-controls">
                                        <button class="quantity-btn minus" onclick="updateQuantity(<?= $productId ?>, -1)">-</button>
                                        <span class="quantity"><?= $item['quantity'] ?></span>
                                        <button class="quantity-btn plus" onclick="updateQuantity(<?= $productId ?>, 1)">+</button>
                                    </div>
                                </div>
                                <div class="item-price">
                                    <span class="price">$<?= number_format($product['price'], 2) ?></span>
                                    <button class="remove-btn" onclick="removeItem(<?= $productId ?>)">Remove</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="basket-summary">
                        <h3 class="summary-title">Order Summary</h3>
                        <div class="summary-details">
                            <div class="summary-row">
                                <span>Subtotal (<?= $itemCount ?> items)</span>
                                <span>$<?= number_format($subtotal, 2) ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Delivery</span>
                                <span>$<?= number_format($deliveryFee, 2) ?></span>
                            </div>
                            <div class="summary-row total">
                                <span>Total</span>
                                <span>$<?= number_format($total, 2) ?></span>
                            </div>
                        </div>
                        <button class="checkout-btn">Validate order</button>
                        <a href="landingPage.php#catalog" class="continue-shopping">Continue Shopping</a>
                        <input type="hidden" name="user_id" value="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>">
<input type="hidden" name="subtotal" value="<?php echo $subtotal; ?>">
<input type="hidden" name="delivery_fee" value="<?php echo $deliveryFee; ?>">
<input type="hidden" name="total_amount" value="<?php echo $total; ?>">
                    </div>
                <?php endif; ?>
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
                </div>
            </div>
            
            
    </footer>

    <script>
    function updateQuantity(productId, change) {
        fetch('update_basket.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                change: change
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            }
        });
    }

    function removeItem(productId) {
        if(confirm('Remove this item from your basket?')) {
            fetch('update_basket.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    action: 'remove'
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                }
            });
        }
    }
    </script>
</body>
</html>