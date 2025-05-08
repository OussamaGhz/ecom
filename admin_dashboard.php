<?php
session_start();
include_once 'includes/header.php';
require 'config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Admin Dashboard</h1>
    <ul>
        <li><a href="manage_items.php">Manage Items</a></li>
        <li><a href="view_orders.php">View All Orders</a></li>
        <li><a href="view_users.php">View Users</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</body>
</html>
