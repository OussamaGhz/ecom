<?php
session_start();
require 'config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Set page title
$page_title = "Order Details";

// Check if order_id is provided
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    $_SESSION['error'] = "No order ID specified.";
    header("Location: view_orders.php");
    exit();
}

$order_id = intval($_GET['order_id']);

try {
    // Call the stored procedure
    $stmt = $conn->prepare("CALL GetOrderDetails(:order_id)");
    $stmt->execute([':order_id' => $order_id]);
    
    // Fetch order information
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    
    // Move to next result set (items)
    $stmt->nextRowset();
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Move to last result set (total)
    $stmt->nextRowset();
    $totalInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    $orderTotal = $totalInfo['total_amount'];
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: view_orders.php");
    exit();
}

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
            <a href="javascript:window.print()" class="btn btn-sm btn-outline">
                <i class="fas fa-print"></i> Print
            </a>
        </div>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-times-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="admin-card">
        <div class="order-header">
            <div class="order-status-section">
                <h2><i class="fas fa-info-circle"></i> Order Status</h2>
                
                <?php
                // Status class mapping
                $statusClass = 'status-default';
                if (isset($order['status'])) {
                    switch($order['status']) {
                        case 'pending': $statusClass = 'status-warning'; break;
                        case 'processing': $statusClass = 'status-info'; break;
                        case 'shipped': $statusClass = 'status-primary'; break;
                        case 'delivered': $statusClass = 'status-success'; break;
                        case 'cancelled': $statusClass = 'status-danger'; break;
                    }
                }
                ?>
                
                <div class="status-info">
                    <span class="status-badge <?php echo $statusClass; ?>">
                        <?php echo ucfirst($order['status'] ?? 'Pending'); ?>
                    </span>
                    <span class="order-date">
                        <i class="far fa-calendar-alt"></i> Ordered on: <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?>
                    </span>
                </div>
                
                <form method="POST" action="view_orders.php" class="status-form">
                    <div class="form-row">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <div class="form-group">
                            <label for="new_status" class="admin-form-label">Update Status:</label>
                            <select id="new_status" name="new_status" class="admin-form-control">
                                <option value="pending" <?php echo ($order['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo ($order['status'] ?? '') === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo ($order['status'] ?? '') === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo ($order['status'] ?? '') === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo ($order['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
            
            <div class="order-summary">
                <h3>Order Summary</h3>
                <div class="summary-details">
                    <div class="summary-item">
                        <span class="label">Subtotal:</span>
                        <span class="value"><?php echo '$' . number_format($order['total_price'], 2); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Shipping:</span>
                        <span class="value">Free</span>
                    </div>
                    <div class="summary-item total">
                        <span class="label">Total:</span>
                        <span class="value"><?php echo '$' . number_format($order['total_price'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <h2><i class="fas fa-user"></i> Customer Information</h2>
        
        <div class="customer-details">
            <div class="customer-info">
                <div class="info-item">
                    <span class="info-label">Name:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['username']); ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['email']); ?></span>
                </div>
                
                <?php if (isset($order['phone']) && $order['phone']): ?>
                <div class="info-item">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['phone']); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($address): ?>
            <div class="shipping-info">
                <h3>Shipping Address</h3>
                <address>
                    <?php echo htmlspecialchars($address['address_line1']); ?><br>
                    <?php if ($address['address_line2']): echo htmlspecialchars($address['address_line2']) . '<br>'; endif; ?>
                    <?php echo htmlspecialchars($address['city']) . ', ' . htmlspecialchars($address['state']) . ' ' . htmlspecialchars($address['postal_code']); ?><br>
                    <?php echo htmlspecialchars($address['country']); ?>
                </address>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-card">
        <h2><i class="fas fa-shoe-prints"></i> Ordered Shoes</h2>
        
        <?php if (empty($orderItems)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>No Shoes Found</h3>
                <p>This order does not contain any shoes.</p>
            </div>
        <?php else: ?>
            <div class="ordered-shoes">
                <?php foreach ($orderItems as $item): ?>
                <div class="order-item-card">
                    <div class="item-image">
                        <?php if (isset($item['image']) && $item['image']): ?>
                            <img src="assets/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <?php else: ?>
                            <img src="assets/images/placeholder.png" alt="Product image placeholder">
                        <?php endif; ?>
                    </div>
                    
                    <div class="item-details">
                        <h3 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                        
                        <div class="item-meta">
                            <?php if (isset($item['brand']) && $item['brand']): ?>
                                <span class="meta-item"><?php echo htmlspecialchars($item['brand']); ?></span>
                            <?php endif; ?>
                            
                            <?php if (isset($item['style']) && $item['style']): ?>
                                <span class="meta-item"><?php echo ucfirst(htmlspecialchars($item['style'])); ?></span>
                            <?php endif; ?>
                            
                            <?php if (isset($item['color']) && $item['color']): ?>
                                <div class="meta-item color">
                                    <span class="color-dot" style="background-color: <?php echo htmlspecialchars($item['color']); ?>"></span>
                                    <span><?php echo ucfirst(htmlspecialchars($item['color'])); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($item['size']) && $item['size']): ?>
                                <span class="meta-item">Size: <?php echo htmlspecialchars($item['size']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="item-quantity">
                        <span>Qty: <?php echo $item['quantity']; ?></span>
                    </div>
                    
                    <div class="item-price">
                        <div class="price-info">
                            <span class="price-label">Unit Price:</span>
                            <span class="price-value">$<?php echo number_format($item['price'], 2); ?></span>
                        </div>
                        <div class="price-info total">
                            <span class="price-label">Subtotal:</span>
                            <span class="price-value">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Order details specific styles */
.order-header {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-6);
}

@media (max-width: 768px) {
    .order-header {
        grid-template-columns: 1fr;
    }
}

.order-status-section h2,
.order-summary h3 {
    margin-bottom: var(--space-4);
}

.status-info {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: var(--space-4);
    margin-bottom: var(--space-4);
}

.order-date {
    color: var(--neutral-600);
    font-size: 0.9rem;
}

.status-form {
    margin-top: var(--space-4);
}

.status-form .form-row {
    display: flex;
    gap: var(--space-3);
    align-items: flex-end;
}

.status-form .form-group {
    flex: 1;
}

.summary-details {
    background-color: var(--neutral-50);
    padding: var(--space-4);
    border-radius: var(--radius-md);
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: var(--space-2) 0;
    border-bottom: 1px solid var(--neutral-200);
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-item.total {
    margin-top: var(--space-3);
    padding-top: var(--space-3);
    border-top: 2px solid var(--neutral-300);
    font-weight: var(--font-bold);
    font-size: 1.1rem;
}

.customer-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-6);
}

