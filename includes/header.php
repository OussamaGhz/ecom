<?php
include_once 'functions.php';   
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session only if it's not already active
// log session detials
    error_log("Session ID: " . session_id());
    error_log("Session Data: " . print_r($_SESSION, true));   
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>E-Commerce Site</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
<nav>
   
    <?php if (isLoggedIn()): ?> <!-- Check if the user is logged in -->
        <?php if (isAdmin()): ?> <!-- Check if the user is an admin -->
            <a href="admin_dashboard.php">Admin Panel</a>
        <?php else: ?>
        <a href="cart.php">Cart</a>
        <a href="order_history.php">My Orders</a>
        <a href="logout.php">Logout</a>
        <?php endif; ?>
    <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    <?php endif; ?>
</nav>