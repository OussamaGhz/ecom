<?php
session_start();
require 'config/database.php';
include 'includes/header.php';

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
$page_title = "Order Confirmation";

// Fetch order details
$stmt = $conn->prepare("
    SELECT orders.*, users.username
    FROM orders 
    JOIN users ON orders.user_id = users.id
    WHERE orders.id = :order_id AND orders.user_id = :user_id
");
$stmt->execute([
    ':order_id' => $order_id,
    ':user_id' => $_SESSION['user_id']
]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if order doesn't exist or doesn't belong to user
if (!$order) {
    header("Location: index.php");
    exit();
}

// Fetch order items
$stmt = $conn->prepare("
    SELECT order_items.*, items.name
    FROM order_items
    JOIN items ON order_items.item_id = items.id
    WHERE order_items.order_id = :order_id
");
$stmt->execute([':order_id' => $order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="confirmation-wrapper">
        <div class="confirmation-header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Order Confirmed!</h1>
            <p>Thank you for your purchase. Your order has been successfully placed.</p>
        </div>
        
        <div class="order-details">
            <h2>Order Details</h2>
            
            <div class="order-info">
                <div class="info-row">
                    <span class="info-label">Order Number:</span>
                    <span class="info-value">#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date:</span>
                    <span class="info-value"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total:</span>
                    <span class="info-value">$<?php echo number_format($order['total_price'], 2); ?></span>
                </div>
            </div>
            
            <table class="order-items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
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
        
        <div class="confirmation-actions">
            <a href="order_history.php" class="btn btn-outline">
                <i class="fas fa-history"></i> View Order History
            </a>
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>