<?php
session_start();
require 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch orders for the logged-in user
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute([':user_id' => $user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Order History - E-commerce Shop</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Order History</h1>
    
    <?php if (empty($orders)): ?>
        <p>You have not placed any orders yet.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order">
                <h2>Order ID: <?php echo $order['id']; ?></h2>
                <p><strong>Total:</strong> $<?php echo number_format($order['total'], 2); ?></p>
                <p><strong>Placed on:</strong> <?php echo $order['created_at']; ?></p>
                
                <!-- Fetch order items -->
                <?php
                    $stmt = $conn->prepare("SELECT oi.*, i.name FROM order_items oi 
                                            JOIN items i ON oi.item_id = i.id 
                                            WHERE oi.order_id = :order_id");
                    $stmt->execute([':order_id' => $order['id']]);
                    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <table>
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <hr>
        <?php endforeach; ?>
    <?php endif; ?>

    <a href="index.php">Back to Home</a>
</body>
</html>
