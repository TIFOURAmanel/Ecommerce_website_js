<?php
session_start();
require_once 'connection.php';
$pdo = getConnection();
// Verify required data exists
if (!isset($_SESSION['user_id']) || !isset($_SESSION['order_address']) || empty($_SESSION['basket'])) {
    $_SESSION['error'] = "Missing order information";
    header('Location: basket.php'); // Redirect to basket if data is missing
    exit;
}
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

try {
    $pdo->beginTransaction();
    
    // 1. Check stock quantities
    foreach ($_SESSION['basket'] as $productId => $item) {
        $stmt = $pdo->prepare("SELECT stock_quantity, name_prod FROM products WHERE product_id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if ($item['quantity'] > $product['stock_quantity']) {
            throw new Exception("Insufficient stock for {$product['name_prod']}. Only {$product['stock_quantity']} available.");
        }
    }
    
    // 2. Create order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, address , total_amount, order_date ) VALUES (?, ?,? , NOW() )");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['order_address'] , $total]  );
    $orderId = $pdo->lastInsertId();
    
    // 3. Add order items and update stock
    foreach ($_SESSION['basket'] as $productId => $item) {
        // Add to order_items
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$orderId, $productId, $item['quantity']]);
        
        // Update stock
        $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
        $stmt->execute([$item['quantity'], $productId]);
    }
    
    $pdo->commit();
    
    // Clear cart and address
    unset($_SESSION['basket']);
    unset($_SESSION['order_address']);
    
    // Redirect to success page
    header("Location: order_success.php?order_id=$orderId");
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = $e->getMessage();
    header('Location: basket.php');
    exit;
}
