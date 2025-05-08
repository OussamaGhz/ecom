<?php
session_start();
include_once 'includes/header.php';                     
require 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$item_id = intval($_GET['id']);

// Fetch the item from the database
$stmt = $conn->prepare("SELECT * FROM items WHERE id = :id");
$stmt->bindParam(':id', $item_id);
$stmt->execute();
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header("Location: index.php");
    exit();
}

$page_title = $item['name'];

// Handle adding item to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // Ensure the cart exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Get the current quantity of the item in the cart (if any)
    $current_cart_quantity = isset($_SESSION['cart'][$item_id]) ? $_SESSION['cart'][$item_id] : 0;

    // Check if the total quantity (current + new) exceeds the stock
    if ($current_cart_quantity + $quantity > $item['stock']) {
        $message = "You cannot add more than the available stock.";
        $message_type = "error";
    } else {
        // Update the cart
        $_SESSION['cart'][$item_id] = $current_cart_quantity + $quantity;
        $message = "Item successfully added to cart!";
        $message_type = "success";
    }
}
?>

<div class="container">
    <div class="breadcrumbs">
        <a href="index.php">Home</a>
        <span class="separator"><i class="fas fa-chevron-right"></i></span>
        <span><?php echo htmlspecialchars($item['name']); ?></span>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="product-detail">
        <div class="product-gallery">
            <?php if ($item['image']): ?>
                <div class="main-image">
                    <img src="assets/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                </div>
            <?php else: ?>
                <div class="main-image">
                    <img src="assets/images/placeholder.png" alt="Product image placeholder">
                </div>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <h1 class="product-title"><?php echo htmlspecialchars($item['name']); ?></h1>
            
            <div class="product-price">$<?php echo number_format($item['price'], 2); ?></div>
            
            <?php if ($item['stock'] > 0): ?>
                <div class="stock-status in-stock">
                    <i class="fas fa-check-circle"></i> In Stock (<?php echo $item['stock']; ?> available)
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
            
            <?php if ($item['stock'] > 0): ?>
                <form method="POST" action="" class="add-to-cart-form">
                    <div class="form-group">
                        <label for="quantity" class="form-label">Quantity:</label>
                        <div class="quantity-input">
                            <button type="button" class="quantity-decrease"><i class="fas fa-minus"></i></button>
                            <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $item['stock']; ?>" class="form-control">
                            <button type="button" class="quantity-increase"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    
                    <div class="product-actions">
                        <button type="submit" class="btn btn-primary btn-lg add-to-cart">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        
                        <a href="#" class="btn btn-outline btn-lg">
                            <i class="far fa-heart"></i> Add to Wishlist
                        </a>
                    </div>
                </form>
            <?php endif; ?>
            
            <div class="product-meta">
                <div class="meta-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Secure payment</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-truck"></i>
                    <span>Fast delivery</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-exchange-alt"></i>
                    <span>30-day returns</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>