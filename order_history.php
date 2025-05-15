<?php
session_start();
require 'config/database.php';
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Use the GetCustomerOrderHistory stored procedure
$stmt = $conn->prepare("CALL GetCustomerOrderHistory(:user_id)");
$stmt->execute([':user_id' => $user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->nextRowset();  // Move to the next result set (order items)
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group items by order_id for easier access
$orderItemsByOrder = [];
foreach ($orderItems as $item) {
    if (!isset($orderItemsByOrder[$item['order_id']])) {
        $orderItemsByOrder[$item['order_id']] = [];
    }
    $orderItemsByOrder[$item['order_id']][] = $item;
}

$page_title = "Order History";
?>

<div class="container">
    <h1 class="page-title">Order History</h1>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($orders)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            You have not placed any orders yet.
        </div>
        <div class="centered-content">
            <a href="shop.php" class="btn btn-primary">Browse Products</a>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="order-card" data-order-id="<?php echo $order['id']; ?>">
                    <div class="order-header">
                        <div>
                            <h3>Order #<?php echo $order['id']; ?></h3>
                            <span class="order-date"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                        </div>
                        <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </div>
                    </div>
                    
                    <!-- Display order items -->
                    <div class="order-items">
                        <?php if (isset($orderItemsByOrder[$order['id']])): ?>
                            <?php foreach ($orderItemsByOrder[$order['id']] as $item): ?>
                                <div class="order-item">
                                    <div class="item-name">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                        <?php if ($item['brand']): ?>
                                            <span class="item-brand"><?php echo htmlspecialchars($item['brand']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="item-details">
                                        <span>Qty: <?php echo $item['quantity']; ?></span>
                                        <span>$<?php echo number_format($item['price'], 2); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="order-summary">
                        <div class="summary-item">
                            <span>Items:</span>
                            <span><?php echo $order['item_count']; ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Total:</span>
                            <span class="order-total">$<?php echo number_format($order['total_price'], 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <?php if ($order['status'] === 'pending'): ?>
                        <button class="btn btn-outline-danger" onclick="confirmCancel(<?php echo $order['id']; ?>)">
                            <i class="fas fa-times"></i> Cancel Order
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmCancel(orderId) {
    if (confirm("Are you sure you want to cancel this order? This action cannot be undone.")) {
        window.location.href = "cancel_order.php?id=" + orderId;
    }
}
</script>

<style>
.orders-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.order-card {
    border: 1px solid var(--neutral-200);
    border-radius: var(--radius-md);
    padding: var(--space-4);
    background: white;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-3);
}

.order-date {
    color: var(--neutral-500);
    font-size: var(--text-sm);
}

.order-status {
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-full);
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
}

.status-pending {
    background-color: var(--neutral-100);
    color: var(--neutral-700);
}

.status-processing {
    background-color: #fef3c7;
    color: #92400e;
}

.status-shipped {
    background-color: #dbeafe;
    color: #1e40af;
}

.status-delivered {
    background-color: #d1fae5;
    color: #065f46;
}

.status-cancelled {
    background-color: #fee2e2;
    color: #b91c1c;
}

.order-summary {
    display: flex;
    border-top: 1px solid var(--neutral-100);
    border-bottom: 1px solid var(--neutral-100);
    padding: var(--space-3) 0;
    margin-bottom: var(--space-3);
}

.summary-item {
    flex: 1;
    display: flex;
    justify-content: space-between;
}

.order-total {
    font-weight: var(--font-bold);
    color: var(--primary-600);
}

.order-actions {
    display: flex;
    gap: var(--space-3);
}

.centered-content {
    display: flex;
    justify-content: center;
    margin-top: var(--space-6);
}
</style>

<?php include 'includes/footer.php'; ?>