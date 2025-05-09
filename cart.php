<?php
session_start();
require 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=cart.php");
    exit();
}

$page_title = "Your Cart";
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Fetch item details from the database
$items = [];
$total = 0;

if (!empty($cart_items)) {
    try {
        $ids = implode(',', array_keys($cart_items));
        $stmt = $conn->query("SELECT * FROM items WHERE id IN ($ids)");
        $fetched_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Map items with their quantities
        foreach ($fetched_items as $item) {
            $quantity = $cart_items[$item['id']];
            $subtotal = $item['price'] * $quantity;
            $total += $subtotal;
            
            $items[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'price' => $item['price'],
                'image' => $item['image'],
                'brand' => $item['brand'] ?? '',
                'style' => $item['style'] ?? '',
                'color' => $item['color'] ?? '',
                'stock' => $item['stock'],
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error fetching cart items: " . $e->getMessage();
    }
}

// Handle deleting an item
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    if (isset($_SESSION['cart'][$delete_id])) {
        unset($_SESSION['cart'][$delete_id]);
        $_SESSION['success'] = "Item removed from cart.";
    }
    header("Location: cart.php");
    exit();
}

// Handle updating quantities
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $item_id => $quantity) {
        $item_id = intval($item_id);
        $quantity = intval($quantity);
        
        // Find the stock for this item
        $stock = 0;
        foreach ($items as $item) {
            if ($item['id'] == $item_id) {
                $stock = $item['stock'];
                break;
            }
        }
        
        // Validate the quantity against the stock
        if ($quantity > $stock) {
            $quantity = $stock;
            $_SESSION['warning'] = "Some quantities were adjusted to match available stock.";
        }
        
        if ($quantity > 0) {
            $_SESSION['cart'][$item_id] = $quantity;
        } else {
            unset($_SESSION['cart'][$item_id]);
        }
    }
    
    $_SESSION['success'] = "Cart updated successfully.";
    header("Location: cart.php");
    exit();
}

// Include header after setting page title
include 'includes/header.php';
?>

