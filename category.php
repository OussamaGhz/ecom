<?php
session_start();
require 'config/database.php';

// Get category from URL
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$brand = isset($_GET['brand']) ? trim($_GET['brand']) : '';
$gender = isset($_GET['gender']) ? trim($_GET['gender']) : '';
$size = isset($_GET['size']) ? trim($_GET['size']) : '';
$color = isset($_GET['color']) ? trim($_GET['color']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : '';
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : '';

// Set page title based on category
if ($category) {
    $page_title = ucfirst($category) . " Shoes";
} elseif ($gender) {
    $page_title = ucfirst($gender) . "'s Shoes";
} elseif ($brand) {
    $page_title = $brand . " Shoes";
} else {
    $page_title = "All Shoes";
}

// Build query with filters
$query = "SELECT * FROM items";
$where_clauses = [];
$params = [];

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
    $where_clauses[] = "size LIKE :size";
    $params[':size'] = "%$size%";
}

if ($color) {
    $where_clauses[] = "color = :color";
    $params[':color'] = $color;
}

if ($min_price !== '') {
    $where_clauses[] = "price >= :min_price";
    $params[':min_price'] = $min_price;
}

if ($max_price !== '') {
    $where_clauses[] = "price <= :max_price";
    $params[':max_price'] = $max_price;
}

if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

