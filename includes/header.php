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
    <title>ShopEasy - <?php echo isset($page_title) ? $page_title : 'Online Store'; ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Base Styles -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <!-- Page Specific Styles (will be included in individual pages) -->
    <?php if (isset($page_specific_css)): ?>
        <link rel="stylesheet" href="<?php echo $page_specific_css; ?>">
    <?php endif; ?>
</head>
<body>
    <!-- Top announcement bar (optional) -->
    <div class="announcement-bar">
        <div class="container">
            <p>Free shipping on orders over $50! <a href="#">Learn More</a></p>
        </div>
    </div>

    <!-- Main header -->
    <header class="site-header">
        <div class="container">
            <div class="header-wrapper">
                <!-- Logo -->
                <div class="logo">
                    <a href="index.php">
                        <i class="fas fa-shopping-bag"></i>
                        <span>ShopEasy</span>
                    </a>
                </div>

                <!-- Search bar -->
                <div class="search-bar">
                    <form action="index.php" method="GET">
                        <input type="text" name="search" placeholder="Search for products..." 
                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- User actions -->
                <div class="user-actions">
                    <?php if (isLoggedIn()): ?>
                        <div class="dropdown">
                            <button class="dropdown-toggle">
                                <i class="fas fa-user"></i>
                                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            </button>
                            <div class="dropdown-menu">
                                <a href="order_history.php">My Orders</a>
                                <a href="profile.php">My Profile</a>
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
    <nav class="main-nav">
        <div class="container">
            <ul class="nav-list">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="index.php?category=electronics" class="nav-link">Electronics</a></li>
                <li><a href="index.php?category=clothing" class="nav-link">Clothing</a></li>
                <li><a href="index.php?category=home" class="nav-link">Home & Garden</a></li>
                <li><a href="index.php?category=books" class="nav-link">Books</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="admin_dashboard.php" class="nav-link admin-link">Admin Panel</a></li>
                <?php endif; ?>
            </ul>

            <!-- Mobile menu button -->
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Mobile navigation (hidden by default) -->
    <div class="mobile-nav">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php?category=electronics">Electronics</a></li>
            <li><a href="index.php?category=clothing">Clothing</a></li>
            <li><a href="index.php?category=home">Home & Garden</a></li>
            <li><a href="index.php?category=books">Books</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="order_history.php">My Orders</a></li>
                <li><a href="profile.php">My Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
            <?php if (isAdmin()): ?>
                <li><a href="admin_dashboard.php">Admin Panel</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Main content wrapper -->
    <main class="site-content">