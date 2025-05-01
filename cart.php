<?php
session_start();

// Initialize basket if not exists
if (!isset($_SESSION['basket'])) {
    $_SESSION['basket'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $action = $_POST['action'];
    
    // Validate quantity
    if ($quantity < 1) $quantity = 1;
    
    if ($action === 'add') {
        // Add/update product in basket
        if (isset($_SESSION['basket'][$productId])) {
            $_SESSION['basket'][$productId]['quantity'] += $quantity;
        } else {
            // You should fetch price from database here for security
            $_SESSION['basket'][$productId] = [
                'quantity' => $quantity,
            ];
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