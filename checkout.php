<?php
session_start();
require 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total = 0;

// Fetch item details from the database
$items = [];

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
        echo "Failed to place order: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout - E-commerce Shop</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Checkout</h1>

    <?php if (empty($items)): ?>
        <p>Your cart is empty. <a href="index.php">Back to Home</a></p>
    <?php else: ?>
        <form method="POST" action="">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p><strong>Total: $<?php echo number_format($total, 2); ?></strong></p>
            <button type="submit">Place Order</button>
        </form>
    <?php endif; ?>

    <a href="cart.php">Back to Cart</a>
</body>
</html>
