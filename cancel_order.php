<?php
session_start();
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_SESSION['user_id'])) {
    $orderId = $_POST['order_id'];
    $userId = $_SESSION['user_id'];
    $pdo = getConnection();

    // Validate that the order belongs to the user
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch();

    if ($order) {
        try {
            $pdo->beginTransaction();

            // 1. Restore stock quantities
            $items = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $items->execute([$orderId]);

            foreach ($items as $item) {
                $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?")
                    ->execute([$item['quantity'], $item['product_id']]);
            }

            // 2. Update order status instead of deleting
            $pdo->prepare("UPDATE orders SET state = 'cancelled' WHERE order_id = ?")->execute([$orderId]);

            $pdo->commit();
            $_SESSION['message'] = "Order cancelled successfully.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Failed to cancel order: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Order not found or not yours.";
    }
} else {
    $_SESSION['error'] = "Invalid request.";
}

header("Location: my_orders.php");
exit;
