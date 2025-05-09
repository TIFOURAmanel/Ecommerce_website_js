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

    // Call the stored procedure
    $stmt = $pdo->prepare("CALL ProcessCustomerOrder(?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        $_SESSION['order_address'],
        $total
    ]);

    // Get the order ID - proper handling of result set
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $orderId = $result['order_id'];



    $pdo->commit();

    if (!$orderId) {
        throw new Exception("Failed to retrieve order ID");
    }

    // Clear address
    unset($_SESSION['order_address']);

    // Redirect to success page
    header("Location: order_success.php?order_id=$orderId");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = $e->getMessage();
    header('Location: basket.php');
    exit;
}