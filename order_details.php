<?php
session_start();
include_once 'includes/header.php';
require 'config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    header("Location: view_orders.php");
    exit();
}

// Fetch order details
$stmt = $conn->prepare("
    SELECT items.name, order_items.quantity, order_items.price 
    FROM order_items 
    JOIN items ON order_items.item_id = items.id 
    WHERE order_items.order_id = :order_id
");
$stmt->execute([':order_id' => $order_id]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Order Details</h1>

    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orderItems as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="view_orders.php">Back to Orders</a>
</body>
</html>
