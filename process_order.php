<?php
session_start();
require_once('connection.php'); // Include your database connection

$pdo = getConnection();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['address'])) {
    $_SESSION['order_address'] = $_POST['address'];
    header('Location: confirm_order.php');
    exit;
}

// If not a POST request or address not set
header('Location: basket.php');
exit;
?>