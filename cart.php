<?php
session_start();
require 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Fetch item details from the database
$items = [];
$total = 0;

if (!empty($cart_items)) {
    $ids = implode(',', array_keys($cart_items));
    $stmt = $conn->query("SELECT * FROM items WHERE id IN ($ids)");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as &$item) {
        $quantity = $cart_items[$item['id']];
        $item['quantity'] = $quantity;
        $item['subtotal'] = $item['price'] * $quantity;
        $total += $item['subtotal'];
    }
}

// Handle deleting an item
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    unset($_SESSION['cart'][$delete_id]);
    header("Location: cart.php");
    exit();
}

// Handle updating quantities
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    foreach ($_POST['quantities'] as $item_id => $quantity) {
        if ($quantity > 0) {
            $_SESSION['cart'][$item_id] = $quantity;
        } else {
            unset($_SESSION['cart'][$item_id]);
        }
    }
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cart - E-commerce Shop</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Your Cart</h1>

    <?php if (empty($items)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <form method="POST" action="">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>
                                <input type="number" name="quantities[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>">
                            </td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                            <td><a href="cart.php?delete=<?php echo $item['id']; ?>">Delete</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p><strong>Total: $<?php echo number_format($total, 2); ?></strong></p>
            <button type="submit" name="update">Update Cart</button>
        </form>

        <a href="checkout.php">Proceed to Checkout</a>
    <?php endif; ?>

    <a href="index.php">Back to Home</a>
</body>
</html>