<div class="container">
    <h1 class="page-title">Shopping Cart</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['warning'])): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo $_SESSION['warning']; unset($_SESSION['warning']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-times-circle"></i>
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <div class="empty-cart">
            <div class="empty-cart-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h2>Your cart is empty</h2>
            <p>Looks like you haven't added any shoes to your cart yet.</p>
            <a href="index.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="cart-wrapper">
            <div class="cart-items">
                <form method="POST" action="">
                    <?php foreach ($items as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <?php if ($item['image']): ?>
                                    <img src="assets/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <?php else: ?>
                                    <img src="assets/images/placeholder.png" alt="Product image placeholder">
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-details">
                                <h3 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                                
                                <div class="item-meta">
                                    <?php if ($item['brand']): ?>
                                        <span class="meta-item brand"><?php echo htmlspecialchars($item['brand']); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if ($item['style']): ?>
                                        <span class="meta-item style"><?php echo ucfirst(htmlspecialchars($item['style'])); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if ($item['color']): ?>
                                        <div class="meta-item color">
                                            <span class="color-dot" style="background-color: <?php echo htmlspecialchars($item['color']); ?>"></span>
                                            <span><?php echo ucfirst(htmlspecialchars($item['color'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="item-price">
                                    $<?php echo number_format($item['price'], 2); ?>
                                </div>
                                
                                <div class="item-actions">
                                    <a href="cart.php?delete=<?php echo $item['id']; ?>" class="remove-item">
                                        <i class="fas fa-trash-alt"></i> Remove
                                    </a>
                                </div>
                            </div>
                            
                            <div class="item-quantity">
                                <div class="quantity-label">Quantity:</div>
                                <div class="quantity-selector">
                                    <button type="button" class="qty-btn minus"><i class="fas fa-minus"></i></button>
                                    <input type="number" name="quantities[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" class="qty-input">
                                    <button type="button" class="qty-btn plus"><i class="fas fa-plus"></i></button>
                                </div>
                                <div class="stock-info">
                                    <?php echo $item['stock']; ?> available
                                </div>
                            </div>
                            
                            <div class="item-subtotal">
                                <div class="subtotal-label">Subtotal:</div>
                                <div class="subtotal-value">$<?php echo number_format($item['subtotal'], 2); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="cart-actions">
                        <button type="submit" name="update_cart" class="btn btn-outline">
                            <i class="fas fa-sync-alt"></i> Update Cart
                        </button>
                        <a href="index.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                    </div>
                </form>
            </div>
            
            <div class="cart-summary">
                <h2>Order Summary</h2>
                
                <div class="summary-line">
                    <span>Subtotal</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
                
                <div class="summary-line">
                    <span>Shipping</span>
                    <span>Free</span>
                </div>
                
                <div class="summary-line total">
                    <span>Total</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
                
                <a href="checkout.php" class="btn btn-primary btn-block checkout-btn">
                    <i class="fas fa-lock"></i> Proceed to Checkout
                </a>
                
                <div class="secure-checkout">
                    <i class="fas fa-shield-alt"></i> 
                    Secure checkout
                </div>
                
                <div class="payment-methods">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-amex"></i>
                    <i class="fab fa-cc-paypal"></i>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Cart page specific styles */
.empty-cart {
    text-align: center;
    padding: var(--space-10) var(--space-6);
    background-color: white;
    border-radius: var(--radius-lg);
    box-shadow: 0 2px 10px var(--shadow);
    margin: var(--space-8) 0;
}

.empty-cart-icon {
    font-size: 4rem;
    color: var(--neutral-400);
    margin-bottom: var(--space-4);
}

.empty-cart h2 {
    margin-bottom: var(--space-2);
    color: var(--neutral-700);
}

.empty-cart p {
    margin-bottom: var(--space-6);
    color: var(--neutral-600);
}

.cart-wrapper {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--space-6);
}

.cart-items {
    background-color: white;
    border-radius: var(--radius-lg);
    box-shadow: 0 2px 10px var(--shadow);
    padding: var(--space-5);
}

.cart-item {
    display: grid;
    grid-template-columns: auto 1fr auto auto;
    gap: var(--space-4);
    padding: var(--space-4) 0;
    border-bottom: 1px solid var(--neutral-200);
    align-items: center;
}

.cart-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 100px;
    height: 100px;
    border-radius: var(--radius-md);
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.item-name {
    font-size: var(--text-lg);
    font-weight: var(--font-medium);
    margin: 0 0 var(--space-1) 0;
}

.item-meta {
    display: flex;
    gap: var(--space-3);
    flex-wrap: wrap;
    margin-bottom: var(--space-2);
}

.meta-item {
    font-size: 0.85rem;
    padding: 2px 8px;
    background-color: var(--neutral-100);
    border-radius: var(--radius-full);
    color: var(--neutral-700);
}

.meta-item.color {
    display: flex;
    align-items: center;
    gap: 5px;
}

.color-dot {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 1px solid var(--neutral-300);
}

.item-price {
    font-weight: var(--font-medium);
    color: var(--neutral-700);
    margin-bottom: var(--space-2);
}

.item-actions {
    margin-top: var(--space-2);
}

.remove-item {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1);
    color: var(--neutral-600);
    font-size: var(--text-sm);
    padding: 3px 8px;
    border-radius: var(--radius-md);
    background-color: var(--neutral-100);
    transition: all 0.2s ease;
}

.remove-item:hover {
    color: var(--error);
    background-color: var(--error-50);
}

.item-quantity {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-2);
}

.quantity-label {
    font-size: var(--text-sm);
    color: var(--neutral-600);
}

.quantity-selector {
    display: flex;
    align-items: center;
    border: 1px solid var(--neutral-300);
    border-radius: var(--radius-md);
}

.qty-btn {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: none;
    border: none;
    cursor: pointer;
    color: var(--neutral-700);
}

.qty-btn:hover {
    background-color: var(--neutral-100);
}

.qty-input {
    width: 40px;
    height: 36px;
    text-align: center;
    border: none;
    border-left: 1px solid var(--neutral-300);
    border-right: 1px solid var(--neutral-300);
}

.stock-info {
    font-size: var(--text-xs);
    color: var(--neutral-500);
}

.item-subtotal {
    text-align: right;
    min-width: 120px;
}

.subtotal-label {
    font-size: var(--text-sm);
    color: var(--neutral-600);
    margin-bottom: var(--space-1);
}

.subtotal-value {
    font-size: var(--text-lg);
    font-weight: var(--font-bold);
    color: var(--primary-600);
}

.cart-actions {
    padding: var(--space-5) 0 0;
    display: flex;
    justify-content: space-between;
}

.cart-summary {
    background-color: white;
    border-radius: var(--radius-lg);
    box-shadow: 0 2px 10px var(--shadow);
    padding: var(--space-5);
    position: sticky;
    top: 20px;
    height: fit-content;
}

.cart-summary h2 {
    margin-bottom: var(--space-5);
    font-size: var(--text-xl);
    color: var(--neutral-800);
    padding-bottom: var(--space-3);
    border-bottom: 1px solid var(--neutral-200);
}

.summary-line {
    display: flex;
    justify-content: space-between;
    margin-bottom: var(--space-3);
    color: var(--neutral-700);
}

.summary-line.total {
    font-size: var(--text-xl);
    font-weight: var(--font-bold);
    color: var(--neutral-800);
    padding-top: var(--space-3);
    border-top: 2px solid var(--neutral-300);
    margin-top: var(--space-4);
}

.checkout-btn {
    margin-top: var(--space-5);
    width: 100%;
    padding: var(--space-3);
    font-size: var(--text-md);
}

.secure-checkout {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2);
    margin-top: var(--space-4);
    font-size: var(--text-sm);
    color: var(--neutral-600);
}

.payment-methods {
    display: flex;
    justify-content: center;
    gap: var(--space-3);
    margin-top: var(--space-3);
    font-size: 1.5rem;
    color: var(--neutral-500);
}

/* Responsive styles */
@media (max-width: 992px) {
    .cart-wrapper {
        grid-template-columns: 1fr;
    }
    
    .cart-summary {
        position: static;
        margin-top: var(--space-4);
    }
}

@media (max-width: 768px) {
    .cart-item {
        grid-template-columns: 80px 1fr;
        grid-template-areas:
            "image details"
            "quantity quantity"
            "subtotal subtotal";
        row-gap: var(--space-4);
    }
    
    .item-image {
        grid-area: image;
        width: 80px;
        height: 80px;
    }
    
    .item-details {
        grid-area: details;
    }
    
    .item-quantity {
        grid-area: quantity;
        flex-direction: row;
        justify-content: space-between;
    }
    
    .item-subtotal {
        grid-area: subtotal;
        text-align: left;
        display: flex;
        justify-content: space-between;
        border-top: 1px dashed var(--neutral-200);
        padding-top: var(--space-3);
    }
}
</style>

<script>
// Quantity buttons functionality
document.addEventListener('DOMContentLoaded', function() {
    const minusButtons = document.querySelectorAll('.qty-btn.minus');
    const plusButtons = document.querySelectorAll('.qty-btn.plus');
    
    minusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.nextElementSibling;
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
            }
        });
    });
    
    plusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const currentValue = parseInt(input.value);
            const maxValue = parseInt(input.getAttribute('max'));
            if (currentValue < maxValue) {
                input.value = currentValue + 1;
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>