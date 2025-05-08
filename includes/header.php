<?php
include_once 'functions.php';   
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    error_log("Session ID: " . session_id());
    error_log("Session Data: " . print_r($_SESSION, true));   
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShoeHaven - <?php echo isset($page_title) ? $page_title : 'Premium Footwear'; ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Base Styles -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <!-- Admin Styles if logged in as admin -->
    <?php if (isAdmin()): ?>
    <link rel="stylesheet" href="assets/css/pages/admin.css">
    <?php endif; ?>
    <!-- Page Specific Styles (will be included in individual pages) -->
    <?php if (isset($page_specific_css)): ?>
        <link rel="stylesheet" href="<?php echo $page_specific_css; ?>">
    <?php endif; ?>
</head>
<body <?php if (isAdmin()): ?>class="admin-mode"<?php endif; ?>>
    <!-- Top announcement bar (optional) - Hidden for admins -->
    <?php if (!isAdmin()): ?>
    <div class="announcement-bar">
        <div class="container">
            <p>Free shipping on orders over $50! <a href="#">Learn More</a></p>
        </div>
    </div>
    <?php endif; ?>

    

    <!-- Main header -->
    <header class="site-header <?php if (isAdmin()): ?>admin-header<?php endif; ?>">
        <div class="container">
            <div class="header-wrapper">
                <!-- Logo -->
                <div class="logo">
                    <?php if (isAdmin()): ?>
                    <a href="admin_dashboard.php">
                        <i class="fas fa-shoe-prints"></i>
                        <span>ShoeHaven Admin</span>
                    </a>
                    <?php else: ?>
                    <a href="index.php">
                        <i class="fas fa-shoe-prints"></i>
                        <span>ShoeHaven</span>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Search bar - conditionally shown -->
                <?php if (!isAdmin() || basename($_SERVER['PHP_SELF']) === 'admin_dashboard.php'): ?>
                <div class="search-bar">
                    <form action="<?php echo isAdmin() ? 'admin_dashboard.php' : 'index.php'; ?>" method="GET">
                        <input type="text" name="search" placeholder="Search for shoes..." 
                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- User actions -->
                <div class="user-actions">
                    <?php if (isLoggedIn()): ?>
                        <div class="dropdown">
                            <button class="dropdown-toggle">
                                <i class="fas fa-user"></i>
                                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            </button>
                            <div class="dropdown-menu">
                                <?php if (isAdmin()): ?>
                                    <a href="admin_dashboard.php">Dashboard</a>
                                    <a href="manage_items.php">Manage Shoes</a>
                                    <a href="view_orders.php">View Orders</a>
                                    <a href="view_users.php">Manage Users</a>
                                <?php else: ?>
                                    <a href="order_history.php">My Orders</a>
                                    <a href="profile.php">My Profile</a>
                                <?php endif; ?>
                                <a href="logout.php">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-sm btn-outline">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login</span>
                        </a>
                    <?php endif; ?>

                    <?php if (!isAdmin() && isLoggedIn()): ?>
                        <a href="cart.php" class="cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <?php 
                                $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                                if ($cart_count > 0): 
                            ?>
                                <span class="cart-count"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main navigation -->
    <nav class="main-nav <?php if (isAdmin()): ?>admin-nav<?php endif; ?>">
        <div class="container">
            <?php if (isAdmin()): ?>
            <!-- Admin Navigation -->
            <ul class="nav-list">
                <li><a href="admin_dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'admin_dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="manage_items.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'manage_items.php' ? 'active' : ''; ?>">Manage Shoes</a></li>
                <li><a href="view_orders.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'view_orders.php' ? 'active' : ''; ?>">Orders</a></li>
                <li><a href="view_users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'view_users.php' ? 'active' : ''; ?>">Users</a></li>
                <li><a href="index.php" class="nav-link">View Store</a></li>
            </ul>
            <?php else: ?>
            <!-- Regular User Navigation -->
            <ul class="nav-list">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="index.php?gender=men" class="nav-link">Men</a></li>
                <li><a href="index.php?gender=women" class="nav-link">Women</a></li>
                <li><a href="index.php?gender=kids" class="nav-link">Kids</a></li>
                <li><a href="index.php?category=athletic" class="nav-link">Athletic</a></li>
                <li><a href="index.php?category=casual" class="nav-link">Casual</a></li>
                <li><a href="index.php?category=formal" class="nav-link">Formal</a></li>
                <li><a href="index.php?category=boots" class="nav-link">Boots</a></li>
                <li><a href="sale.php" class="nav-link sale">Sale</a></li>
            </ul>
            <?php endif; ?>

            <!-- Mobile menu button -->
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Mobile navigation (hidden by default) -->
    <div class="mobile-nav">
        <ul>
            <?php if (isAdmin()): ?>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="manage_items.php">Manage Shoes</a></li>
                <li><a href="view_orders.php">Orders</a></li>
                <li><a href="view_users.php">Users</a></li>
                <li><a href="index.php">View Store</a></li>
            <?php else: ?>
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php?gender=men">Men</a></li>
                <li><a href="index.php?gender=women">Women</a></li>
                <li><a href="index.php?gender=kids">Kids</a></li>
                <li><a href="index.php?category=athletic">Athletic</a></li>
                <li><a href="index.php?category=casual">Casual</a></li>
                <li><a href="index.php?category=formal">Formal</a></li>
                <li><a href="index.php?category=boots">Boots</a></li>
                <li><a href="sale.php">Sale</a></li>
            <?php endif; ?>
            
            <?php if (isLoggedIn()): ?>
                <?php if (!isAdmin()): ?>
                    <li><a href="order_history.php">My Orders</a></li>
                    <li><a href="profile.php">My Profile</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Main content wrapper -->
    <main class="site-content">