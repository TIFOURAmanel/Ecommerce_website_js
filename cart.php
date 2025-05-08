<?php
session_start();
require_once('connection.php');

// Initialize basket if not exists
if (!isset($_SESSION['basket'])) {
    $_SESSION['basket'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $action = $_POST['action'];
    $cartId = $_SESSION['cart_id'];
    $pdo = getConnection();

    // Validate quantity
    if ($quantity < 1) $quantity = 1;
    
    if ($action === 'add') {
        $stmt = $pdo->prepare("SELECT price FROM products WHERE product_id = ?");
        $stmt->execute([$productId]);
        $priceData = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch price data as an associative array
        $price = $priceData['price']; // Access the price value
    
        // Add/update product in basket
        if (isset($_SESSION['basket'][$productId])) {
            $_SESSION['basket'][$productId]['quantity'] += $quantity;
            
             // add cart items quantity to bdd
             $pdo->beginTransaction();
             $stmt = $pdo->prepare("UPDATE cart_items SET  quantity = quantity + ? , price = price + ? WHERE cart_id = ? AND product_id = ? ");
             $stmt->execute([ $quantity , $price * $quantity, $cartId, $productId]);
             $pdo->commit();
        } else {
            // You should fetch price from database here for security
            $_SESSION['basket'][$productId] = [
                'quantity' => $quantity,
            ];
             
             $pdo->beginTransaction();
             // add cart items to bdd
             $stmt = $pdo->prepare("INSERT INTO cart_items ( cart_id , product_id , quantity , price) VALUES ( ?, ? ,? ,?)");
             $stmt->execute([ $cartId , $productId , $quantity , $price * $quantity ]);
             $pdo->commit();
        }
    } 
    elseif ($action === 'remove') {
        // Remove product from basket
        unset($_SESSION['basket'][$productId]);
    }
    
    // Redirect back to prevent form resubmission
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit();
}
?> 