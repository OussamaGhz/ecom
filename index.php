<?php
include 'includes/header.php';
session_start();
require 'config/database.php';

// Fetch featured products (newest arrivals)
$newArrivalsStmt = $conn->query("SELECT * FROM items ORDER BY id DESC LIMIT 4");
$newArrivals = $newArrivalsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch best sellers (most ordered items)
$bestSellersStmt = $conn->query("
    SELECT i.*, COUNT(oi.item_id) as order_count 
    FROM items i 
    JOIN order_items oi ON i.id = oi.item_id 
    GROUP BY i.id 
    ORDER BY order_count DESC 
    LIMIT 4
");
$bestSellers = $bestSellersStmt->fetchAll(PDO::FETCH_ASSOC);

// Set page title
$page_title = "Home";
?>

<?php if(isset($_GET['order_success']) && $_GET['order_success'] === 'true'): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        Your order #<?php echo htmlspecialchars($_GET['order_id']); ?> has been placed successfully!
    </div>
<?php endif; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-background" style="background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/hero.png'); filter: blur(1px);"></div>
    <div class="container hero-content">
        <h1 style="color: white; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);">Step Into Comfort & Style</h1>
        <p style="color: white; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);">Discover our premium collection of shoes for every occasion</p>
        <div class="hero-buttons">
            <a href="#categories" class="btn btn-primary">Shop Now</a>
            <a href="about.php" class="btn btn-outline" style="color: white; border-color: white;">Learn More</a>
        </div>
    </div>
</section>

<!-- About Us Section -->
<section class="about-section">
    <div class="container about-container">
        <div class="about-image">
            <img src="assets/images/about1.png" alt="ShoeHaven store interior">
        </div>
        <div class="about-content">
            <h2>Welcome to ShoeHaven</h2>
            <p class="about-tagline">Crafting comfort for every step since 2010</p>
            <p>Welcome to ShoeHaven, where passion for footwear meets exceptional service. We believe that the right pair of shoes can transform not just your outfit, but your day.</p>
            <p>Our carefully curated collection features premium brands and styles for every occasion—from athletic performance shoes engineered for optimal support to elegant formal options that make a statement.</p>
            <a href="about.php" class="btn btn-outline">Learn More About Us</a>
        </div>
    </div>
</section>

<!-- Featured Categories -->
<section id="categories" class="featured-categories">
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <div class="category-grid">
            <a href="category.php?category=athletic" class="category-card">
                <div class="category-image">
                    <img src="assets/images/categories/athletic.png" alt="Athletic shoes">
                </div>
                <div class="category-overlay"></div>
                <div class="category-info">
                    <h3>Athletic</h3>
                    <p>Performance shoes for sports & training</p>
                    <span class="shop-now">Shop Now <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <a href="category.php?category=casual" class="category-card">
                <div class="category-image">
                    <img src="assets/images/categories/casual.jpg" alt="Casual shoes">
                </div>
                <div class="category-overlay"></div>
                <div class="category-info">
                    <h3>Casual</h3>
                    <p>Everyday comfort & style</p>
                    <span class="shop-now">Shop Now <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <a href="category.php?category=formal" class="category-card">
                <div class="category-image">
                    <img src="assets/images/categories/formal.jpg" alt="Formal shoes">
                </div>
                <div class="category-overlay"></div>
                <div class="category-info">
                    <h3>Formal</h3>
                    <p>Elegant shoes for special occasions</p>
                    <span class="shop-now">Shop Now <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <a href="category.php?category=boots" class="category-card">
                <div class="category-image">
                    <img src="assets/images/categories/boots.jpg" alt="Boots">
                </div>
                <div class="category-overlay"></div>
                <div class="category-info">
                    <h3>Boots</h3>
                    <p>Durable & stylish for any weather</p>
                    <span class="shop-now">Shop Now <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- Latest Arrivals Section -->
<section class="latest-arrivals">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">New Arrivals</h2>
            <a href="category.php?sort=newest" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="products-slider">
            <?php foreach ($newArrivals as $item): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($item['image']): ?>
                            <img src="assets/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <?php else: ?>
                            <img src="assets/images/placeholder.png" alt="Shoe image placeholder">
                        <?php endif; ?>
                        <div class="product-badge badge-new">New</div>
                    </div>
                    <div class="product-details">
                        <?php if (isset($item['brand']) && $item['brand']): ?>
                            <div class="product-brand"><?php echo htmlspecialchars($item['brand']); ?></div>
                        <?php endif; ?>
                        <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <div class="product-price">$<?php echo number_format($item['price'], 2); ?></div>
                        <a href="item.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Best Sellers Section -->
<section class="best-sellers">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Best Sellers</h2>
            <a href="category.php?sort=popular" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="products-slider">
            <?php foreach ($bestSellers as $item): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($item['image']): ?>
                            <img src="assets/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <?php else: ?>
                            <img src="assets/images/placeholder.png" alt="Shoe image placeholder">
                        <?php endif; ?>
                        <div class="product-badge badge-bestseller">Best Seller</div>
                    </div>
                    <div class="product-details">
                        <?php if (isset($item['brand']) && $item['brand']): ?>
                            <div class="product-brand"><?php echo htmlspecialchars($item['brand']); ?></div>
                        <?php endif; ?>
                        <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <div class="product-price">$<?php echo number_format($item['price'], 2); ?></div>
                        <a href="item.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>



<!-- Featured Collection Banner -->
<section class="promo-banner" style="background-image: url('assets/images/collection.png'); background-size: cover; background-position: center; height: 60vh; display: flex; align-items: center; justify-content: center; color: white;">
    <div class="container">
        <div class="promo-content">
            <h2>Summer Collection 2025</h2>
            <p>Discover our lightweight and comfortable shoes perfect for warm weather</p>
            <a href="category.php?collection=summer" class="btn btn-light">Shop the Collection</a>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="benefits-section">
    <div class="container">
        <div class="benefits-grid">
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3>Quality Guaranteed</h3>
                <p>We source only the highest quality shoes from trusted brands and manufacturers.</p>
            </div>
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h3>Fast Delivery</h3>
                <p>Enjoy free shipping on orders over $50 with quick delivery to your doorstep.</p>
            </div>
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h3>Easy Returns</h3>
                <p>Not the right fit? Return within 30 days for a full refund or exchange.</p>
            </div>
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Secure Shopping</h3>
                <p>Shop with confidence knowing your personal information is protected.</p>
            </div>
        </div>
    </div>
</section>

<style>
/* Enhanced styles for the homepage */
.category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--space-5);
    margin-top: var(--space-6);
}

