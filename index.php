<?php
include 'includes/header.php';
session_start();
require 'config/database.php';

// Fetch items with filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$brand = isset($_GET['brand']) ? trim($_GET['brand']) : '';
$gender = isset($_GET['gender']) ? trim($_GET['gender']) : '';
$size = isset($_GET['size']) ? trim($_GET['size']) : '';
$color = isset($_GET['color']) ? trim($_GET['color']) : '';

$query = "SELECT * FROM items";
$where_clauses = [];
$params = [];

if ($search) {
    $where_clauses[] = "(name LIKE :search OR description LIKE :search OR brand LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($category) {
    $where_clauses[] = "style = :category";
    $params[':category'] = $category;
}

if ($brand) {
    $where_clauses[] = "brand = :brand";
    $params[':brand'] = $brand;
}

if ($gender) {
    $where_clauses[] = "gender = :gender";
    $params[':gender'] = $gender;
}

if ($size) {
    $where_clauses[] = "size = :size";
    $params[':size'] = $size;
}

if ($color) {
    $where_clauses[] = "color = :color";
    $params[':color'] = $color;
}

if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

// Get brands for filter
$brandQuery = $conn->query("SELECT DISTINCT brand FROM items WHERE brand IS NOT NULL ORDER BY brand");
$brands = $brandQuery->fetchAll(PDO::FETCH_COLUMN);

// Get colors for filter
$colorQuery = $conn->query("SELECT DISTINCT color FROM items WHERE color IS NOT NULL ORDER BY color");
$colors = $colorQuery->fetchAll(PDO::FETCH_COLUMN);

// Get sizes for filter
$sizeQuery = $conn->query("SELECT DISTINCT size FROM items WHERE size IS NOT NULL ORDER BY size");
$sizes = $sizeQuery->fetchAll(PDO::FETCH_COLUMN);

$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Home";
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-background"></div>
    <div class="container">
        <div class="hero-content">
            <h1>Step Into Style</h1>
            <p>Discover premium footwear for every occasion</p>
            <a href="#featured-products" class="btn btn-primary btn-lg">Shop Now</a>
        </div>
    </div>
</section>

<!-- About Us Section -->
<section class="about-section">
    <div class="container">
        <div class="about-container">
            <div class="about-image">
                <img src="assets/images/store-interior.jpg" alt="ShoeHaven store interior">
            </div>
            <div class="about-content">
                <h2>About ShoeHaven</h2>
                <p class="about-tagline">Crafting comfort for every step since 2010</p>
                <p>Welcome to ShoeHaven, where passion for footwear meets exceptional service. We believe that the right pair of shoes can transform not just your outfit, but your day.</p>
                <p>Our carefully curated collection features premium brands and styles for every occasion—from athletic performance shoes engineered for optimal support to elegant formal options that make a statement.</p>
                <p>Founded by footwear enthusiasts with over 20 years of industry experience, we're committed to helping you find the perfect fit for your lifestyle and budget.</p>
                <a href="about.php" class="btn btn-outline">Learn More About Us</a>
            </div>
        </div>
    </div>
</section>

<!-- Featured Categories -->
<section class="featured-categories">
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <div class="category-grid">
            <a href="index.php?category=athletic" class="category-card">
                <div class="category-icon"><i class="fas fa-running"></i></div>
                <h3>Athletic</h3>
            </a>
            <a href="index.php?category=casual" class="category-card">
                <div class="category-icon"><i class="fas fa-shoe-prints"></i></div>
                <h3>Casual</h3>
            </a>
            <a href="index.php?category=formal" class="category-card">
                <div class="category-icon"><i class="fas fa-briefcase"></i></div>
                <h3>Formal</h3>
            </a>
            <a href="index.php?category=boots" class="category-card">
                <div class="category-icon"><i class="fas fa-hiking"></i></div>
                <h3>Boots</h3>
            </a>
        </div>
    </div>
</section>

