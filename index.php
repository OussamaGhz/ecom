<?php
session_start();
require 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$is_admin = $_SESSION['is_admin'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
    <?php if ($is_admin): ?>
        <a href="admin.php">Go to Admin Panel</a><br>
    <?php endif; ?>
    <a href="logout.php">Logout</a>
</body>
</html>