.category-card {
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: 0 5px 15px var(--shadow);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    display: block;
    color: inherit;
    height: 100%;
    background: white;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px var(--shadow);
}

.category-image {
    height: 200px;
    overflow: hidden;
}

.category-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.category-card:hover .category-image img {
    transform: scale(1.05);
}

.category-info {
    padding: var(--space-4);
    text-align: center;
}

.category-info h3 {
    margin-bottom: var(--space-2);
    font-size: var(--text-xl);
}

.category-info p {
    color: var(--neutral-600);
    margin-bottom: var(--space-3);
}

.shop-now {
    color: var(--primary-500);
    font-weight: var(--font-medium);
    display: inline-block;
    transition: transform 0.2s ease;
}

.category-card:hover .shop-now {
    transform: translateX(5px);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-5);
}

.view-all {
    color: var(--primary-500);
    font-weight: var(--font-medium);
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.view-all:hover {
    color: var(--primary-600);
}

.products-slider {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: var(--space-4);
}

.latest-arrivals,
.best-sellers {
    padding: var(--space-8) 0;
}

.best-sellers {
    background-color: var(--neutral-50);
}

.product-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    font-weight: var(--font-medium);
}

.badge-new {
    background-color: var(--primary-500);
    color: white;
}

.badge-bestseller {
    background-color: #ff6b6b;
    color: white;
}

.badge-sale {
    background-color: #fcc419;
    color: var(--neutral-800);
}

.product-brand {
    color: var(--neutral-600);
    font-size: 0.85rem;
    margin-bottom: var(--space-1);
}

.product-card {
    position: relative;
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: 0 5px 15px var(--shadow);
    transition: transform 0.3s ease;
    background: white;
}

.product-card:hover {
    transform: translateY(-5px);
}

.product-image {
    height: 200px;
    overflow: hidden;
    position: relative;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-details {
    padding: var(--space-4);
    text-align: center;
}

.product-name {
    margin-bottom: var(--space-2);
    font-size: var(--text-lg);
}

.product-price {
    font-weight: var(--font-bold);
    color: var(--primary-500);
    margin-bottom: var(--space-3);
    font-size: 1.1rem;
}
</style>

<?php include 'includes/footer.php'; ?>