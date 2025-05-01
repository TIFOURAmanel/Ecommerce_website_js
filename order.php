<?php
session_start();
require_once('connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign.php");
    exit();
}

// Get database connection
$pdo = getConnection();

try {
    // Begin transaction
    $pdo->beginTransaction();
    
    // 1. Insert order header
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, order_date, adress , total_amount)
        VALUES (?, NOW(), ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $_POST['adress'],
        $_POST['total_amount']
    ]);
    
    $orderId = $pdo->lastInsertId();
    
    // 2. Insert order items
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity)
        VALUES (?, ?, ?)
    ");
    
    foreach ($_SESSION['basket'] as $productId => $item) {
        $stmt->execute([
            $orderId,
            $productId,
            $item['quantity']
        ]);
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Clear basket
    unset($_SESSION['basket']);
    
    // Redirect to confirmation page
    header("Location: order_confirmation.php?order_id=".$orderId);
    exit();
    
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error processing your order. Please try again.";
    header("Location: basket.php");
    exit();
}
?>