<?php
session_start();
require 'config/database.php';

$shoe_styles = ['athletic', 'casual', 'formal', 'boots', 'sandals', 'heels', 'sneakers'];
    $shoe_brands = ['Nike', 'Adidas', 'Puma', 'Reebok', 'New Balance', 'Converse', 'Vans', 'Dr. Martens', 'Clarks', 'Timberland'];
    $shoe_colors = ['black', 'white', 'red', 'blue', 'green', 'brown', 'gray', 'beige', 'multicolor'];
    $shoe_genders = ['men', 'women', 'unisex', 'kids'];

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Set page title
$page_title = "Manage Shoes";

// Filter handling
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Adding a new shoe
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_item'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $brand = $_POST['brand'] ?? '';
    $style = $_POST['style'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $color = $_POST['color'] ?? '';
    
    // Handle sizes as a comma-separated string
    $sizes = isset($_POST['sizes']) ? implode(',', $_POST['sizes']) : '';
    
    // Initialize image path
    $image = '';
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $filename = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_size = $_FILES['image']['size'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Check file extension
        if (in_array($ext, $allowed)) {
            // Check file size - 2MB max
            if ($file_size <= 2097152) {
                // Create directory if it doesn't exist
                $upload_dir = 'assets/images/products/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Generate unique filename
                $new_filename = 'shoe_' . uniqid() . '.' . $ext;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $image = 'images/products/' . $new_filename;
                } else {
                    $_SESSION['error'] = "Failed to upload image. Please try again.";
                }
            } else {
                $_SESSION['error'] = "Image file is too large. Maximum size is 2MB.";
            }
        } else {
            $_SESSION['error'] = "Invalid file type. Allowed types: JPG, PNG, WEBP, GIF.";
        }
    }

    $stmt = $conn->prepare("INSERT INTO items (name, description, price, stock, brand, style, gender, size, color, image) 
                           VALUES (:name, :description, :price, :stock, :brand, :style, :gender, :size, :color, :image)");
    $stmt->execute([
        ':name' => $name,
        ':description' => $description,
        ':price' => $price,
        ':stock' => $stock,
        ':brand' => $brand,
        ':style' => $style,
        ':gender' => $gender,
        ':size' => $sizes,
        ':color' => $color,
        ':image' => $image
    ]);

    header("Location: manage_items.php?success=1");
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']); // Ensure ID is an integer
    
    try {
        // First check if the item exists
        $checkStmt = $conn->prepare("SELECT id FROM items WHERE id = :id");
        $checkStmt->execute([':id' => $id]);
        
        if ($checkStmt->rowCount() === 0) {
            // Item doesn't exist
            $_SESSION['error'] = "The item you're trying to delete doesn't exist.";
            header("Location: manage_items.php");
            exit();
        }
        
        // Check if item is referenced in order_items table
        $orderCheckStmt = $conn->prepare("SELECT COUNT(*) FROM order_items WHERE item_id = :id");
        $orderCheckStmt->execute([':id' => $id]);
        $orderCount = $orderCheckStmt->fetchColumn();
        
        if ($orderCount > 0) {
            // Check if active column exists in the items table
            $columnCheckStmt = $conn->query("SHOW COLUMNS FROM items LIKE 'active'");
            
            if ($columnCheckStmt->rowCount() == 0) {
                // Add active column if it doesn't exist
                $conn->exec("ALTER TABLE items ADD COLUMN active TINYINT(1) DEFAULT 1");
            }
            
            // Item is referenced in orders - don't delete, just mark as out of stock and inactive
            $updateStmt = $conn->prepare("UPDATE items SET stock = 0, active = 0 WHERE id = :id");
            $updateStmt->execute([':id' => $id]);
            
            $_SESSION['warning'] = "This shoe is referenced in orders. It has been marked as inactive/out of stock rather than deleted.";
            header("Location: manage_items.php");
            exit();
        } else {
            // Safe to delete - no order references
            $deleteStmt = $conn->prepare("DELETE FROM items WHERE id = :id");
            $deleteStmt->execute([':id' => $id]);
            
            $_SESSION['success'] = "Shoe has been deleted successfully.";
            header("Location: manage_items.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: manage_items.php");
        exit();
    }
}

// Build query based on filters
$query = "SELECT * FROM items";
$where_clauses = [];
$params = [];

if ($filter === 'low_stock') {
    $where_clauses[] = "stock <= 5 AND stock > 0";
} elseif ($filter === 'out_of_stock') {
    $where_clauses[] = "stock = 0";
}

if ($category) {
    $where_clauses[] = "style = :category";
    $params[':category'] = $category;
}

if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

$query .= " ORDER BY id DESC";

// Fetching all items
$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include header after setting variables
include_once 'includes/header.php';
?>

<div class="container">
    <div class="dashboard-header">
        <h1 class="page-title">
            <?php if ($filter === 'low_stock'): ?>
                Low Stock Shoes
            <?php elseif ($filter === 'out_of_stock'): ?>
                Out of Stock Shoes
            <?php elseif ($category): ?>
                <?php echo ucfirst($category); ?> Shoes
            <?php else: ?>
                Shoe Inventory Management
            <?php endif; ?>
        </h1>
        <div class="dashboard-actions">
            <a href="admin_dashboard.php" class="btn btn-sm btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['warning'])): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['warning']; unset($_SESSION['warning']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-times-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Shoe has been added successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Shoe has been deleted successfully.
        </div>
    <?php endif; ?>

    

    <div class="admin-card">
    <h2><i class="fas fa-plus-circle"></i> Add New Shoe</h2>
    
    <form method="POST" action="" class="admin-form" enctype="multipart/form-data">
        <div class="form-row">
            <div class="admin-form-group">
                <label for="name" class="admin-form-label">Shoe Name</label>
                <input type="text" id="name" name="name" class="admin-form-control" required>
            </div>
            
            <div class="admin-form-group">
                <label for="brand" class="admin-form-label">Brand</label>
                <select id="brand" name="brand" class="admin-form-control">
                    <option value="">Select Brand</option>
                    <?php foreach ($shoe_brands as $brand): ?>
                        <option value="<?php echo $brand; ?>"><?php echo $brand; ?></option>
                    <?php endforeach; ?>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="admin-form-group">
                <label for="style" class="admin-form-label">Style</label>
                <select id="style" name="style" class="admin-form-control">
                    <option value="">Select Style</option>
                    <?php foreach ($shoe_styles as $style): ?>
                        <option value="<?php echo $style; ?>"><?php echo ucfirst($style); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="admin-form-group">
                <label for="gender" class="admin-form-label">Gender</label>
                <select id="gender" name="gender" class="admin-form-control">
                    <option value="">Select Gender</option>
                    <?php foreach ($shoe_genders as $gender): ?>
                        <option value="<?php echo $gender; ?>"><?php echo ucfirst($gender); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="admin-form-group">
                <label for="color" class="admin-form-label">Color</label>
                <select id="color" name="color" class="admin-form-control">
                    <option value="">Select Color</option>
                    <?php foreach ($shoe_colors as $color): ?>
                        <option value="<?php echo $color; ?>"><?php echo ucfirst($color); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="admin-form-group">
            <label class="admin-form-label">Available Sizes</label>
            <div class="size-options">
                <?php 
                $size_range = range(5, 13);
                foreach ($size_range as $size): 
                ?>
                    <label class="size-option">
                        <input type="checkbox" name="sizes[]" value="<?php echo $size; ?>">
                        <?php echo $size; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="admin-form-group">
            <label for="description" class="admin-form-label">Description</label>
            <textarea id="description" name="description" class="admin-form-control" rows="4" required></textarea>
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
        
        <div class="admin-form-group">
            <label for="image" class="admin-form-label">Shoe Image</label>
            <input type="file" id="image" name="image" class="admin-form-control" accept="image/jpeg,image/png,image/webp">
            <small class="form-text">Recommended size: 800x800px. Max file size: 2MB.</small>
        </div>
        
        <button type="submit" name="add_item" class="btn btn-primary" style="background-color: var(--primary-500);">
            <i class="fas fa-plus"></i> Add Shoe
        </button>
    </form>
</div>
<!-- Filter navigation -->
<div class="filter-navigation">
        <a href="manage_items.php" class="filter-link <?php echo (!$filter && !$category) ? 'active' : ''; ?>">All Shoes</a>
        <a href="manage_items.php?filter=low_stock" class="filter-link <?php echo $filter === 'low_stock' ? 'active' : ''; ?>">Low Stock</a>
        <a href="manage_items.php?filter=out_of_stock" class="filter-link <?php echo $filter === 'out_of_stock' ? 'active' : ''; ?>">Out of Stock</a>
        <div class="filter-divider"></div>
        <a href="manage_items.php?category=athletic" class="filter-link <?php echo $category === 'athletic' ? 'active' : ''; ?>">Athletic</a>
        <a href="manage_items.php?category=casual" class="filter-link <?php echo $category === 'casual' ? 'active' : ''; ?>">Casual</a>
        <a href="manage_items.php?category=formal" class="filter-link <?php echo $category === 'formal' ? 'active' : ''; ?>">Formal</a>
        <a href="manage_items.php?category=boots" class="filter-link <?php echo $category === 'boots' ? 'active' : ''; ?>">Boots</a>
    </div>
    <div class="admin-card">
        <h2><i class="fas fa-shoe-prints"></i> Shoe Inventory</h2>
        
        <?php if (empty($items)): ?>
            <div class="empty-state">
                <i class="fas fa-shoe-prints"></i>
                <h3>No Shoes Found</h3>
                <?php if ($filter || $category): ?>
                    <p>No shoes match the selected filter. <a href="manage_items.php">View all shoes</a>.</p>
                <?php else: ?>
                    <p>There are no shoes in the inventory yet. Add your first shoe using the form above.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                            <div class="product-thumbnail">
        <img src="image.php?id=<?php echo $item['id']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
    </div>
                            </td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo isset($item['brand']) ? htmlspecialchars($item['brand']) : '-'; ?></td>
                            <td><?php echo isset($item['style']) ? ucfirst(htmlspecialchars($item['style'])) : '-'; ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <?php if ($item['stock'] <= 5 && $item['stock'] > 0): ?>
                                    <span class="badge badge-warning"><?php echo $item['stock']; ?></span>
                                <?php elseif ($item['stock'] == 0): ?>
                                    <span class="badge badge-danger">Out of Stock</span>
                                <?php else: ?>
                                    <span class="badge badge-success"><?php echo $item['stock']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="admin-actions">
                                    <a href="edit_item.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="manage_items.php?delete=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline delete-btn" 
                                       onclick="return confirm('Are you sure you want to delete this shoe?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
    .filter-navigation {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 2rem;
    }
    
    .filter-link {
        padding: 0.5rem 1rem;
        border-radius: 100px;
        background-color: var(--neutral-100);
        color: var(--neutral-700);
        text-decoration: none;
        transition: all 0.2s ease;
    }
    
    .filter-link:hover {
        background-color: var(--neutral-200);
    }
    
    .filter-link.active {
        background-color: var(--primary-500);
        color: white;
    }
    
    .filter-divider {
        width: 1px;
        background-color: var(--neutral-300);
        margin: 0 0.5rem;
    }
    
    .product-thumbnail {
        width: 60px;
        height: 60px;
        overflow: hidden;
        border-radius: var(--radius-sm);
        background-color: var(--neutral-100);
    }
    
    .product-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 100px;
        font-size: 0.75rem;
        font-weight: var(--font-medium);
    }
    
    .badge-success {
        background-color: #d4edda;
        color: #155724;
    }
    
    .badge-warning {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .badge-danger {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .form-text {
        color: var(--neutral-600);
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1rem;
    }

    .size-options {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: var(--space-2);
    }
    
    .size-option {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border: 1px solid var(--neutral-300);
        border-radius: var(--radius-md);
        cursor: pointer;
    }
    
    .size-option:hover {
        background-color: var(--neutral-100);
    }
    
    .size-option input[type="checkbox"] {
        margin: 0;
    }
    
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>