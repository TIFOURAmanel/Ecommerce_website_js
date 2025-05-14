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
        $price = (float) $result['price'];
        $subtotal += $price * $item['quantity'];
        $itemCount += $item['quantity'];
    }
}
if (isset($_SESSION['error'])) {
    echo "<script>alert('".addslashes($_SESSION['error'])."');</script>";
    unset($_SESSION['error']);
}
$total = $subtotal + $deliveryFee;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Basket - Meuble Confort</title>
    <link rel="stylesheet" href="basketStyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Italianno&display=swap" rel="stylesheet">
</head>

<body>
    <?php include('header.php'); ?>

    <main class="main-content">
        <section class="basket-section">
            <h1 class="section-title">Your Basket</h1>

            <div class="basket-container">
                <?php
                // Call the stored procedure to get cart items
                $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

                if ($userId) {
                    $stmt = $pdo->prepare("CALL GetCustomerCart(?)");
                    $stmt->execute([$userId]);

                    // First result set contains cart summary (we skip it)
                    $stmt->nextRowset();

                    // Second result set contains cart items
                    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Calculate totals
                    $subtotal = 0;
                    $itemCount = 0;

                    foreach ($cartItems as $item) {
                        $subtotal += $item['prix_total'];
                        $itemCount += $item['quantity'];
                    }

                    $deliveryFee = 5.00; // Example delivery fee
                    $total = $subtotal + $deliveryFee;
                }

                if (empty($cartItems)): ?>
                    <div class="empty-basket">
                        <p>Your basket is empty</p>
                        <a href="landingPage.php#catalog" class="continue-shopping">Continue Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="basket-items">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="basket-item" data-product-id="<?= $item['product_id'] ?>">
                                <img src="<?= htmlspecialchars($item['image_url']) ?>"
                                    alt="<?= htmlspecialchars($item['nom_produit']) ?>" class="item-image">
                                <div class="item-details">
                                    <h3 class="item-name"><?= htmlspecialchars($item['nom_produit']) ?></h3>
                                    <p class="item-desc"><?= htmlspecialchars($item['description']) ?></p>
                                    <div class="item-controls">
                                        <button class="quantity-btn minus"
                                            onclick="updateQuantity(<?= $item['product_id'] ?>, -1)">-</button>
                                        <span class="quantity"><?= $item['quantity'] ?></span>
                                        <button class="quantity-btn plus"
                                            onclick="updateQuantity(<?= $item['product_id'] ?>, 1)">+</button>
                                    </div>
                                </div>
                                <div class="item-price">
                                    <span class="price"><?= number_format($item['prix_unitaire'], 2) ?>da</span>
                                    <button class="remove-btn" onclick="removeItem(<?= $item['product_id'] ?>)">Remove</button>
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
                        <div class="checkout-section">
                            <button type="button" class="checkout-btn" onclick="showAddressForm()">Validate order</button>
                            <a href="landingPage.php#catalog" class="continue-shopping">Continue Shopping</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <div id="address-form" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%);">
        <form method="post" action="process_order.php">
            <h3>Enter Your Delivery Address</h3>
            <div style="margin-bottom:15px;">
                <input type="text" name="address" placeholder="Your full address" required
                    style="width:100%; padding:8px; box-sizing:border-box;">
            </div>
            <div style="display:flex; gap:10px;">
                <button type="button" onclick="hideAddressForm()"
                    style="padding:8px 15px; background:#f0f0f0; border:1px solid #ccc;">Cancel</button>
                <button type="submit" name="submit_address"
                    style="padding:8px 15px; background:var(--primary-color); color:white; border:none;">Continue</button>
            </div>
        </form>
    </div>
    <?php include('footer.php'); ?>


    <script>
        function showAddressForm() {
            document.getElementById('address-form').style.display = 'block';
        }
        function hideAddressForm() {
            document.getElementById('address-form').style.display = 'none';
        }
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
                    if (data.success) {
                        location.reload();
                    }
                });
        }

        function removeItem(productId) {
            if (confirm('Remove this item from your basket?')) {
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
                        if (data.success) {
                            location.reload();
                        }
                    });
            }
        }
    </script>
</body>

</html>