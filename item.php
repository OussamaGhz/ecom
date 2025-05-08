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
    echo "Item not found.";
    exit();
}

// Prevent adding items with stock = 0
if ($item['stock'] <= 0) {
    echo "This item is out of stock.";
    exit();
}

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
        $message = "You cannot add more than the available stock. Current stock: " . $item['stock'];
    } else {
        // Update the cart
        $_SESSION['cart'][$item_id] = $current_cart_quantity + $quantity;
        $message = "Item successfully added to cart!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($item['name']); ?> - E-commerce Shop</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1><?php echo htmlspecialchars($item['name']); ?></h1>
    
    <?php if ($item['image']): ?>
        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width:300px; height:auto;">
    <?php endif; ?>

    <p><?php echo htmlspecialchars($item['description']); ?></p>
    <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
    <p>Available Stock: <?php echo $item['stock']; ?></p>

    <form method="POST" action="">
        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $item['stock']; ?>">
        <button type="submit">Add to Cart</button>
    </form>

    <?php if (isset($message)): ?>
        <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <a href="index.php">Back to Home</a> | <a href="cart.php">Go to Cart</a>
</body>
</html>