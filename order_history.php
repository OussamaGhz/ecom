<?php
session_start();
require 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch orders
$stmt = $conn->prepare("SELECT o.id, o.created_at, o.status, o.total_price,
                       (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS item_count
                FROM orders o
                WHERE o.user_id = :user_id
                ORDER BY o.created_at DESC");
$stmt->execute([':user_id' => $user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Order History";
include 'includes/header.php';
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
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <h3>Order #<?php echo $order['id']; ?></h3>
                            <span class="order-date"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                        </div>
                        <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-item">
                            <span class="detail-label"><i class="fas fa-box"></i> Items:</span>
                            <span class="detail-value"><?php echo $order['item_count']; ?> items</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label"><i class="fas fa-money-bill-alt"></i> Total:</span>
                            <span class="detail-value order-total">$<?php echo number_format($order['total_price'], 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        <?php if ($order['status'] === 'pending'): ?>
                        <button class="btn btn-sm btn-outline-danger" onclick="confirmCancel(<?php echo $order['id']; ?>)">
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
    margin-top: 1.5rem;
}

.order-card {
    border: 1px solid var(--neutral-200);
    border-radius: 8px;
    padding: 1.25rem;
    background: white;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: transform 0.2s ease;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--neutral-100);
}

.order-info h3 {
    margin: 0 0 0.25rem 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.order-date {
    color: var(--neutral-500);
    font-size: 0.875rem;
}

.order-status {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    text-transform: uppercase;
}

.status-pending {
    background-color: #FFF8E1;
    color: #F57F17;
}

.status-processing {
    background-color: #E1F5FE;
    color: #0277BD;
}

.status-shipped {
    background-color: #E8F5E9;
    color: #2E7D32;
}

.status-delivered {
    background-color: #E0F2F1;
    color: #00695C;
}

.status-cancelled {
    background-color: #FFEBEE;
    color: #C62828;
}

.order-details {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    min-width: 120px;
}

.detail-label {
    font-size: 0.75rem;
    color: var(--neutral-500);
    margin-bottom: 0.25rem;
}

.detail-value {
    font-weight: 500;
}

.order-total {
    color: var(--primary-600);
    font-weight: 700;
}

.order-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--neutral-100);
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .order-header {
        flex-direction: column;
    }
    
    .order-status {
        margin-top: 0.5rem;
        align-self: flex-start;
    }
}
</style>

<?php include 'includes/footer.php'; ?>