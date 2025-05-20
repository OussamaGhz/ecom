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
            <a href="#categories" class="btn btn-primary" style="background-color: var(--primary-500);">Shop Now <i class="fas fa-arrow-right"></i></a>
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
                    <img src="assets/images/categories/athletic.jpg" alt="Athletic shoes">
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
/* Updated Category Card CSS for a Modern Look */
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
    color: white; /* Changed to white for better contrast over image */
    height: 300px; /* Fixed height for consistent cards */
    text-decoration: none;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px var(--shadow);
}

.category-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.category-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.7s ease;
}

.category-card:hover .category-image img {
    transform: scale(1.1);
}

.category-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.1) 100%);
    z-index: 2;
    transition: background 0.3s ease;
}

.category-card:hover .category-overlay {
    background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.2) 100%);
}

.category-info {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    padding: var(--space-5);
    text-align: left; /* Changed to left alignment */
    z-index: 3;
    transition: transform 0.3s ease;
}

.category-info h3 {
    margin-bottom: var(--space-2);
    font-size: var(--text-xl);
    color: white;
    font-weight: 600;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
}

.category-info p {
    color: rgba(255, 255, 255, 0.85);
    margin-bottom: var(--space-3);
    font-size: 0.95rem;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
    max-width: 85%;
}

.shop-now {
    color: white;
    background-color: rgba(255, 255, 255, 0.2);
    padding: 0.5rem 1rem;
    border-radius: var(--radius);
    font-weight: var(--font-medium);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.category-card:hover .shop-now {
    background-color: var(--primary-500);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .category-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    
    .category-card {
        height: 250px;
    }
    
    .category-info p {
        max-width: 100%;
    }
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

/* Enhanced Product Card CSS for a Modern Look */
.product-card {
    position: relative;
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: 0 5px 15px var(--shadow);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    color: white;
    height: 350px; /* Taller than category cards to fit more content */
    display: block;
    text-decoration: none;
    background: none; /* Remove white background */
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px var(--shadow);
}

.product-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.7s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.1);
}

/* Overlay for text readability */
.product-image::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.1) 60%);
    z-index: 2;
    transition: background 0.3s ease;
}

.product-card:hover .product-image::after {
    background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.2) 60%);
}

.product-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    padding: 0.35rem 0.8rem;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    z-index: 4;
    border-radius: 0; /* Squared corners for modern look */
    transform: rotate(0deg);
    box-shadow: 0 3px 6px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 4px;
}

.product-details {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    padding: var(--space-5);
    text-align: left; /* Changed to left alignment */
    z-index: 3;
    transition: transform 0.3s ease;
}

.product-brand {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.85rem;
    margin-bottom: var(--space-1);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.product-name {
    margin-bottom: var(--space-2);
    font-size: var(--text-lg);
    font-weight: 600;
    color: white;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.product-price {
    font-weight: var(--font-bold);
    color: white;
    margin-bottom: var(--space-3);
    font-size: 1.2rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

/* Style the View Details button */
.product-card .btn-primary {
    background-color: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.product-card:hover .btn-primary {
    background-color: var(--primary-500);
    border-color: var(--primary-500);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .product-card {
        height: 300px;
    }
    
    .products-slider {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
}

.badge-new {
    background: linear-gradient(135deg, #4e6fff 0%, #2745e8 100%);
    color: white;
    clip-path: polygon(0% 0%, 100% 0%, 100% 70%, 90% 100%, 0% 100%); /* Angled bottom right corner */
    padding-right: 1rem;
}

.badge-new::before {
    content: "★";
    font-size: 0.75rem;
    margin-right: 2px;
}

/* Bestseller badge - Gradient red */
.badge-bestseller {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5253 100%);
    color: white;
    clip-path: polygon(10% 0%, 100% 0%, 100% 100%, 0% 100%, 0% 30%); /* Angled top left corner */
    padding-left: 1rem;
}

.badge-bestseller::before {
    content: "♦";
    font-size: 0.75rem;
    margin-right: 2px;
}

/* Sale badge - Gradient gold with dark text */
.badge-sale {
    background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
    color: #222;
    font-weight: 700;
    transform: rotate(-3deg);
    border: 1px dashed rgba(0,0,0,0.2);
}

.badge-sale::before {
    content: "%";
    font-size: 0.75rem;
    margin-right: 2px;
}

/* Badge animations on hover */
.product-card:hover .product-badge {
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 5px 10px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

/* Special positioning for when multiple badges are present */
.product-image .product-badge:nth-of-type(2) {
    top: 55px; /* Position second badge below the first */
}
</style>

<?php include 'includes/footer.php'; ?>