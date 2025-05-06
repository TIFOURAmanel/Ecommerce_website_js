<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Adjust as needed
    exit;
}

$pdo = getConnection();
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <style>
        body { font-family: Arial; padding: 2rem; background: #f7f7f7; }
        .order { background: white; padding: 1rem; margin-bottom: 1rem; border: 1px solid #ddd; border-radius: 6px; }
        .order h3 { margin: 0 0 1rem 0; }
        .order-actions { margin-top: 1rem; }
        .btn-cancel { background: #e74c3c; color: white; padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; }
        .btn-disabled { background: #ccc; cursor: not-allowed; }
    </style>
</head>
<body>
    <h2>My Orders</h2>

    <?php if (empty($orders)): ?>
        <p>You have no orders yet.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order">
                <h3>Order #<?= $order['order_id'] ?></h3>
                <p><strong>Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
                <p><strong>Total:</strong> <?= number_format($order['total_amount'], 2) ?>da</p>
                <p><strong>Date:</strong> <?= $order['order_date'] ?></p>
                <p><strong>State:</strong><?= htmlspecialchars($order['state']) ?></p>

                <div class="order-actions">
                    <form method="post" action="cancel_order.php" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                        <button type="submit" class="btn-cancel">Cancel Order</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
