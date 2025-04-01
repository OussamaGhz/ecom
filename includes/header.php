<?php
include_once 'functions.php';   
?>
<!DOCTYPE html>
<html>
<head>
    <title>E-Commerce Site</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
<nav>
    <a href="index.php">Home</a>
    <?php if (isLoggedIn()): ?>
        
      
        <?php if (isAdmin()): ?>
            <a href="admin.php">Admin Panel</a>
        <?php endif; ?>
        <a href="cart.php">Cart</a>
        <a href="order_history.php">My Orders</a>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    <?php endif; ?>
</nav>