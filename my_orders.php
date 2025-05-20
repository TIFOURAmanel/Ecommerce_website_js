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
$stmt->closeCursor(); 

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <link rel="stylesheet" href="my_order.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Italianno&display=swap" rel="stylesheet">

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
                        <form method="post" action="cancel_order.php"
                            onsubmit="return confirm('Are you sure you want to cancel this order?');">
                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                            <button type="submit" class="btn-cancel">Cancel Order</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php include('footer.php'); ?>
    <script>
        // Animate orders on page load
        document.addEventListener('DOMContentLoaded', function () {
            // Animate the title
            anime({
                targets: 'h2',
                translateY: [-30, 0],
                opacity: [0, 1],
                duration: 800,
                easing: 'easeOutExpo'
            });

            // Animate each order card with stagger
            anime({
                targets: '.order',
                translateY: [40, 0],
                opacity: [0, 1],
                duration: 800,
                delay: anime.stagger(100, { start: 300 }),
                easing: 'easeOutExpo'
            });

            // Add hover animation to order cards
            const orders = document.querySelectorAll('.order');
            orders.forEach(order => {
                order.addEventListener('mouseenter', () => {
                    anime({
                        targets: order,
                        scale: 1.02,
                        duration: 300,
                        easing: 'easeInOutQuad'
                    });
                });

                order.addEventListener('mouseleave', () => {
                    anime({
                        targets: order,
                        scale: 1,
                        duration: 300,
                        easing: 'easeInOutQuad'
                    });
                });
            });

            // Add pulse animation to cancel buttons
            const cancelBtns = document.querySelectorAll('.btn-cancel');
            cancelBtns.forEach(btn => {
                btn.addEventListener('mouseenter', () => {
                    anime({
                        targets: btn,
                        scale: 1.05,
                        duration: 300,
                        easing: 'easeInOutQuad'
                    });
                });

                btn.addEventListener('mouseleave', () => {
                    anime({
                        targets: btn,
                        scale: 1,
                        duration: 300,
                        easing: 'easeInOutQuad'
                    });
                });
            });

            // Add animation for empty state
            const emptyState = document.querySelector('p:not(.order p)');
            if (emptyState) {
                anime({
                    targets: emptyState,
                    scale: [0.9, 1],
                    opacity: [0, 1],
                    duration: 800,
                    easing: 'easeOutElastic'
                });
            }
        });
    </script>
</body>

</html>