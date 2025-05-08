<?php
session_start();
require 'config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Set page title
$page_title = "View Orders";

// Fetch all orders with related user data
$stmt = $conn->query("
    SELECT orders.id, orders.user_id, users.username, orders.total_price, orders.created_at 
    FROM orders 
    JOIN users ON orders.user_id = users.id
    ORDER BY orders.created_at DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    <div class="admin-card">
        <h2><i class="fas fa-clipboard-list"></i> All Orders</h2>
        
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <i class="fas fa-clipboard"></i>
                <h3>No Orders Found</h3>
                <p>There are no orders in the system yet.</p>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total Price</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                            <td>
                                <div class="admin-actions">
                                    <a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>