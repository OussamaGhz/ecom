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

// Set page title
$page_title = "Edit Product";

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
    $category = $_POST['category'] ?? '';

    $stmt = $conn->prepare("UPDATE items SET name = :name, description = :description, price = :price, stock = :stock, category = :category WHERE id = :id");
    $stmt->execute([
        ':name' => $name,
        ':description' => $description,
        ':price' => $price,
        ':stock' => $stock,
        ':category' => $category,
        ':id' => $id
    ]);

    header("Location: manage_items.php?updated=1");
    exit();
}

// Include header after setting variables
include_once 'includes/header.php';
?>

<div class="container">
    <div class="dashboard-header">
        <h1 class="page-title">Edit Product</h1>
        <div class="dashboard-actions">
            <a href="manage_items.php" class="btn btn-sm btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>

    <div class="admin-card">
        <h2><i class="fas fa-edit"></i> Edit Product Details</h2>
        
        <form method="POST" action="" class="admin-form">
            <div class="form-row">
                <div class="admin-form-group">
                    <label for="name" class="admin-form-label">Product Name</label>
                    <input type="text" id="name" name="name" class="admin-form-control" value="<?php echo htmlspecialchars($item['name']); ?>" required>
                </div>
                
                <div class="admin-form-group">
                    <label for="category" class="admin-form-label">Category</label>
                    <select id="category" name="category" class="admin-form-control">
                        <option value="">Select Category</option>
                        <option value="electronics" <?php echo isset($item['category']) && $item['category'] === 'electronics' ? 'selected' : ''; ?>>Electronics</option>
                        <option value="clothing" <?php echo isset($item['category']) && $item['category'] === 'clothing' ? 'selected' : ''; ?>>Clothing</option>
                        <option value="home" <?php echo isset($item['category']) && $item['category'] === 'home' ? 'selected' : ''; ?>>Home & Garden</option>
                        <option value="books" <?php echo isset($item['category']) && $item['category'] === 'books' ? 'selected' : ''; ?>>Books</option>
                    </select>
                </div>
            </div>
            
            <div class="admin-form-group">
                <label for="description" class="admin-form-label">Description</label>
                <textarea id="description" name="description" class="admin-form-control" required><?php echo htmlspecialchars($item['description']); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="admin-form-group">
                    <label for="price" class="admin-form-label">Price ($)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" class="admin-form-control" value="<?php echo $item['price']; ?>" required>
                </div>
                
                <div class="admin-form-group">
                    <label for="stock" class="admin-form-label">Stock</label>
                    <input type="number" id="stock" name="stock" min="0" class="admin-form-control" value="<?php echo $item['stock']; ?>" required>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Product
                </button>
                <a href="manage_items.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>

    <div class="admin-card">
        <h2><i class="fas fa-eye"></i> Product Preview</h2>
        
        <div class="product-detail">
            <div class="product-gallery">
                <?php if (isset($item['image']) && $item['image']): ?>
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
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>