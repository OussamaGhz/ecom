<?php
session_start();
require 'config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Set page title
$page_title = "Manage Products";

// Adding a new item
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_item'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'] ?? '';

    $stmt = $conn->prepare("INSERT INTO items (name, description, price, stock, category) VALUES (:name, :description, :price, :stock, :category)");
    $stmt->execute([
        ':name' => $name,
        ':description' => $description,
        ':price' => $price,
        ':stock' => $stock,
        ':category' => $category
    ]);

    header("Location: manage_items.php?success=1");
    exit();
}

// Deleting an item
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM items WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header("Location: manage_items.php?deleted=1");
    exit();
}

// Fetching all items
$stmt = $conn->query("SELECT * FROM items ORDER BY id DESC");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include header after setting variables
include_once 'includes/header.php';
?>

<div class="container">
    <div class="dashboard-header">
        <h1 class="page-title">Product Management</h1>
        <div class="dashboard-actions">
            <a href="admin_dashboard.php" class="btn btn-sm btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Product has been added successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Product has been deleted successfully.
        </div>
    <?php endif; ?>

    <div class="admin-card">
        <h2><i class="fas fa-plus-circle"></i> Add New Product</h2>
        
        <form method="POST" action="" class="admin-form">
            <div class="form-row">
                <div class="admin-form-group">
                    <label for="name" class="admin-form-label">Product Name</label>
                    <input type="text" id="name" name="name" class="admin-form-control" required>
                </div>
                
                <div class="admin-form-group">
                    <label for="category" class="admin-form-label">Category</label>
                    <select id="category" name="category" class="admin-form-control">
                        <option value="">Select Category</option>
                        <option value="electronics">Electronics</option>
                        <option value="clothing">Clothing</option>
                        <option value="home">Home & Garden</option>
                        <option value="books">Books</option>
                    </select>
                </div>
            </div>
            
            <div class="admin-form-group">
                <label for="description" class="admin-form-label">Description</label>
                <textarea id="description" name="description" class="admin-form-control" required></textarea>
            </div>
            
            <div class="form-row">
                <div class="admin-form-group">
                    <label for="price" class="admin-form-label">Price ($)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" class="admin-form-control" required>
                </div>
                
                <div class="admin-form-group">
                    <label for="stock" class="admin-form-label">Stock</label>
                    <input type="number" id="stock" name="stock" min="0" class="admin-form-control" required>
                </div>
            </div>
            
            <button type="submit" name="add_item" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Product
            </button>
        </form>
    </div>

    <div class="admin-card">
        <h2><i class="fas fa-box-open"></i> Product Inventory</h2>
        
        <?php if (empty($items)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>No Products Found</h3>
                <p>There are no products in the inventory yet. Add your first product using the form above.</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($items as $item): ?>
                    <div class="product-card admin-product">
                        <div class="product-image">
                            <?php if (isset($item['image']) && $item['image']): ?>
                                <img src="assets/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php else: ?>
                                <img src="assets/images/placeholder.png" alt="Product image placeholder">
                            <?php endif; ?>
                        </div>
                        <div class="product-details">
                            <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <div class="product-price">$<?php echo number_format($item['price'], 2); ?></div>
                            <p class="product-stock">
                                <?php if ($item['stock'] > 10): ?>
                                    <span class="text-success"><i class="fas fa-check-circle"></i> In Stock (<?php echo $item['stock']; ?>)</span>
                                <?php elseif ($item['stock'] > 0): ?>
                                    <span class="text-warning"><i class="fas fa-exclamation-circle"></i> Low Stock (<?php echo $item['stock']; ?>)</span>
                                <?php else: ?>
                                    <span class="text-danger"><i class="fas fa-times-circle"></i> Out of Stock</span>
                                <?php endif; ?>
                            </p>
                            <div class="product-actions">
                                <a href="edit_item.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="manage_items.php?delete=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline delete-btn" 
                                onclick="return confirm('Are you sure you want to delete this product?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>