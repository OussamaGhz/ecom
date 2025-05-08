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
    SELECT orders.*, users.username, users.email, users.phone
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

// Handle status update
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    $updateStmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $updateStmt->execute([
        ':status' => $new_status,
        ':id' => $order_id
    ]);
    
    // Refresh order data
    $stmt->execute([':order_id' => $order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $statusMessage = "Order status updated to " . ucfirst($new_status);
}

// Fetch order items
$stmt = $conn->prepare("
    SELECT order_items.*, items.name, items.image, items.brand, items.color, items.style, items.gender, items.size
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

    <?php if (isset($statusMessage)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $statusMessage; ?>
        </div>
    <?php endif; ?>

    <div class="admin-card">
        <div class="card-header">
            <h2><i class="fas fa-info-circle"></i> Order Information</h2>
            
            <div class="status-badge-container">
                <?php 
                    $statusClass = 'status-default';
                    if (isset($order['status'])) {
                        switch($order['status']) {
                            case 'pending': $statusClass = 'status-warning'; break;
                            case 'processing': $statusClass = 'status-info'; break;
                            case 'shipped': $statusClass = 'status-primary'; break;
                            case 'delivered': $statusClass = 'status-success'; break;
                            case 'cancelled': $statusClass = 'status-danger'; break;
                        }
                    } else {
                        $statusClass = 'status-warning';
                    }
                ?>
                <span class="status-badge large <?php echo $statusClass; ?>">
                    <?php echo $order['status'] ?? 'Pending'; ?>
                </span>
                
                <button class="btn btn-sm btn-outline" onclick="document.getElementById('status-form').style.display = 'flex';">
                    <i class="fas fa-edit"></i> Update Status
                </button>
            </div>
        </div>
        
        <!-- Status update form -->
        <div id="status-form" class="status-form" style="display: none;">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="admin-form-group">
                        <label for="status" class="admin-form-label">Status:</label>
                        <select name="status" id="status" class="admin-form-control">
                            <option value="pending" <?php echo ($order['status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo ($order['status'] ?? '') === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo ($order['status'] ?? '') === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo ($order['status'] ?? '') === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo ($order['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('status-form').style.display = 'none';">Cancel</button>
                </div>
            </form>
        </div>
        
        <div class="order-info">
            <div class="info-section">
                <h3>Order Details</h3>
                <div class="info-group">
                    <span class="info-label">Order Date:</span>
                    <span class="info-value"><?php echo date('F j, Y h:i A', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Total Amount:</span>
                    <span class="info-value price">$<?php echo number_format($order['total_price'], 2); ?></span>
                </div>
            </div>
            
            <div class="info-section">
                <h3>Customer Information</h3>
                <div class="info-group">
                    <span class="info-label">Name:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['username']); ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['email']); ?></span>
                </div>
                <?php if (isset($order['phone']) && $order['phone']): ?>
                <div class="info-group">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['phone']); ?></span>
                </div>
                <?php endif; ?>
            </div>
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
                <?php foreach ($orderItems as $item): 
                    $available_sizes = isset($item['size']) ? explode(',', $item['size']) : [];
                ?>
                <div class="order-item-card">
                    <div class="item-image">
                        <?php if (isset($item['image']) && $item['image']): ?>
                            <img src="assets/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <?php else: ?>
                            <img src="assets/images/placeholder.png" alt="Shoe image placeholder">
                        <?php endif; ?>
                    </div>
                    <div class="item-details">
                        <div class="item-meta">
                            <?php if (isset($item['brand']) && $item['brand']): ?>
                                <span class="item-brand"><?php echo htmlspecialchars($item['brand']); ?></span>
                            <?php endif; ?>
                            
                            <?php if (isset($item['style']) && $item['style']): ?>
                                <span class="item-style"><?php echo ucfirst(htmlspecialchars($item['style'])); ?></span>
                            <?php endif; ?>
                            
                            <?php if (isset($item['gender']) && $item['gender']): ?>
                                <span class="item-gender"><?php echo ucfirst(htmlspecialchars($item['gender'])); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <h3 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                        
                        <div class="item-specs">
                            <?php if (isset($item['color']) && $item['color']): ?>
                                <div class="item-color">
                                    <span class="color-dot" style="background-color: <?php echo $item['color']; ?>"></span>
                                    <span><?php echo ucfirst($item['color']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($available_sizes)): ?>
                                <div class="item-size">
                                    <span>Size: </span>
                                    <strong><?php echo ucfirst($available_sizes[0]); ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="item-quantity">
                            <span>Quantity: </span>
                            <strong><?php echo $item['quantity']; ?></strong>
                        </div>
                    </div>
                    <div class="item-pricing">
                        <div class="item-price">$<?php echo number_format($item['price'], 2); ?></div>
                        <div class="item-subtotal">
                            <span>Subtotal:</span>
                            <strong>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="order-summary">
                    <div class="summary-row total">
                        <span>Total:</span>
                        <strong>$<?php echo number_format($order['total_price'], 2); ?></strong>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--space-4);
    }
    
    .status-badge-container {
        display: flex;
        align-items: center;
        gap: var(--space-3);
    }
    
    .status-badge.large {
        font-size: 1rem;
    }
    
    .status-form {
        background-color: var(--neutral-50);
        padding: var(--space-4);
        border-radius: var(--radius-lg);
        margin-bottom: var(--space-4);
    }
    
    .order-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: var(--space-6);
    }
    
    .info-section {
        margin-bottom: var(--space-4);
    }
    
    .info-section h3 {
        margin-bottom: var(--space-3);
        color: var(--neutral-700);
        font-size: var(--text-lg);
        border-bottom: 1px solid var(--neutral-200);
        padding-bottom: var(--space-2);
    }
    
    .info-group {
        margin-bottom: var(--space-2);
        display: flex;
    }
    
    .info-label {
        font-weight: var(--font-medium);
        min-width: 100px;
    }
    
    .info-value {
        color: var(--neutral-700);
    }
    
    .info-value.price {
        color: var(--primary-600);
        font-weight: var(--font-bold);
    }
    
    .ordered-shoes {
        display: flex;
        flex-direction: column;
        gap: var(--space-4);
    }
    
    .order-item-card {
        display: grid;
        grid-template-columns: 100px 1fr auto;
        gap: var(--space-4);
        padding: var(--space-4);
        border: 1px solid var(--neutral-200);
        border-radius: var(--radius-lg);
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
    
    .item-meta {
        display: flex;
        gap: var(--space-2);
        margin-bottom: var(--space-2);
    }
    
    .item-brand,
    .item-style,
    .item-gender {
        background-color: var(--neutral-100);
        padding: 2px 8px;
        border-radius: var(--radius-md);
        font-size: 0.75rem;
    }
    
    .item-name {
        margin-bottom: var(--space-2);
    }
    
    .item-specs {
        display: flex;
        gap: var(--space-4);
        margin-bottom: var(--space-2);
        font-size: 0.9rem;
    }
    
    .item-color {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .color-dot {
        display: inline-block;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        border: 1px solid var(--neutral-300);
    }
    
    .item-quantity {
        font-size: 0.9rem;
    }
    
    .item-pricing {
        text-align: right;
    }
    
    .item-price {
        font-size: 1.1rem;
        margin-bottom: var(--space-2);
    }
    
    .item-subtotal {
        font-size: 0.9rem;
    }
    
    .item-subtotal strong {
        font-size: 1.1rem;
        color: var(--primary-600);
    }
    
    .order-summary {
        margin-top: var(--space-4);
        padding-top: var(--space-4);
        border-top: 2px solid var(--neutral-300);
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }
    
    .summary-row {
        display: flex;
        gap: var(--space-5);
        align-items: center;
        margin-bottom: var(--space-2);
    }
    
    .summary-row.total {
        font-size: 1.25rem;
    }
    
    .summary-row.total strong {
        color: var(--primary-600);
    }
    
    @media (max-width: 768px) {
        .order-item-card {
            grid-template-columns: 80px 1fr;
        }
        
        .item-pricing {
            grid-column: 1 / 3;
            text-align: left;
            display: flex;
            justify-content: space-between;
            margin-top: var(--space-2);
        }
    }
</style>

<?php include 'includes/footer.php'; ?>