<?php
session_start();
require 'config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Set page title
$page_title = "Admin Dashboard";

// Fetch all products to display on admin dashboard
$stmt = $conn->query("SELECT * FROM items ORDER BY id DESC");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include header after setting variables
include_once 'includes/header.php';
?>

<div class="container">
    <div class="dashboard-header">
        <h1 class="page-title">Admin Dashboard</h1>
        <div class="dashboard-actions">
            <a href="manage_items.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Product
            </a>
        </div>
    </div>
    
    <div class="admin-stats">
        <div class="stat-card">
            <i class="fas fa-box"></i>
            <h3>Total Products</h3>
            <p class="stat-number"><?php echo count($items); ?></p>
        </div>
        
        <?php
        // Count users
        $userStmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
        $userCount = $userStmt->fetchColumn();
        ?>
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <h3>Total Users</h3>
            <p class="stat-number"><?php echo $userCount; ?></p>
        </div>
        
        <?php
        // Count orders
        $orderStmt = $conn->query("SELECT COUNT(*) FROM orders");
        $orderCount = $orderStmt->fetchColumn();
        ?>
        <div class="stat-card">
            <i class="fas fa-shopping-cart"></i>
            <h3>Total Orders</h3>
            <p class="stat-number"><?php echo $orderCount; ?></p>
        </div>
    </div>
    
    <div class="admin-links">
        <a href="manage_items.php" class="admin-link-card">
            <i class="fas fa-box-open"></i>
            <span>Manage Products</span>
        </a>
        <a href="view_orders.php" class="admin-link-card">
            <i class="fas fa-clipboard-list"></i>
            <span>View Orders</span>
        </a>
        <a href="view_users.php" class="admin-link-card">
            <i class="fas fa-users"></i>
            <span>Manage Users</span>
        </a>
    </div>
    
    <h2 class="section-title">Product Inventory</h2>
    
    <!-- Products display -->
    <div class="products-grid">
        <?php foreach ($items as $item): ?>
            <div class="product-card admin-product">
                <div class="product-image">
                    <?php if ($item['image']): ?>
                        <img src="assets/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <?php else: ?>
                        <img src="assets/images/placeholder.png" alt="Product image placeholder">
                    <?php endif; ?>
                </div>
                <div class="product-details">
                    <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                    <div class="product-price">$<?php echo number_format($item['price'], 2); ?></div>
                    <p class="product-stock">Stock: <?php echo $item['stock']; ?> units</p>
                    <div class="product-actions">
                        <a href="edit_item.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="manage_items.php?delete=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline delete-btn" 
                           onclick="return confirm('Are you sure you want to delete this item?');">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        al