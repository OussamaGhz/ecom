<?php
session_start();
require 'config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set page title
$page_title = "Order Management";

// Filter by status
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$date_filter = isset($_GET['date']) ? trim($_GET['date']) : '';

// Build query with filters
$query = "
    SELECT orders.*, users.username, users.email 
    FROM orders 
    JOIN users ON orders.user_id = users.id
";

$where_clauses = [];
$params = [];

if ($status_filter) {
    $where_clauses[] = "orders.status = :status";
    $params[':status'] = $status_filter;
}

if ($date_filter) {
    $where_clauses[] = "DATE(orders.created_at) = :date";
    $params[':date'] = $date_filter;
}

if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

$query .= " ORDER BY orders.created_at DESC";

$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get counts for each status
$pending_count = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'pending' OR status IS NULL")->fetchColumn();
$processing_count = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'processing'")->fetchColumn();
$shipped_count = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'shipped'")->fetchColumn();
$delivered_count = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered'")->fetchColumn();

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    $updateStmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $updateStmt->execute([
        ':status' => $new_status,
        ':id' => $order_id
    ]);
    
    // Redirect to refresh the page
    header("Location: view_orders.php" . (empty($_SERVER['QUERY_STRING']) ? "" : "?" . $_SERVER['QUERY_STRING']));
    exit();
}

// Include header after setting variables
include_once 'includes/header.php';
?>

<div class="container">
    <div class="dashboard-header">
        <h1 class="page-title">Order Management</h1>
        <div class="dashboard-actions">
            <a href="admin_dashboard.php" class="btn btn-sm btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
    
    <!-- Order Statistics -->
    <div class="admin-stats order-stats">
        <a href="view_orders.php<?php echo $status_filter === 'pending' ? '' : '?status=pending'; ?>" 
           class="stat-card <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
            <i class="fas fa-clock"></i>
            <h3>Pending</h3>
            <p class="stat-number"><?php echo $pending_count; ?></p>
        </a>
        
        <a href="view_orders.php<?php echo $status_filter === 'processing' ? '' : '?status=processing'; ?>" 
           class="stat-card <?php echo $status_filter === 'processing' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            <h3>Processing</h3>
            <p class="stat-number"><?php echo $processing_count; ?></p>
        </a>
        
        <a href="view_orders.php<?php echo $status_filter === 'shipped' ? '' : '?status=shipped'; ?>" 
           class="stat-card <?php echo $status_filter === 'shipped' ? 'active' : ''; ?>">
            <i class="fas fa-shipping-fast"></i>
            <h3>Shipped</h3>
            <p class="stat-number"><?php echo $shipped_count; ?></p>
        </a>
        
        <a href="view_orders.php<?php echo $status_filter === 'delivered' ? '' : '?status=delivered'; ?>" 
           class="stat-card <?php echo $status_filter === 'delivered' ? 'active' : ''; ?>">
            <i class="fas fa-check-circle"></i>
            <h3>Delivered</h3>
            <p class="stat-number"><?php echo $delivered_count; ?></p>
        </a>
    </div>

    <div class="admin-card">
        <div class="card-header">
            <h2><i class="fas fa-clipboard-list"></i> <?php echo $status_filter ? ucfirst($status_filter) . ' Orders' : 'All Orders'; ?></h2>
            
            <!-- Filter Section -->
            <div class="filter-toolbar">
                <form action="" method="GET" class="date-filter">
                    <?php if ($status_filter): ?>
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                    <?php endif; ?>
                    <div class="form-group inline">
                        <label for="date">Filter by Date:</label>
                        <input type="date" id="date" name="date" value="<?php echo $date_filter; ?>">
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline">Apply</button>
                    <?php if ($date_filter || $status_filter): ?>
                        <a href="view_orders.php" class="btn btn-sm btn-outline">Clear Filters</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <i class="fas fa-clipboard"></i>
                <h3>No Orders Found</h3>
                <p>There are no orders matching the selected filters.</p>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): 
                        // Get order item count
                        $itemCountStmt = $conn->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = :order_id");
                        $itemCountStmt->execute([':order_id' => $order['id']]);
                        $itemCount = $itemCountStmt->fetchColumn();
                        
                        // Get status class
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
                        <tr>
                            <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo htmlspecialchars($order['email']); ?></td>
                            <td><?php echo $itemCount; ?> shoe<?php echo $itemCount != 1 ? 's' : ''; ?></td>
                            <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo $order['status'] ?? 'Pending'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="admin-actions">
                                    <a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <button class="btn btn-sm btn-outline" onclick="toggleStatusForm(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-edit"></i> Status
                                    </button>
                                </div>
                                
                                <!-- Hidden status update form -->
                                <div id="status-form-<?php echo $order['id']; ?>" class="status-update-form" style="display: none;">
                                    <form method="POST" action="">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="new_status" class="status-select">
                                            <option value="pending" <?php echo ($order['status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo ($order['status'] ?? '') === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="shipped" <?php echo ($order['status'] ?? '') === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo ($order['status'] ?? '') === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo ($order['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Order-specific styles */
    .order-stats .stat-card.active {
        border: 2px solid var(--primary-500);
        transform: translateY(-5px);
    }
    
    .filter-toolbar {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-bottom: var(--space-4);
    }
    
    .date-filter {
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }
    
    .form-group.inline {
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }
    
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
    
    .status-update-form {
        margin-top: var(--space-2);
        display: flex;
        align-items: center;
    }
    
    .status-select {
        padding: 0.25rem;
        border-radius: var(--radius-md);
        border: 1px solid var(--neutral-300);
        margin-right: var(--space-2);
    }
</style>

<script>
    function toggleStatusForm(orderId) {
        const form = document.getElementById(`status-form-${orderId}`);
        if (form.style.display === 'none') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }
</script>

<?php include 'includes/footer.php'; ?>