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
                <?php if ($item['image']): ?>
                    <img src="assets/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                <?php else: ?>
                    <img src="assets/images/placeholder.png" alt="Shoe image placeholder">
                <?php endif; ?>
            </div>
            <div class="thumbnail-gallery">
                <!-- Placeholder thumbnails - in a real implementation, you'd have multiple images -->
                <div class="thumbnail active">
                    <?php if ($item['image']): ?>
                        <img src="assets/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <?php else: ?>
                        <img src="assets/images/placeholder.png" alt="Shoe image placeholder">
                    <?php endif; ?>
                </div>
                <div class="thumbnail">
                    <img src="assets/images/shoe-angle2.png" alt="Side view">
                </div>
                <div class="thumbnail">
                    <img src="assets/images/shoe-angle3.png" alt="Back view">
                </div>
                <div class="thumbnail">
                    <img src="assets/images/shoe-angle4.png" alt="Top view">
                </div>
            </div>
        </div>
        
        <div class="product-info">
            <?php if (isset($item['brand']) && $item['brand']): ?>
                <div class="product-brand"><?php echo htmlspecialchars($item['brand']); ?></div>
            <?php endif; ?>
            
            <h1 class="product-title"><?php echo htmlspecialchars($item['name']); ?></h1>
            
            <div class="product-meta">
                <?php if (isset($item['style']) && $item['style']): ?>
                    <span class="meta-item style"><?php echo ucfirst(htmlspecialchars($item['style'])); ?></span>
                <?php endif; ?>
                
                <?php if (isset($item['gender']) && $item['gender']): ?>
                    <span class="meta-item gender"><?php echo ucfirst(htmlspecialchars($item['gender'])); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="product-price">$<?php echo number_format($item['price'], 2); ?></div>
            
            <?php if ($item['stock'] > 0): ?>
                <div class="stock-status in-stock">
                    <i class="fas fa-check-circle"></i> In Stock
                </div>
            <?php else: ?>
                <div class="stock-status out-of-stock">
                    <i class="fas fa-times-circle"></i> Out of Stock
                </div>
            <?php endif; ?>
            
            <form action="cart_add.php" method="POST" class="product-form">
                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                
                <?php if (isset($item['color']) && $item['color']): ?>
                    <div class="form-group">
                        <label>Color</label>
                        <div class="color-swatch">
                            <div class="swatch-option selected" style="background-color: <?php echo htmlspecialchars($item['color']); ?>"></div>
                        </div>
                        <input type="hidden" name="color" value="<?php echo htmlspecialchars($item['color']); ?>">
                    </div>
                <?php endif; ?>
                
                <?php if (isset($item['size']) && $item['size']): ?>
                    <div class="form-group">
                        <label>Size</label>
                        <div class="size-options">
                            <?php
                            // Display available sizes - in a real implementation, you'd have multiple sizes
                            $sizes = explode(',', $item['size']);
                            foreach ($sizes as $sizeOption):
                                $sizeOption = trim($sizeOption);
                            ?>
                                <div class="size-option <?php echo $sizeOption === trim($item['size']) ? 'selected' : ''; ?>"
                                     data-size="<?php echo htmlspecialchars($sizeOption); ?>">
                                    <?php echo htmlspecialchars($sizeOption); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="size" value="<?php echo htmlspecialchars($item['size']); ?>">
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Quantity</label>
                    <div class="quantity-selector">
                        <button type="button" class="qty-btn minus"><i class="fas fa-minus"></i></button>
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $item['stock']; ?>">
                        <button type="button" class="qty-btn plus"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                
                <div class="product-actions">
                    <button type="submit" class="btn btn-primary btn-lg" <?php echo $item['stock'] <= 0 ? 'disabled' : ''; ?>>
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                    <button type="button" class="btn btn-outline btn-icon">
                        <i class="far fa-heart"></i>
                    </button>
                </div>
            </form>
            
            <div class="product-description">
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
            </div>
            
            <div class="product-features">
                <h3>Features</h3>
                <ul>
                    <?php if (isset($item['brand']) && $item['brand']): ?>
                        <li><strong>Brand:</strong> <?php echo htmlspecialchars($item['brand']); ?></li>
                    <?php endif; ?>
                    <?php if (isset($item['style']) && $item['style']): ?>
                        <li><strong>Style:</strong> <?php echo ucfirst(htmlspecialchars($item['style'])); ?></li>
                    <?php endif; ?>
                    <?php if (isset($item['gender']) && $item['gender']): ?>
                        <li><strong>Gender:</strong> <?php echo ucfirst(htmlspecialchars($item['gender'])); ?></li>
                    <?php endif; ?>
                    <?php if (isset($item['color']) && $item['color']): ?>
                        <li><strong>Color:</strong> <?php echo ucfirst(htmlspecialchars($item['color'])); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="shipping-info">
                <div class="info-item">
                    <i class="fas fa-truck"></i>
                    <span>Free shipping on orders over $50</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-undo"></i>
                    <span>30-day return policy</span>
                </div>
            </div>
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
    
    .stock-status {
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .in-stock {
        color: var(--success-500);
    }
    
    .out-of-stock {
        color: var(--danger-500);
    }
    
    .product-form {
        margin-bottom: 2rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    
    .color-swatch {
        display: flex;
        gap: 0.5rem;
    }
    
    .swatch-option {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid transparent;
    }
    
    .swatch-option.selected {
        border-color: var(--primary-500);
    }
    
    .size-options {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .size-option {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--neutral-300);
        border-radius: var(--radius-md);
        cursor: pointer;
    }
    
    .size-option.selected {
        background-color: var(--primary-500);
        color: white;
        border-color: var(--primary-500);
    }
    
    .quantity-selector {
        display: flex;
        align-items: center;
        max-width: 150px;
    }
    
    .qty-btn {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--neutral-100);
        border: none;
        cursor: pointer;
    }
    
    .quantity-selector input {
        width: 60px;
        height: 40px;
        text-align: center;
        border: 1px solid var(--neutral-300);
        border-left: none;
        border-right: none;
    }
    
    .product-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    .btn-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }
    
    .product-description {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid var(--neutral-200);
    }
    
    .product-description h3 {
        margin-bottom: 1rem;
    }
    
    .product-features {
        margin-top: 2rem;
    }
    
    .product-features h3 {
        margin-bottom: 1rem;
    }
    
    .product-features ul {
        list-style-type: none;
        padding: 0;
    }
    
    .product-features li {
        margin-bottom: 0.5rem;
    }
    
    .shipping-info {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid var(--neutral-200);
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .info-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .info-item i {
        color: var(--primary-500);
    }
    
    @media (max-width: 768px) {
        .product-detail {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>