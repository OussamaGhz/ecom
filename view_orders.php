<?php
session_start();
include_once 'includes/header.php';
require 'config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Fetch all orders with related user data
$stmt = $conn->query("
    SELECT orders.id, orders.user_id, users.username, orders.total_price, orders.created_at 
    FROM orders 
    JOIN users ON orders.user_id = users.id
    ORDER BY orders.created_at DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Orders</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>All Orders</h1>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>User</th>
                <th>Total Price</th>
                <th>Date</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                    <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                    <td><?php echo $order['created_at']; ?></td>
                    <td><a href="order_details.php?order_id=<?php echo $order['id']; ?>">View Details</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="admin_dashboard.php">Back to Dashboard</a>
</body>
</html>
