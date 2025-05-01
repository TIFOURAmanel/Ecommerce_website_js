<?php
session_start();

// Check if request is POST and has JSON data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SERVER['CONTENT_TYPE']) || strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Invalid request']));
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input


$productId = (int)$input['product_id'];
$change = (int)$input['change'];

// Initialize basket if not exists
if (!isset($_SESSION['basket'])) {
    $_SESSION['basket'] = [];
}
if (isset($input['action']) && $input['action'] === 'remove') {
    // Remove item from basket
    if (isset($_SESSION['basket'][$productId])) {
        unset($_SESSION['basket'][$productId]);
        echo json_encode(['success' => true, 'message' => 'Item removed']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Item not found in basket']);
    }
} elseif (isset($input['change'])) {

// Update quantity
if (isset($_SESSION['basket'][$productId])) {
    // Calculate new quantity
    $newQuantity = $_SESSION['basket'][$productId]['quantity'] + $change;
    
    // Ensure quantity doesn't go below 1
    if ($newQuantity < 1) {
        $newQuantity = 1;
    }
    
    // Update quantity
    $_SESSION['basket'][$productId]['quantity'] = $newQuantity;
    
    // Optional: Remove item if quantity reaches 0
    // if ($newQuantity <= 0) {
    //     unset($_SESSION['basket'][$productId]);
    // }
    
    echo json_encode(['success' => true]);
} else {
    // Product not in basket - you might want to add it with initial quantity
    // $_SESSION['basket'][$productId] = ['quantity' => max(1, $change)];
    
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Product not in basket']);
}
}
?>