@media (max-width: 768px) {
    .customer-details {
        grid-template-columns: 1fr;
    }
}

.info-item {
    margin-bottom: var(--space-3);
}

.info-label {
    font-weight: var(--font-medium);
    display: inline-block;
    width: 80px;
}

.shipping-info h3 {
    margin-bottom: var(--space-3);
}

.shipping-info address {
    font-style: normal;
    line-height: 1.5;
}

.ordered-shoes {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.order-item-card {
    display: grid;
    grid-template-columns: 100px 1fr auto auto;
    gap: var(--space-4);
    padding: var(--space-4);
    border: 1px solid var(--neutral-200);
    border-radius: var(--radius-md);
    align-items: center;
}

@media (max-width: 992px) {
    .order-item-card {
        grid-template-columns: 80px 1fr auto;
        grid-template-areas:
            "image details price"
            "image quantity quantity";
    }
    
    .item-image {
        grid-area: image;
    }
    
    .item-details {
        grid-area: details;
    }
    
    .item-quantity {
        grid-area: quantity;
    }
    
    .item-price {
        grid-area: price;
    }
}

@media (max-width: 576px) {
    .order-item-card {
        grid-template-columns: 80px 1fr;
        grid-template-areas:
            "image details"
            "quantity price";
    }
}

.item-image {
    width: 100px;
    height: 100px;
    border-radius: var(--radius-md);
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-name {
    margin: 0 0 var(--space-2) 0;
    font-size: var(--text-lg);
}

.item-meta {
    display: flex;
    gap: var(--space-3);
    flex-wrap: wrap;
}

.meta-item {
    font-size: 0.85rem;
    padding: 2px 8px;
    background-color: var(--neutral-100);
    border-radius: var(--radius-full);
    color: var(--neutral-700);
}

.meta-item.color {
    display: flex;
    align-items: center;
    gap: 5px;
}

.color-dot {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 1px solid var(--neutral-300);
}

.item-quantity {
    font-weight: var(--font-medium);
}

.price-info {
    margin-bottom: var(--space-2);
    text-align: right;
}

.price-label {
    color: var(--neutral-600);
    font-size: 0.85rem;
    display: block;
    margin-bottom: 2px;
}

.price-value {
    font-weight: var(--font-medium);
}

.price-info.total .price-value {
    font-weight: var(--font-bold);
    color: var(--primary-600);
}

/* Status badges */
.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: var(--font-medium);
    text-transform: capitalize;
}

.status-warning {
    background-color: #fff3cd;
    color: #856404;
}

.status-info {
    background-color: #d1ecf1;
    color: #0c5460;
}

.status-primary {
    background-color: #cce5ff;
    color: #004085;
}

.status-success {
    background-color: #d4edda;
    color: #155724;
}

.status-danger {
    background-color: #f8d7da;
    color: #721c24;
}

.status-default {
    background-color: #e9ecef;
    color: #495057;
}
</style>

<?php include 'includes/footer.php'; ?>