// Add sorting
if ($sort === 'newest') {
    $query .= " ORDER BY id DESC";
} elseif ($sort === 'price_low') {
    $query .= " ORDER BY price ASC";
} elseif ($sort === 'price_high') {
    $query .= " ORDER BY price DESC";
} elseif ($sort === 'popular') {
    // Using a subquery to sort by popularity (based on order count)
    $query = "SELECT i.*, IFNULL(oc.order_count, 0) as order_count FROM items i 
              LEFT JOIN (
                  SELECT item_id, COUNT(*) as order_count 
                  FROM order_items 
                  GROUP BY item_id
              ) oc ON i.id = oc.item_id";
              
    if (!empty($where_clauses)) {
        $query .= " WHERE " . implode(" AND ", $where_clauses);
    }
    
    $query .= " ORDER BY order_count DESC";
} else {
    $query .= " ORDER BY id DESC";
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

// Execute the query
$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include header after setting variables
include_once 'includes/header.php';
?>

<div class="container">
    <!-- Breadcrumb navigation -->
    <nav class="breadcrumb">
        <a href="index.php">Home</a>
        <span class="separator">/</span>
        <?php if ($gender): ?>
            <a href="category.php?gender=<?php echo htmlspecialchars($gender); ?>">
                <?php echo ucfirst(htmlspecialchars($gender)); ?>'s Shoes
            </a>
        <?php endif; ?>
        
        <?php if ($category): ?>
            <?php if ($gender): ?><span class="separator">/</span><?php endif; ?>
            <span class="active"><?php echo ucfirst(htmlspecialchars($category)); ?> Shoes</span>
        <?php elseif ($brand): ?>
            <span class="active"><?php echo htmlspecialchars($brand); ?> Shoes</span>
        <?php elseif ($gender): ?>
            <!-- Already displayed above -->
        <?php else: ?>
            <span class="active">All Shoes</span>
        <?php endif; ?>
    </nav>

    <div class="category-header">
        <h1 class="page-title"><?php echo $page_title; ?></h1>
        <div class="category-description">
            <?php if ($category === 'athletic'): ?>
                <p>High-performance athletic shoes designed for sports, training, and active lifestyles. Built with comfort, support, and durability in mind.</p>
            <?php elseif ($category === 'casual'): ?>
                <p>Everyday casual shoes that blend comfort and style. Perfect for daily wear, weekends, and relaxed occasions.</p>
            <?php elseif ($category === 'formal'): ?>
                <p>Elegant formal shoes crafted for professional settings and special occasions. Refined designs that elevate any outfit.</p>
            <?php elseif ($category === 'boots'): ?>
                <p>Durable and stylish boots for all seasons. From weather-resistant outdoor boots to fashion-forward designs.</p>
            <?php elseif ($gender === 'men'): ?>
                <p>Explore our collection of men's footwear featuring the latest styles, classic designs, and performance shoes.</p>
            <?php elseif ($gender === 'women'): ?>
                <p>Discover our women's shoe collection with trendy styles, comfortable fits, and versatile options for every occasion.</p>
            <?php elseif ($gender === 'kids'): ?>
                <p>Fun, durable, and comfortable shoes for children of all ages. Designed to support growing feet.</p>
            <?php else: ?>
                <p>Browse our complete collection of premium footwear for every style, occasion, and season.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="category-container">
        <!-- Filter sidebar -->
        <div class="filter-sidebar">
            <div class="filter-header">
                <h3>Filter Options</h3>
                <button class="filter-toggle-mobile">
                    <i class="fas fa-sliders-h"></i> Filters
                </button>
            </div>
            
            <form id="filter-form" method="GET" action="category.php" class="filter-form">
                <?php if ($category): ?>
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                <?php endif; ?>
                
                <div class="filter-section">
                    <h4>Gender</h4>
                    <div class="filter-options">
                        <label class="filter-option">
                            <input type="radio" name="gender" value="men" <?php echo $gender === 'men' ? 'checked' : ''; ?>>
                            <span>Men</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="gender" value="women" <?php echo $gender === 'women' ? 'checked' : ''; ?>>
                            <span>Women</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="gender" value="kids" <?php echo $gender === 'kids' ? 'checked' : ''; ?>>
                            <span>Kids</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="gender" value="" <?php echo $gender === '' ? 'checked' : ''; ?>>
                            <span>All</span>
                        </label>
                    </div>
                </div>
                
                <?php if (!$category && count($brands) > 0): ?>
                <div class="filter-section">
                    <h4>Category</h4>
                    <div class="filter-options">
                        <label class="filter-option">
                            <input type="radio" name="category" value="athletic" <?php echo $category === 'athletic' ? 'checked' : ''; ?>>
                            <span>Athletic</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="category" value="casual" <?php echo $category === 'casual' ? 'checked' : ''; ?>>
                            <span>Casual</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="category" value="formal" <?php echo $category === 'formal' ? 'checked' : ''; ?>>
                            <span>Formal</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="category" value="boots" <?php echo $category === 'boots' ? 'checked' : ''; ?>>
                            <span>Boots</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="category" value="" <?php echo $category === '' ? 'checked' : ''; ?>>
                            <span>All</span>
                        </label>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (count($brands) > 0): ?>
                <div class="filter-section">
                    <h4>Brand</h4>
                    <div class="filter-options scrollable">
                        <label class="filter-option">
                            <input type="radio" name="brand" value="" <?php echo $brand === '' ? 'checked' : ''; ?>>
                            <span>All Brands</span>
                        </label>
                        <?php foreach ($brands as $brandOption): ?>
                        <label class="filter-option">
                            <input type="radio" name="brand" value="<?php echo htmlspecialchars($brandOption); ?>" <?php echo $brand === $brandOption ? 'checked' : ''; ?>>
                            <span><?php echo htmlspecialchars($brandOption); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="filter-section">
                    <h4>Price Range</h4>
                    <div class="price-range">
                        <input type="number" name="min_price" placeholder="Min $" value="<?php echo $min_price; ?>" min="0" step="10">
                        <span>to</span>
                        <input type="number" name="max_price" placeholder="Max $" value="<?php echo $max_price; ?>" min="0" step="10">
                    </div>
                </div>
                
                <?php if (count($colors) > 0): ?>
                <div class="filter-section">
                    <h4>Color</h4>
                    <div class="filter-options scrollable">
                        <label class="filter-option">
                            <input type="radio" name="color" value="" <?php echo $color === '' ? 'checked' : ''; ?>>
                            <span>All Colors</span>
                        </label>
                        <?php foreach ($colors as $colorOption): ?>
                        <label class="filter-option color-option">
                            <input type="radio" name="color" value="<?php echo htmlspecialchars($colorOption); ?>" <?php echo $color === $colorOption ? 'checked' : ''; ?>>
                            <span class="color-swatch" style="background-color: <?php echo htmlspecialchars($colorOption); ?>"></span>
                            <span><?php echo ucfirst(htmlspecialchars($colorOption)); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="category.php<?php echo $category ? '?category=' . htmlspecialchars($category) : ''; ?>" class="btn btn-outline">Clear Filters</a>
                </div>
            </form>
        </div>
        
        <!-- Products grid -->
        <div class="products-container">
            <div class="products-header">
                <div class="products-count">
                    <p><?php echo count($items); ?> shoes found</p>
                </div>
                <div class="sort-options">
                    <label for="sort">Sort by:</label>
                    <select id="sort" name="sort" onchange="applySorting(this.value)">
                        <option value="newest" <?php echo $sort === 'newest' || !$sort ? 'selected' : ''; ?>>Newest</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Popularity</option>
                    </select>
                </div>
            </div>

            <?php if (empty($items)): ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h3>No shoes found</h3>
                    <p>We couldn't find any shoes matching your criteria. Try adjusting your filters or browse our collections.</p>
                    <a href="category.php" class="btn btn-primary">View All Shoes</a>
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
                                <?php if (isset($item['brand']) && $item['brand']): ?>
                                    <div class="product-brand"><?php echo htmlspecialchars($item['brand']); ?></div>
                                <?php endif; ?>
                                <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                                <div class="product-price">
                                    <?php if (isset($item['original_price']) && $item['original_price'] > $item['price']): ?>
                                        <span class="original-price">$<?php echo number_format($item['original_price'], 2); ?></span>
                                    <?php endif; ?>
                                    $<?php echo number_format($item['price'], 2); ?>
                                </div>
                                
                                <?php if (isset($item['color'])): ?>
                                    <div class="product-color">
                                        <span class="color-dot" style="background-color: <?php echo htmlspecialchars($item['color']); ?>"></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="product-actions">
                                    <a href="item.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                                    <form method="POST" action="cart.php">
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" name="add_to_cart" class="btn btn-sm btn-outline">
                                            <i class="fas fa-shopping-cart"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Category page specific styles */
.breadcrumb {
    display: flex;
    padding: var(--space-4) 0;
    margin-bottom: var(--space-4);
    font-size: 0.9rem;
}

.breadcrumb a {
    color: var(--primary-500);
}

.breadcrumb .separator {
    margin: 0 0.5rem;
    color: var(--neutral-400);
}

.breadcrumb .active {
    color: var(--neutral-600);
}

.category-header {
    margin-bottom: var(--space-6);
}

.category-description {
    color: var(--neutral-600);
    max-width: 800px;
}

.category-container {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: var(--space-6);
}

.filter-sidebar {
    background-color: white;
    border-radius: var(--radius-lg);
    padding: var(--space-4);
    box-shadow: 0 2px 10px var(--shadow);
    height: fit-content;
    position: sticky;
    top: 20px;
}

.filter-toggle-mobile {
    display: none;
}

.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-4);
    border-bottom: 1px solid var(--neutral-200);
    padding-bottom: var(--space-3);
}

