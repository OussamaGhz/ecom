<?php
session_start();
include_once 'includes/header.php';
require 'config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Adding a new item
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_item'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $stmt = $conn->prepare("INSERT INTO items (name, description, price, stock) VALUES (:name, :description, :price, :stock)");
    $stmt->execute([
        ':name' => $name,
        ':description' => $description,
        ':price' => $price,
        ':stock' => $stock
    ]);

    header("Location: manage_items.php");
    exit();
}

// Deleting an item
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM items WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header("Location: manage_items.php");
    exit();
}

// Fetching all items
$stmt = $conn->query("SELECT * FROM items");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Items</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Manage Items</h1>

    <!-- Add New Item Form -->
    <form method="POST" action="">
        <h2>Add New Item</h2>
        <input type="text" name="name" placeholder="Item Name" required>
        <textarea name="description" placeholder="Description" required></textarea>
        <input type="number" name="price" placeholder="Price" step="0.01" required>
        <input type="number" name="stock" placeholder="Stock" required>
        <button type="submit" name="add_item">Add Item</button>
    </form>

    <!-- Display Existing Items -->
    <h2>Existing Items</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo $item['id']; ?></td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['stock']; ?></td>
                    <td>
                        <a href="edit_item.php?id=<?php echo $item['id']; ?>">Edit</a>
                        <a href="manage_items.php?delete=<?php echo $item['id']; ?>" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="admin_dashboard.php">Back to Dashboard</a>
</body>
</html>
