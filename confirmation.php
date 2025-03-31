<?php
session_start();
require 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}

$order_id = intval($_GET['order_id']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation - E-commerce Shop</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Order Confirmation</h1>
    <p>Your order has been successfully placed!</p>
    <p><strong>Order ID:</strong> <?php echo $order_id; ?></p>
    <a href="index.php">Return to Home</a>
</body>
</html>