.filter-section {
    margin-bottom: var(--space-4);
    border-bottom: 1px solid var(--neutral-100);
    padding-bottom: var(--space-3);
}

.filter-section:last-child {
    border-bottom: none;
}

.filter-section h4 {
    margin-bottom: var(--space-3);
    color: var(--neutral-700);
}

.filter-options {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.scrollable {
    max-height: 150px;
    overflow-y: auto;
    padding-right: var(--space-2);
}

.filter-option {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    cursor: pointer;
}

.filter-option input[type="radio"] {
    cursor: pointer;
}

.color-option {
    display: flex;
    align-items: center;
}

.color-swatch {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: inline-block;
    margin-right: var(--space-2);
    border: 1px solid var(--neutral-200);
}

.price-range {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.price-range input {
    width: 80px;
    padding: 0.35rem;
    border: 1px solid var(--neutral-300);
    border-radius: var(--radius-md);
}

.filter-actions {
    margin-top: var(--space-4);
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.products-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-4);
    padding-bottom: var(--space-3);
    border-bottom: 1px solid var(--neutral-200);
}

.sort-options {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.sort-options select {
    padding: 0.35rem 0.75rem;
    border: 1px solid var(--neutral-300);
    border-radius: var(--radius-md);
    background-color: white;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: var(--space-4);
}

.product-card {
    position: relative;
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: 0 5px 15px var(--shadow);
    transition: transform 0.3s ease;
    background: white;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    transform: translateY(-5px);
}

.product-image {
    height: 180px;
    overflow: hidden;
    position: relative;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.product-details {
    padding: var(--space-4);
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.product-brand {
    color: var(--neutral-600);
    font-size: 0.85rem;
    margin-bottom: var(--space-1);
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
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.original-price {
    font-size: 0.9rem;
    color: var(--neutral-500);
    text-decoration: line-through;
    font-weight: normal;
}

.product-color {
    margin-bottom: var(--space-3);
}

.color-dot {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: inline-block;
    border: 1px solid var(--neutral-200);
}

.product-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
}

.product-actions form {
    margin: 0;
}

.empty-state {
    text-align: center;
    padding: var(--space-8) var(--space-4);
    background-color: white;
    border-radius: var(--radius-lg);
    box-shadow: 0 2px 10px var(--shadow);
}

.empty-state i {
    font-size: 3rem;
    color: var(--neutral-400);
    margin-bottom: var(--space-4);
}

.empty-state h3 {
    margin-bottom: var(--space-2);
    color: var(--neutral-700);
}

.empty-state p {
    margin-bottom: var(--space-4);
    color: var(--neutral-600);
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

@media (max-width: 768px) {
    .category-container {
        grid-template-columns: 1fr;
    }
    
    .filter-sidebar {
        position: static;
        margin-bottom: var(--space-4);
    }
    
    .filter-form {
        display: none;
    }
    
    .filter-toggle-mobile {
        display: block;
        background-color: var(--primary-500);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: var(--radius-md);
        cursor: pointer;
    }
    
    .filter-header h3 {
        margin-bottom: 0;
    }
}
</style>

<script>
// Handle sort change
function applySorting(value) {
    // Get current URL
    const url = new URL(window.location.href);
    
    // Update or add sort parameter
    url.searchParams.set('sort', value);
    
    // Redirect to new URL
    window.location.href = url.toString();
}

// Mobile filter toggle
document.addEventListener('DOMContentLoaded', function() {
    const filterToggle = document.querySelector('.filter-toggle-mobile');
    const filterForm = document.querySelector('.filter-form');
    
    if (filterToggle && filterForm) {
        filterToggle.addEventListener('click', function() {
            if (filterForm.style.display === 'block') {
                filterForm.style.display = 'none';
            } else {
                filterForm.style.display = 'block';
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>