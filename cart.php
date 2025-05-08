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

// Debugging: Print the cart session data
echo "<pre>";
echo "Cart Session Data:\n";
print_r($cart_items);
echo "</pre>";

// Fetch item details from the database
$items = [];
$total = 0;

if (!empty($cart_items)) {
    $ids = implode(',', array_keys($cart_items));
    $stmt = $conn->query("SELECT * FROM items WHERE id IN ($ids)");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $items = array_map(function($item) use ($cart_items, &$total) {
        $quantity = $cart_items[$item['id']];
        $subtotal = $item['price'] * $quantity;
        $total += $subtotal; // Add the subtotal to the total
        return [
            'id' => $item['id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'stock' => $item['stock'], // Include stock for validation
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }, $items);
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
        $item_id = intval($item_id); // Ensure the item ID is an integer
        $quantity = intval($quantity); // Ensure the quantity is an integer

        // Fetch the stock for the item
        foreach ($items as $item) {
            if ($item['id'] == $item_id) {
                $stock = $item['stock'];
                break;
            }
        }

        // Validate the quantity against the stock
        if ($quantity > $stock) {
            $quantity = $stock; // Cap the quantity at the stock limit
            $error_message = "The quantity for '{$item['name']}' has been capped at the available stock ($stock).";
        }

        if ($quantity > 0) {
            $_SESSION['cart'][$item_id] = $quantity; // Update the quantity
        } else {
            unset($_SESSION['cart'][$item_id]); // Remove the item if quantity is 0
        }
    }
    header("Location: cart.php"); // Redirect to avoid form resubmission
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

    <?php if (!empty($error_message)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

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