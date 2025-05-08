<?php
session_start();
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

// Set page title
$page_title = "Order Details";

// Fetch order details
$stmt = $conn->prepare("
    SELECT orders.*, users.username, users.email
    FROM orders 
    JOIN users ON orders.user_id = users.id
    WHERE orders.id = :order_id
");
$stmt->execute([':order_id' => $order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: view_orders.php");
    exit();
}

// Fetch order items
$stmt = $conn->prepare("
    SELECT order_items.*, items.name, items.image 
    FROM order_items 
    JOIN items ON order_items.item_id = items.id 
    WHERE order_items.order_id = :order_id
");
$stmt->execute([':order_id' => $order_id]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include header after setting variables
include_once 'includes/header.php';
?>

<div class="container">
    <div class="dashboard-header">
        <h1 class="page-title">Order #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></h1>
        <div class="dashboard-actions">
            <a href="view_orders.php" class="btn btn-sm btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>

    <div class="admin-card">
        <h2><i class="fas fa-info-circle"></i> Order Information</h2>
        
        <div class="order-info">
            <div class="info-group">
                <span class="info-label">Order Date:</span>
                <span class="info-value"><?php echo date('F j, Y h:i A', strtotime($order['created_at'])); ?></span>
            </div>
            <div class="info-group">
                <span class="info-label">Customer:</span>
                <span class="info-value"><?php echo htmlspecialchars($order['username']); ?></span>
            </div>
            <div class="info-group">
                <span class="info-label">Email:</span>
                <span class="info-value"><?php echo htmlspecialchars($order['email']); ?></span>
            </div>
            <div class="info-group">
                <span class="info-label">Total Amount:</span>
                <span class="info-value">$<?php echo number_format($order['total_price'], 2); ?></span>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <h2><i class="fas fa-box"></i> Order Items</h2>
        
        <?php if (empty($orderItems)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>No Items Found</h3>
                <p>This order does not contain any items.</p>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td>
                                <div class="order-item">
                                    <div class="item-image-small">
                                        <?php if (isset($item['image']) && $item['image']): ?>
                                            <img src="assets/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        <?php else: ?>
                                            <img src="assets/images/placeholder.png" alt="Product image placeholder">
                                        <?php endif; ?>
                                    </div>
                                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                                </div>
                            </td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right"><strong>Total:</strong></td>
                        <td><strong>$<?php echo number_format($order['total_price'], 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
    .order-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: var(--space-4);
    }
    
    .info-group {
        margin-bottom: var(--space-3);
    }
    
    .info-label {
        font-weight: var(--font-medium);
        margin-right: var(--space-2);
    }
    
    .order-item {
        display: flex;
        align-items: center;
        gap: var(--space-3);
    }
    
    .item-image-small {
        width: 50px;
        height: 50px;
        border-radius: var(--radius-md);
        overflow: hidden;
    }
    
    .item-image-small img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .text-right {
        text-align: right;
    }
</style>

<?php include 'includes/footer.php'; ?>