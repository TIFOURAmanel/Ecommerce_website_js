<?php
session_start();
require_once('connection.php');  // Include database connection

$pdo = getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['address'])) {
    try {
        // Traitement normal de la commande
        $_SESSION['order_address'] = $_POST['address'];
        header('Location: confirm_order.php');
        exit;
    } catch (PDOException $e) {
        // Vérifier si c'est une erreur de stock
        if ($e->getCode() == '45000') {
            // Récupérer le dernier message d'erreur pour cet utilisateur
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            $stmt = $pdo->prepare("SELECT error_message FROM order_errors WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$userId]);
            $error = $stmt->fetch();
            
            $_SESSION['error'] = isset($error['error_message']) ? $error['error_message'] : 'Erreur de stock';
        } else {
            $_SESSION['error'] = 'An error occurred while processing your order.Please try again';
        }
        
        header('Location: basket.php');
        exit;
    }
}

header('Location: basket.php');
exit;
?>