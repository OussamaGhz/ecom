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
$stmt = $conn->query("SELECT * FROM items ORDER BY id DESC LIMIT 8");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get counts for each shoe category
$categoryCounts = [];
$categoryQuery = $conn->query("SELECT style, COUNT(*) as count FROM items GROUP BY style");
$categoryResults = $categoryQuery->fetchAll(PDO::FETCH_ASSOC);
foreach ($categoryResults as $category) {
    if ($category['style']) {
        $categoryCounts[$category['style']] = $category['count'];
    }
}

// Get low stock items for alert
$lowStockQuery = $conn->query("SELECT COUNT(*) FROM items WHERE stock <= 5 AND stock > 0");
$lowStockCount = $lowStockQuery->fetchColumn();

// Get out of stock items
$outOfStockQuery = $conn->query("SELECT COUNT(*) FROM items WHERE stock = 0");
$outOfStockCount = $outOfStockQuery->fetchColumn();

// Include header after setting variables
include_once 'includes/header.php';
?>

<div class="container">
    <div class="dashboard-header">
        <h1 class="page-title">ShoeHaven Admin Dashboard</h1>
        <div class="dashboard-actions">
            <a href="manage_items.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Shoe
            </a>
        </div>
    </div>
    
    <!-- Alerts Section -->
    <?php if ($lowStockCount > 0 || $outOfStockCount > 0): ?>
    <div class="admin-alerts">
        <?php if ($lowStockCount > 0): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span><strong><?php echo $lowStockCount; ?> shoes</strong> are low in stock (5 or fewer remaining)</span>
            <a href="manage_items.php?filter=low_stock" class="alert-link">Review Now</a>
        </div>
        <?php endif; ?>
        
        <?php if ($outOfStockCount > 0): ?>
        <div class="alert alert-danger">
            <i class="fas fa-times-circle"></i>
            <span><strong><?php echo $outOfStockCount; ?> shoes</strong> are currently out of stock</span>
            <a href="manage_items.php?filter=out_of_stock" class="alert-link">Review Now</a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Key Stats -->
    <div class="admin-stats">
        <div class="stat-card">
            <i class="fas fa-shoe-prints"></i>
            <h3>Total Shoes</h3>
            <p class="stat-number"><?php echo count($items); ?></p>
        </div>
        
        <?php
        // Count users
        $userStmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
        $userCount = $userStmt->fetchColumn();
        ?>
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <h3>Customers</h3>
            <p class="stat-number"><?php echo $userCount; ?></p>
        </div>
        
        <?php
        // Count orders
        $orderStmt = $conn->query("SELECT COUNT(*) FROM orders");
        $orderCount = $orderStmt->fetchColumn();
        ?>
        <div class="stat-card">
            <i class="fas fa-shopping-cart"></i>
            <h3>Orders</h3>
            <p class="stat-number"><?php echo $orderCount; ?></p>
        </div>
        
        <?php
        // Calculate revenue
        $revenueStmt = $conn->query("SELECT SUM(total_price) FROM orders");
        $totalRevenue = $revenueStmt->fetchColumn() ?: 0;
        ?>
        <div class="stat-card">
            <i class="fas fa-dollar-sign"></i>
            <h3>Total Revenue</h3>
            <p class="stat-number">$<?php echo number_format($totalRevenue, 2); ?></p>
        </div>
    </div>
    
    <!-- Inventory by Category -->
    <div class="admin-card">
        <div class="card-header">
            <h2><i class="fas fa-chart-pie"></i> Inventory by Category</h2>
        </div>
        <div class="category-stats">
            <?php
            $categories = [
                'athletic' => ['name' => 'Athletic', 'icon' => 'fas fa-running'],
                'casual' => ['name' => 'Casual', 'icon' => 'fas fa-tshirt'],
                'formal' => ['name' => 'Formal', 'icon' => 'fas fa-user-tie'],
                'boots' => ['name' => 'Boots', 'icon' => 'fas fa-hiking'],
                'sandals' => ['name' => 'Sandals', 'icon' => 'fas fa-umbrella-beach']
            ];
            
            foreach($categories as $key => $category): 
                $count = isset($categoryCounts[$key]) ? $categoryCounts[$key] : 0;
            ?>
            <div class="category-stat-card">
                <div class="category-icon">
                    <i class="<?php echo $category['icon']; ?>"></i>
                </div>
                <div class="category-details">
                    <h4><?php echo $category['name']; ?></h4>
                    <p><?php echo $count; ?> shoes</p>
                </div>
                <a href="manage_items.php?category=<?php echo $key; ?>" class="category-link">
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Quick Links -->
    <div class="admin-links">
        <a href="manage_items.php" class="admin-link-card">
            <i class="fas fa-shoe-prints"></i>
            <span>Manage Shoes</span>
        </a>
        <a href="view_orders.php" class="admin-link-card">
            <i class="fas fa-clipboard-list"></i>
            <span>Process Orders</span>
        </a>
        <a href="view_users.php" class="admin-link-card">
            <i class="fas fa-users"></i>
            <span>Customer Database</span>
        </a>
        <a href="index.php" class="admin-link-card">
            <i class="fas fa-store"></i>
            <span>View Storefront</span>
        </a>
    </div>
    
    <!-- Recent Inventory Section -->
    <div class="admin-card">
        <div class="card-header">
            <h2><i class="fas fa-shoe-prints"></i> Recent Inventory</h2>
            <a href="manage_items.php" class="btn btn-sm btn-outline">View All Shoes</a>
        </div>
        
        <!-- Shoes display -->
        <div class="products-grid">
            <?php foreach ($items as $item): ?>
                <div class="product-card admin-product">
                    <div class="product-image">
                        <?php if (isset($item['image']) && $item['image']): ?>
                            <img src="assets/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <?php else: ?>
                            <img src="assets/images/placeholder.png" alt="Shoe image placeholder">
                        <?php endif; ?>
                    </div>
                    <div class="product-details">
                        <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <div class="product-meta">
                            <?php if (isset($item['brand']) && $item['brand']): ?>
                                <span class="product-brand"><?php echo htmlspecialchars($item['brand']); ?></span>
                            <?php endif; ?>
                            <?php if (isset($item['style']) && $item['style']): ?>
                                <span class="product-style"><?php echo ucfirst(htmlspecialchars($item['style'])); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-price">$<?php echo number_format($item['price'], 2); ?></div>
                        <?php if ($item['stock'] <= 5 && $item['stock'] > 0): ?>
                            <p class="product-stock text-warning"><i class="fas fa-exclamation-circle"></i> Low Stock: <?php echo $item['stock']; ?> left</p>
                        <?php elseif ($item['stock'] == 0): ?>
                            <p class="product-stock text-danger"><i class="fas fa-times-circle"></i> Out of Stock</p>
                        <?php else: ?>
                            <p class="product-stock text-success"><i class="fas fa-check-circle"></i> In Stock: <?php echo $item['stock']; ?></p>
                        <?php endif; ?>
                        <div class="product-actions">
                            <a href="edit_item.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="manage_items.php?delete=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline delete-btn" 
                               onclick="return confirm('Are you sure you want to delete this shoe?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Recent Orders Section -->
    <div class="admin-card">
        <div class="card-header">
            <h2><i class="fas fa-shopping-bag"></i> Recent Orders</h2>
            <a href="view_orders.php" class="btn btn-sm btn-outline">View All Orders</a>
        </div>
        
        <?php
        $recentOrdersStmt = $conn->query("
            SELECT orders.id, orders.created_at, users.username, orders.total_price 
            FROM orders 
            JOIN users ON orders.user_id = users.id 
            ORDER BY orders.created_at DESC LIMIT 5
        ");
        $recentOrders = $recentOrdersStmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <?php if (empty($recentOrders)): ?>
            <div class="empty-state">
                <i class="fas fa-shopping-bag"></i>
                <h3>No Orders Yet</h3>
                <p>When customers place orders, they'll appear here.</p>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo htmlspecialchars($order['username']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                        <td>
                            <a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Admin Dashboard Specific Styles */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    
    .admin-alerts {
        margin-bottom: 2rem;
    }
    
    .alert {
        display: flex;
        align-items: center;
        padding: 1rem;
        border-radius: var(--radius-md);
        margin-bottom: 1rem;
    }
    
    .alert-warning {
        background-color: #fff3cd;
        border: 1px solid #ffecb5;
        color: #856404;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
    
    .alert i {
        margin-right: 0.75rem;
        font-size: 1.25rem;
    }
    
    .alert-link {
        margin-left: auto;
        font-weight: bold;
        text-decoration: underline;
        color: inherit;
    }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .card-header h2 {
        margin: 0;
        font-size: 1.5rem;
        color: var(--primary-600);
    }
    
    .category-stats {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.5rem;
    }
    
    .category-stat-card {
        display: flex;
        align-items: center;
        background-color: white;
        padding: 1rem;
        border-radius: var(--radius-md);
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .category-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .category-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: var(--primary-50);
        color: var(--primary-600);
        margin-right: 1rem;
    }
    
    .category-icon i {
        font-size: 1.5rem;
    }
    
    .category-details {
        flex: 1;
    }
    
    .category-details h4 {
        margin: 0;
        font-size: 1rem;
        color: var(--text);
    }
    
    .category-details p {
        margin: 0.25rem 0 0;
        font-size: 0.875rem;
        color: var(--neutral-600);
    }
    
    .category-link {
        color: var(--primary-500);
    }
    
    .text-warning {
        color: #e67e22;
    }
    
    .text-danger {
        color: #e74c3c;
    }
    
    .text-success {
        color: #27ae60;
    }
    
    .product-meta {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .product-brand,
    .product-style {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 100px;
        background-color: var(--neutral-100);
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }
    
    .empty-state i {
        font-size: 3rem;
        color: var(--neutral-400);
        margin-bottom: 1rem;
    }
    
    .empty-state h3 {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        color: var(--neutral-700);
    }
    
    .empty-state p {
        color: var(--neutral-600);
        max-width: 400px;
        margin: 0 auto;
    }
</style>

<?php include 'includes/footer.php'; ?>