<?php
session_start();
require_once('connection.php');

$pdo = getConnection();

if (isset($_GET['order_id'])) {
    $orderId = $_GET['order_id'];
    
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name_prod, p.price, p.image_url 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($products);
    exit;
}

header('HTTP/1.1 400 Bad Request');
echo json_encode(['error' => 'Invalid request']);