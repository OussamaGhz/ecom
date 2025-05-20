<?php
session_start();
require 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if item ID is provided
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$item_id = $_GET['id'];

// Fetch item details
$stmt = $conn->prepare("SELECT * FROM items WHERE id = :id");
$stmt->execute([':id' => $item_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header("Location: index.php");
    exit();
}

$page_title = $item['name'];
include 'includes/header.php';
?>

<div class="container">
    <nav class="breadcrumb">
        <a href="index.php">Home</a>
        <?php if (isset($item['gender'])): ?>
            <span class="separator">/</span>
            <a href="index.php?gender=<?php echo htmlspecialchars($item['gender']); ?>">
                <?php echo ucfirst(htmlspecialchars($item['gender'])); ?>
            </a>
        <?php endif; ?>
        <?php if (isset($item['style'])): ?>
            <span class="separator">/</span>
            <a href="index.php?category=<?php echo htmlspecialchars($item['style']); ?>">
                <?php echo ucfirst(htmlspecialchars($item['style'])); ?>
            </a>
        <?php endif; ?>
        <span class="separator">/</span>
        <span class="active"><?php echo htmlspecialchars($item['name']); ?></span>
    </nav>

    <div class="product-detail">
    <div class="product-gallery">
        <div class="main-image">
            <img src="image.php?id=<?php echo $item['id']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
        </div>
    </div>
    
    <!-- Add the product info section that was missing -->
    <div class="product-info">
        <?php if (isset($item['brand']) && $item['brand']): ?>
            <div class="product-brand"><?php echo htmlspecialchars($item['brand']); ?></div>
        <?php endif; ?>
        
        <h1 class="product-title"><?php echo htmlspecialchars($item['name']); ?></h1>
        <div class="product-price">$<?php echo number_format($item['price'], 2); ?></div>
        
        <?php if ($item['stock'] > 0): ?>
            <div class="stock-status in-stock">
                <i class="fas fa-check-circle"></i> In Stock (<?php echo $item['stock']; ?> left)
            </div>
        <?php else: ?>
            <div class="stock-status out-of-stock">
                <i class="fas fa-times-circle"></i> Out of Stock
            </div>
        <?php endif; ?>
        
        <div class="product-description">
            <h3>Description</h3>
            <p><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
        </div>
        
        <form method="POST" action="cart_add.php" class="product-form">
            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
            
            <div class="form-group">
                <label for="quantity">Quantity:</label>
                <div class="quantity-selector">
                    <button type="button" class="qty-btn qty-decrease">-</button>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $item['stock']; ?>">
                    <button type="button" class="qty-btn qty-increase">+</button>
                </div>
            </div>
            
            <?php if ($item['stock'] > 0): ?>
                <div class="product-actions">
                    <button type="submit" name="add_to_cart" class="btn btn-primary">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>
</div>

<style>
    .breadcrumb {
        display: flex;
        align-items: center;
        margin-bottom: 2rem;
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
    
    .product-detail {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
    
    .product-gallery {
        display: flex;
        flex-direction: column;
    }
    
    .main-image {
        width: 100%;
        height: auto;
        margin-bottom: 1rem;
        border-radius: var(--radius-lg);
        overflow: hidden;
    }
    
    .main-image img {
        width: 100%;
        height: auto;
        object-fit: cover;
    }
    
    .thumbnail-gallery {
        display: flex;
        gap: 0.5rem;
    }
    
    .thumbnail {
        width: 80px;
        height: 80px;
        border-radius: var(--radius-md);
        overflow: hidden;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.3s;
    }
    
    .thumbnail.active {
        opacity: 1;
        border: 2px solid var(--primary-500);
    }
    
    .thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .product-brand {
        font-size: 1.2rem;
        color: var(--primary-500);
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    
    .product-title {
        font-size: 2rem;
        margin-bottom: 1rem;
    }
    
    .product-meta {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .meta-item {
        background-color: var(--neutral-100);
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.9rem;
    }
    
    .product-price {
        font-size: 1.8rem;
        font-weight: bold;
        color: var(--primary-600);
        margin-bottom: 1rem;
    }
    .breadcrumb {
    display: flex;
    align-items: center;
    margin-bottom: 2rem;
    font-size: 0.9rem;
    padding: var(--space-4) 0;
}

.breadcrumb a {
    color: var(--primary-500);
    transition: color 0.2s ease;
}

.breadcrumb a:hover {
    color: var(--primary-600);
    text-decoration: underline;
}

.breadcrumb .separator {
    margin: 0 0.5rem;
    color: var(--neutral-400);
}

.breadcrumb .active {
    color: var(--neutral-600);
    font-weight: 500;
}

/* Enhanced product layout */
.product-detail {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    margin-bottom: 4rem;
    background-color: white;
    border-radius: var(--radius-xl);
    box-shadow: 0 5px 30px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

/* Image gallery enhancements */
.product-gallery {
    position: relative;
    height: 100%;
}

.main-image {
    width: 100%;
    height: 100%;
    min-height: 500px;
    margin: 0;
    border-radius: 0;
    overflow: hidden;
}

.main-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.7s ease;
}

.product-gallery:hover .main-image img {
    transform: scale(1.03);
}

/* Product info section */
.product-info {
    padding: var(--space-6);
    display: flex;
    flex-direction: column;
}

.product-brand {
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: var(--primary-500);
    margin-bottom: var(--space-2);
    font-weight: 600;
    position: relative;
    display: inline-block;
}

.product-brand:after {
    content: "";
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 40px;
    height: 2px;
    background-color: var(--primary-500);
}

.product-title {
    font-size: 2.5rem;
    margin-bottom: var(--space-4);
    line-height: 1.2;
    color: var(--neutral-800);
}

.product-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: var(--space-4);
    flex-wrap: wrap;
}

.meta-item {
    background-color: var(--neutral-100);
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.85rem;
    color: var(--neutral-700);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.meta-item i {
    color: var(--primary-500);
}

.product-price {
    font-size: 2.2rem;
    font-weight: bold;
    color: var(--primary-600);
    margin-bottom: var(--space-4);
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.original-price {
    font-size: 1.3rem;
    color: var(--neutral-500);
    text-decoration: line-through;
    font-weight: normal;
}

.stock-status {
    margin-bottom: var(--space-5);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    padding: var(--space-3);
    border-radius: var(--radius-lg);
    width: fit-content;
}

.in-stock {
    color: var(--success-500);
    background-color: rgba(var(--success-rgb), 0.1);
}

.out-of-stock {
    color: var(--danger-500);
    background-color: rgba(var(--danger-rgb), 0.1);
}

/* Enhanced form elements */
.product-form {
    margin-bottom: var(--space-4);
}

.form-group {
    margin-bottom: var(--space-4);
}

.form-group label {
    display: block;
    margin-bottom: var(--space-2);
    font-weight: 500;
    color: var(--neutral-700);
}

/* Enhanced quantity selector */
.quantity-selector {
    display: flex;
    align-items: stretch;
    max-width: 180px;
    height: 50px;
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    border: 1px solid var(--neutral-200);
}

.qty-btn {
    width: 50px;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--neutral-100);
    border: none;
    cursor: pointer;
    font-size: 1.2rem;
    color: var(--neutral-700);
    transition: all 0.2s ease;
}

.qty-btn:hover {
    background-color: var(--neutral-200);
    color: var(--primary-600);
}

.quantity-selector input {
    width: 80px;
    height: 100%;
    text-align: center;
    border: none;
    font-size: 1.1rem;
    font-weight: 500;
    color: var(--neutral-800);
}

.product-actions {
    display: flex;
    gap: var(--space-3);
    margin-top: var(--space-5);
}

.product-actions .btn-primary {
    padding: 1rem 2rem;
    background-color: var(--primary-500);
    font-size: 1.1rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.3);
}

.product-actions .btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 15px rgba(var(--primary-rgb), 0.4);
}

.product-actions .btn-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: white;
    border: 1px solid var(--neutral-200);
    color: var(--neutral-700);
    transition: all 0.2s ease;
}

.product-actions .btn-icon:hover {
    background-color: var(--neutral-100);
    color: var(--primary-500);
}

/* Product description section */
.product-description {
    margin-top: var(--space-6);
    padding-top: var(--space-5);
    border-top: 1px solid var(--neutral-200);
}

.product-description h3 {
    font-size: 1.3rem;
    margin-bottom: var(--space-3);
    color: var(--neutral-800);
    position: relative;
    padding-bottom: var(--space-2);
}

.product-description h3:after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 3px;
    background-color: var(--primary-500);
    border-radius: 2px;
}

.product-description p {
    line-height: 1.7;
    color: var(--neutral-600);
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .product-detail {
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
    
    .product-info {
        padding: var(--space-4);
    }
    
    .product-title {
        font-size: 2rem;
    }
    
    .main-image {
        min-height: 400px;
    }
}

@media (max-width: 768px) {
    .product-detail {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .main-image {
        min-height: 350px;
    }
    
    .product-title {
        font-size: 1.75rem;
    }
    
    .product-price {
        font-size: 1.8rem;
    }
    
    .quantity-selector {
        max-width: 100%;
    }
    
    .product-actions {
        flex-direction: column;
    }
    
    .product-actions .btn-primary {
        width: 100%;
    }
}
</style>

<?php include 'includes/footer.php'; ?>