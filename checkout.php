<?php
session_start();
require 'config/database.php';
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$page_title = "Checkout";

// Redirect if cart is empty
if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

// Fetch item details from the database
$items = [];
$total = 0;

if (!empty($cart_items)) {
    $ids = implode(',', array_keys($cart_items));
    $stmt = $conn->query("SELECT * FROM items WHERE id IN ($ids)");
    $fetched_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $items = array_map(function($item) use ($cart_items, &$total) {
        $quantity = $cart_items[$item['id']];
        $subtotal = $item['price'] * $quantity;
        $total += $subtotal;
        return [
            'id' => $item['id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'stock' => $item['stock']
        ];
    }, $fetched_items);
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($items)) {
    try {
        $conn->beginTransaction();

        // Insert the order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price) VALUES (:user_id, :total_price)");
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':total_price' => $total
        ]);

        $order_id = $conn->lastInsertId();

        // Insert each item into order_items table and update stock
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, item_id, quantity, price) VALUES (:order_id, :item_id, :quantity, :price)");
        $updateStockStmt = $conn->prepare("UPDATE items SET stock = stock - :quantity WHERE id = :id AND stock >= :quantity");

        foreach ($items as $item) {
            $stmt->execute([
                ':order_id' => $order_id,
                ':item_id' => $item['id'],
                ':quantity' => $item['quantity'],
                ':price' => $item['price']
            ]);

            $updateStockStmt->execute([
                ':quantity' => $item['quantity'],
                ':id' => $item['id']
            ]);
        }

        $conn->commit();
        
        // Clear the cart
        unset($_SESSION['cart']);

        // Redirect to confirmation page
        header("Location: confirmation.php?order_id=" . $order_id);
        exit();
        
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Failed to place order: " . $e->getMessage();
    }
}

// Fetch user information
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h1 class="page-title">Checkout</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <div class="checkout-wrapper">
        <div class="checkout-form">
            <form method="POST" action="" data-validate="true">
                <div class="form-section">
                    <h2>
                        <i class="fas fa-user"></i>
                        Contact Information
                    </h2>
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>
                        <i class="fas fa-shipping-fast"></i>
                        Shipping Address
                    </h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" id="address" name="address" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city" class="form-label">City</label>
                            <input type="text" id="city" name="city" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="zip" class="form-label">ZIP Code</label>
                            <input type="text" id="zip" name="zip" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>
                        <i class="fas fa-credit-card"></i>
                        Payment Method
                    </h2>
                    <div class="payment-options">
                        <div class="payment-option">
                            <input type="radio" name="payment_method" id="credit_card" value="credit_card" checked>
                            <label for="credit_card">
                                <i class="far fa-credit-card"></i>
                                Credit Card
                            </label>
                        </div>
                        <div class="payment-option">
                            <input type="radio" name="payment_method" id="paypal" value="paypal">
                            <label for="paypal">
                                <i class="fab fa-paypal"></i>
                                PayPal
                            </label>
                        </div>
                    </div>
                    
                    <div id="credit_card_form">
                        <div class="form-group">
                            <label for="card_number" class="form-label">Card Number</label>
                            <input type="text" id="card_number" name="card_number" class="form-control" placeholder="XXXX XXXX XXXX XXXX">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="expiry" class="form-label">Expiry Date</label>
                                <input type="text" id="expiry" name="expiry" class="form-control" placeholder="MM/YY">
                            </div>
                            <div class="form-group">
                                <label for="cvv" class="form-label">CVV</label>
                                <input type="text" id="cvv" name="cvv" class="form-control" placeholder="XXX">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="checkout-actions">
                    <a href="cart.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Return to Cart
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-lock"></i> Place Order
                    </button>
                </div>
            </form>
        </div>
        
        <div class="checkout-summary">
            <h2>Order Summary</h2>
            
            <div class="summary-items">
                <?php foreach ($items as $item): ?>
                    <div class="summary-item">
                        <div class="item-info">
                            <span class="item-quantity"><?php echo $item['quantity']; ?> ×</span>
                            <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                        </div>
                        <div class="item-price">
                            $<?php echo number_format($item['subtotal'], 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="summary-totals">
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
            </div>
            
            <div class="secure-checkout">
                <i class="fas fa-shield-alt"></i> 
                Your payment information is secure
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>