<!-- Filter Section -->
<section class="filter-section">
    <div class="container">
        <div class="filter-container">
            <form id="filter-form" method="GET" action="index.php">
                <div class="filter-group">
                    <label for="gender">Gender</label>
                    <select name="gender" id="gender" class="filter-select">
                        <option value="">All</option>
                        <option value="men" <?php echo $gender == 'men' ? 'selected' : ''; ?>>Men</option>
                        <option value="women" <?php echo $gender == 'women' ? 'selected' : ''; ?>>Women</option>
                        <option value="kids" <?php echo $gender == 'kids' ? 'selected' : ''; ?>>Kids</option>
                        <option value="unisex" <?php echo $gender == 'unisex' ? 'selected' : ''; ?>>Unisex</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="brand">Brand</label>
                    <select name="brand" id="brand" class="filter-select">
                        <option value="">All Brands</option>
                        <?php foreach($brands as $brandOption): ?>
                            <option value="<?php echo htmlspecialchars($brandOption); ?>" <?php echo $brand == $brandOption ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brandOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="size">Size</label>
                    <select name="size" id="size" class="filter-select">
                        <option value="">All Sizes</option>
                        <?php foreach($sizes as $sizeOption): ?>
                            <option value="<?php echo htmlspecialchars($sizeOption); ?>" <?php echo $size == $sizeOption ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sizeOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="color">Color</label>
                    <select name="color" id="color" class="filter-select">
                        <option value="">All Colors</option>
                        <?php foreach($colors as $colorOption): ?>
                            <option value="<?php echo htmlspecialchars($colorOption); ?>" <?php echo $color == $colorOption ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($colorOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="index.php" class="btn btn-outline">Clear Filters</a>
            </form>
        </div>
    </div>
</section>

<!-- Products Section -->
<section id="featured-products" class="products-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <?php if ($category): ?>
                    <?php echo htmlspecialchars(ucfirst($category)); ?> Shoes
                <?php elseif ($search): ?>
                    Search results for: "<?php echo htmlspecialchars($search); ?>"
                <?php else: ?>
                    Featured Shoes
                <?php endif; ?>
            </h2>
            
            <?php if (!$search && !$category && !$brand && !$gender && !$size && !$color): ?>
                <div class="section-actions">
                    <a href="index.php?sort=newest" class="btn btn-outline btn-sm">Newest</a>
                    <a href="index.php?sort=popular" class="btn btn-outline btn-sm">Popular</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($items)): ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <p>No shoes found matching your criteria. Try different filters or browse our collections.</p>
                <a href="index.php" class="btn btn-primary">View All Shoes</a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($items as $item): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($item['image']): ?>
                                <img src="assets/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php else: ?>
                                <img src="assets/images/placeholder.png" alt="Shoe image placeholder">
                            <?php endif; ?>
                            
                            <?php if (isset($item['is_sale']) && $item['is_sale']): ?>
                                <div class="product-badge badge-sale">Sale</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-details">
                            <div class="brand-name"><?php echo htmlspecialchars($item['brand'] ?? ''); ?></div>
                            <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <div class="product-price">$<?php echo number_format($item['price'], 2); ?></div>
                            
                            <div class="product-meta">
                                <?php if (isset($item['color']) && $item['color']): ?>
                                    <span class="meta-item color-dot" style="background-color: <?php echo htmlspecialchars($item['color']); ?>"></span>
                                <?php endif; ?>
                                
                                <?php if (isset($item['gender']) && $item['gender']): ?>
                                    <span class="meta-item"><?php echo ucfirst(htmlspecialchars($item['gender'])); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-actions">
                                <a href="item.php?id=<?php echo $item['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Benefits Section -->
<section class="benefits-section">
    <div class="container">
        <h2 class="section-title">Why Shop With Us</h2>
        <div class="benefits-grid">
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-medal"></i>
                </div>
                <h3>Premium Quality</h3>
                <p>We partner with trusted brands to bring you footwear made from high-quality materials built to last.</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <h3>Fast Delivery</h3>
                <p>Free shipping on orders over $50 with express delivery options available. Most orders arrive within 2-5 business days.</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Secure Shopping</h3>
                <p>Shop with confidence knowing your personal and payment information is protected with advanced encryption.</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h3>Easy Returns</h3>
                <p>Not the perfect fit? Free returns within 30 days of purchase, no questions asked.</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Promotion -->
<section class="promo-banner">
    <div class="container">
        <div class="promo-content">
            <h2>Summer Sale</h2>
            <p>Get 30% off on all athletic shoes!</p>
            <a href="index.php?category=athletic" class="btn btn-light">Shop Now</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>