<?php
session_start();
require 'config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: manage_items.php");
    exit();
}

// Fetch the item details
$stmt = $conn->prepare("SELECT * FROM items WHERE id = :id");
$stmt->execute([':id' => $id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header("Location: manage_items.php");
    exit();
}

// Update item
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $stmt = $conn->prepare("UPDATE items SET name = :name, description = :description, price = :price, stock = :stock WHERE id = :id");
    $stmt->execute([
        ':name' => $name,
        ':description' => $description,
        ':price' => $price,
        ':stock' => $stock,
        ':id' => $id
    ]);

    header("Location: manage_items.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Item</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Edit Item</h1>
    <form method="POST" action="">
        <input type="text" name="name" value="<?php echo htmlspecialchars($item['name']); ?>" required>
        <textarea name="description" required><?php echo htmlspecialchars($item['description']); ?></textarea>
        <input type="number" name="price" value="<?php echo $item['price']; ?>" step="0.01" required>
        <input type="number" name="stock" value="<?php echo $item['stock']; ?>" required>
        <button type="submit">Update Item</button>
    </form>

    <a href="manage_items.php">Back to Items</a>
</body>
</html>
