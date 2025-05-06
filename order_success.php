<?php
session_start();
require_once 'connection.php';

$orderId = isset($_GET['order_id']) ? $_GET['order_id'] : null;
$order = null;

try {
    if ($orderId) {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
        $stmt->execute([$orderId, isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0]);
        $order = $stmt->fetch();
        
        if (!$order) $orderId = null;
    }
} catch (PDOException $e) {
    error_log("Order confirmation error: " . $e->getMessage());
    $orderId = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="landingStyle.css">
</head>
<body>
   

    <main style="max-width:800px; margin:20px auto; padding:20px;">
        <?php if ($orderId && $order): ?>
            <div style="background:#dff0d8; color:#3c763d; padding:20px; border-radius:5px;">
                <h2>Order Confirmed!</h2>
                <p>Your order #<?= htmlspecialchars($orderId) ?> has been placed successfully.</p>
                
                <div style="margin-top:20px; text-align:left; background:white; padding:15px; border-radius:4px;">
                    <h3>Order Details</h3>
                    <p><strong>Date:</strong> <?= date('F j, Y', strtotime($order['order_date'])) ?></p>
                    <p><strong>Delivery to:</strong> <?= htmlspecialchars($order['address']) ?></p>
                    <p><strong>Total:</strong> $<?= number_format($order['total_amount'], 2) ?></p>
                </div>
            </div>
        <?php else: ?>
            <div style="background:#f2dede; color:#a94442; padding:20px; border-radius:5px;">
                <h2>Order Not Found</h2>
                <p>We couldn't verify your order details.</p>
            </div>
        <?php endif; ?>
        
        <a href="landing.php" style="display:inline-block; margin-top:20px; padding:10px 20px; background:var(--primary-color); color:white; text-decoration:none; border-radius:4px;">
            Continue Shopping
        </a>
    </main>

    
</body>
</html>