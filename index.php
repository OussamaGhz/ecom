<?php
include 'includes/header.php';
session_start();
require 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch items from the database
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$query = "SELECT * FROM items";
$where_clauses = [];
$params = [];

if ($search) {
    $where_clauses[] = "(name LIKE :search OR description LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($category) {
    $where_clauses[] = "category = :category";
    $params[':category'] = $category;
}

if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$username = $_SESSION['username'];
$is_admin = $_SESSION['is_admin'];
$page_title = "Home";
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Welcome to ShopEasy</h1>
            <p>Discover quality products at affordable prices</p>
            <a href="#featured-products" class="btn btn-primary btn-lg">Shop Now</a>
        </div>
    </div>
</section>

<!-- Featured Categories -->
<section class="featured-categories">
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <div class="category-grid">
            <a href="index.php?category=electronics" class="category-card">
                <div class="category-icon"><i class="fas fa-laptop"></i></div>
                <h3>Electronics</h3>
            </a>
            <a href="index.php?category=clothing" class="category-card">
                <div class="category-icon"><i class="fas fa-tshirt"></i></div>
                <h3>Clothing</h3>
            </a>
            <a href="index.php?category=home" class="category-card">
                <div class="category-icon"><i class="fas fa-home"></i></div>
                <h3>Home & Garden</h3>
            </a>
            <a href="index.php?category=books" class="category-card">
                <div class="category-icon"><i class="fas fa-book"></i></div>
                <h3>Books</h3>
            </a>
        </div>
    </div>
</section>

<!-- Products Section -->
<section id="featured-products" class="products-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <?php echo $category ? htmlspecialchars(ucfirst($category)) : 'Featured Products'; ?>
                <?php if ($search): ?>
                    <span class="search-results-text">Search results for: "<?php echo htmlspecialchars($search); ?>"</span>
                <?php endif; ?>
            </h2>
            
            <?php if (!$search && !$category): ?>
                <div class="section-actions">
                    <a href="index.php?sort=newest" class="btn btn-outline btn-sm">Newest</a>
                    <a href="index.php?sort=popular" class="btn btn-outline btn-sm">Popular</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($items)): ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <p>No products found. Try a different search term or category.</p>
                <a href="index.php" class="btn btn-primary">View All Products</a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($items as $item): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($item['image']): ?>
                                <img src="assets/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php else: ?>
                                <img src="assets/images/placeholder.png" alt="Product image placeholder">
                            <?php endif; ?>
                            
                            <?php if (isset($item['is_sale']) && $item['is_sale']): ?>
                                <div class="product-badge badge-sale">Sale</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-details">
                            <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <div class="product-price">$<?php echo number_format($item['price'], 2); ?></div>
                            <p class="product-description">
                                <?php echo strlen($item['description']) > 100 ? 
                                    htmlspecialchars(substr($item['description'], 0, 100)) . '...' : 
                                    htmlspecialchars($item['description']); ?>
                            </p>
                            <div class="product-actions">
                                <a href="item.php?id=<?php echo $item['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-shopping-cart"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Featured Promotion -->
<section class="promo-banner">
    <div class="container">
        <div class="promo-content">
            <h2>Special Offer</h2>
            <p>Get 20% off on your first order!</p>
            <a href="#" class="btn btn-light">Shop Now</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>