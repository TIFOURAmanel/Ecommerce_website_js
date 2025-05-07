<?php
session_start();
require_once 'connection.php';

try {
    $pdo = getConnection(); // This function should be defined in connection.php
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
// Handle order confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_yes'])) {
    header('Location: finalize_order.php');
    exit;
}

// Redirect if no address in session
if (!isset($_SESSION['order_address'])) {
    header('Location: basket.php');
    exit;
}



// Get cart items from session
$cartItems = isset($_SESSION['basket']) ? $_SESSION['basket'] : [];
$totalAmount = 0;

// Calculate total amount

$itemCount = 0;

if (!empty($_SESSION['basket'])) {
    foreach ($_SESSION['basket'] as $productId => $item) {
        // Fetch product price from database
        $stmt = $pdo->prepare("SELECT price FROM products WHERE product_id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            $totalAmount += $product['price'] * $item['quantity'];
            $itemCount += $item['quantity'];
        }
    }
}

$deliveryFee = 1000; // Your fixed delivery fee
$total = $totalAmount + $deliveryFee;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        :root {
            --primary-color:#461c03;
            --accent-color: #ff7e5f;
            --text-dark: #333;
            --text-light: #fff;
            --gray-light: #f5f5f5;
            --border-radius: 4px;
            --shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        
       
        main {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .confirmation-container {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        
        .order-summary {
            margin: 1.5rem 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
            padding: 1.5rem 0;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .total-amount {
            font-weight: bold;
            font-size: 1.2rem;
            text-align: right;
            margin-top: 1rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        
        .btn-secondary {
            background-color: var(--gray-light);
            color: var(--text-dark);
            border: 1px solid #ddd;
        }
        
    </style>
</head>
<body>
<?php include('header.php'); ?>

    <main>
        <div class="confirmation-container">
            <h2>Confirm Your Order</h2>
            <p><strong>Delivery Address:</strong> <?= htmlspecialchars($_SESSION['order_address']) ?></p>
            
            <div class="order-summary">
    <h3>Order Summary</h3>
    <?php foreach ($_SESSION['basket'] as $productId => $item): ?>
        <?php
        $stmt = $pdo->prepare("SELECT name_prod, price FROM products WHERE product_id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        ?>
        <?php if ($product): ?>
            <div class="order-item">
                <span><?= htmlspecialchars($product['name_prod']) ?> (x<?= $item['quantity'] ?>)</span>
                <span>$<?= number_format($product['price'] * $item['quantity'], 2) ?></span>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
    
    <div class="summary-row">
        <span>Subtotal (<?= $itemCount ?> items)</span>
        <span>$<?= number_format($totalAmount, 2) ?></span>
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
            
            <form method="post">
                <div class="action-buttons">
                    <button type="submit" name="confirm_yes" class="btn btn-primary">
                        Confirm Order
                    </button>
                    <a href="basket.php" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>

    <?php include('footer.php'); ?>
</body>
</html>