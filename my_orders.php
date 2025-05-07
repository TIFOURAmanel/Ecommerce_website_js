<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pdo = getConnection();
$userId = $_SESSION['user_id'];

// Call the stored procedure
$stmt = $pdo->prepare("CALL AfficherHistoriqueCommandes(?)");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor(); // Required when calling stored procedures

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <link rel="stylesheet" href="order.css">
</head>
<body>
<?php include('header.php'); ?>
    <h2>My Orders</h2>

    <?php if (empty($orders)): ?>
        <p>You have no orders yet.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order">
                <h3>Order #<?= $order['order_id'] ?></h3>
                <p><strong>Date:</strong> <?= $order['order_date'] ?></p>
                <p><strong>Total:</strong> <?= number_format($order['total_amount'], 2) ?>da</p>
                <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
                <p><strong>Products:</strong> <?= htmlspecialchars($order['produits']) ?></p>

                <?php if ($order['status'] !== 'cancelled'): ?>
                    <div class="order-actions">
                        <form method="post" action="cancel_order.php" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                            <button type="submit" class="btn-cancel">Cancel Order</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php include('footer.php'); ?>
</body>
</html>

