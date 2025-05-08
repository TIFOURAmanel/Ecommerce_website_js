<?php
session_start();
require_once('connection.php');

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SERVER['CONTENT_TYPE']) || strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Invalid request']));
}

$input = json_decode(file_get_contents('php://input'), true);

$pdo = getConnection();
$productId = (int)$input['product_id'];
$change = (int)$input['change'];
$cartId = $_SESSION['cart_id'];

if (!isset($_SESSION['basket'])) {
    $_SESSION['basket'] = [];
}

if (isset($input['action']) && $input['action'] === 'remove') {
    // Remove item from cart
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cartId, $productId]);
    $pdo->commit();

    unset($_SESSION['basket'][$productId]);

    echo json_encode(['success' => true, 'message' => 'Item removed']);
    exit;
}

if (isset($input['change'])) {
    // Check if product exists in cart
    $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cartId, $productId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found in cart']);
        exit;
    }

    $newQuantity = $row['quantity'] + $change;

    if ($newQuantity <= 0) {
        // Remove item if quantity is 0 or negative
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?");
        $stmt->execute([$cartId, $productId]);
        $pdo->commit();

        unset($_SESSION['basket'][$productId]);

        echo json_encode(['success' => true, 'message' => 'Item removed due to zero quantity']);
        exit;
    }

    
    // Update quantity 
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$newQuantity, $cartId, $productId]);
    $pdo->commit();

    // Update session
    $_SESSION['basket'][$productId] = $newQuantity;

    echo json_encode(['success' => true, 'new_quantity' => $newQuantity]);
    exit;
}
?>
