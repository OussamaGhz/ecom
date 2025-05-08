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
$page_title = "Edit Shoe";

// Fetch the item details
$stmt = $conn->prepare("SELECT * FROM items WHERE id = :id");
$stmt->execute([':id' => $id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header("Location: manage_items.php");
    exit();
}

// Define shoe categories, brands, and sizes
$shoe_styles = ['athletic', 'casual', 'formal', 'boots', 'sandals', 'heels', 'sneakers'];
$shoe_brands = ['Nike', 'Adidas', 'Puma', 'Reebok', 'New Balance', 'Converse', 'Vans', 'Dr. Martens', 'Clarks', 'Timberland'];
$shoe_colors = ['black', 'white', 'red', 'blue', 'green', 'brown', 'gray', 'beige', 'multicolor'];
$shoe_genders = ['men', 'women', 'unisex', 'kids'];

// Handle form submission
$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $style = $_POST['style'] ?? '';
    $brand = $_POST['brand'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $color = $_POST['color'] ?? '';
    $sizes = isset($_POST['sizes']) ? implode(',', $_POST['sizes']) : '';
    
    // Handle image upload
    $image = $item['image']; // Default to existing image
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($ext), $allowed)) {
            $new_filename = 'shoes_' . uniqid() . '.' . $ext;
            $upload_path = 'assets/images/products/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if it exists and is not the default
                if ($item['image'] && $item['image'] != 'placeholder.png' && file_exists('assets/images/products/' . $item['image'])) {
                    unlink('assets/images/products/' . $item['image']);
                }
                $image = 'images/products/' . $new_filename;
            } else {
                $error = "Failed to upload image";
            }
        } else {
            $error = "Invalid image format. Allowed formats: JPG, JPEG, PNG, WEBP";
        }
    }

    if (!$error) {
        try {
            $stmt = $conn->prepare("
                UPDATE items 
                SET name = :name, 
                    description = :description, 
                    price = :price, 
                    stock = :stock, 
                    style = :style,
                    brand = :brand,
                    gender = :gender,
                    color = :color,
                    size = :size,
                    image = :image
                WHERE id = :id
            ");
            
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':stock' => $stock,
                ':style' => $style,
                ':brand' => $brand,
                ':gender' => $gender,
                ':color' => $color,
                ':size' => $sizes,
                ':image' => $image,
                ':id' => $id
            ]);

            $message = "Shoe details updated successfully";
            
            // Refresh item data
            $stmt = $conn->prepare("SELECT * FROM items WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Include header after setting variables
include_once 'includes/header.php';
?>

<div class="container">
    <div class="dashboard-header">
        <h1 class="page-title">Edit Shoe</h1>
        <div class="dashboard-actions">
            <a href="manage_items.php" class="btn btn-sm btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Inventory
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-times-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="admin-card">
        <h2><i class="fas fa-edit"></i> Edit Shoe Details</h2>
        
        <form method="POST" action="" class="admin-form" enctype="multipart/form-data">
            <div class="form-row">
                <div class="admin-form-group">
                    <label for="name" class="admin-form-label">Shoe Name</label>
                    <input type="text" id="name" name="name" class="admin-form-control" value="<?php echo htmlspecialchars($item['name']); ?>" required>
                </div>
                
                <div class="admin-form-group">
                    <label for="brand" class="admin-form-label">Brand</label>
                    <select id="brand" name="brand" class="admin-form-control">
                        <option value="">Select Brand</option>
                        <?php foreach ($shoe_brands as $brand): ?>
                            <option value="<?php echo $brand; ?>" <?php echo isset($item['brand']) && $item['brand'] === $brand ? 'selected' : ''; ?>>
                                <?php echo $brand; ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="other" <?php echo isset($item['brand']) && !in_array($item['brand'], $shoe_brands) ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="admin-form-group">
                    <label for="style" class="admin-form-label">Style</label>
                    <select id="style" name="style" class="admin-form-control">
                        <option value="">Select Style</option>
                        <?php foreach ($shoe_styles as $style): ?>
                            <option value="<?php echo $style; ?>" <?php echo isset($item['style']) && $item['style'] === $style ? 'selected' : ''; ?>>
                                <?php echo ucfirst($style); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="admin-form-group">
                    <label for="gender" class="admin-form-label">Gender</label>
                    <select id="gender" name="gender" class="admin-form-control">
                        <option value="">Select Gender</option>
                        <?php foreach ($shoe_genders as $gender): ?>
                            <option value="<?php echo $gender; ?>" <?php echo isset($item['gender']) && $item['gender'] === $gender ? 'selected' : ''; ?>>
                                <?php echo ucfirst($gender); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="admin-form-group">
                    <label for="color" class="admin-form-label">Color</label>
                    <select id="color" name="color" class="admin-form-control">
                        <option value="">Select Color</option>
                        <?php foreach ($shoe_colors as $color): ?>
                            <option value="<?php echo $color; ?>" <?php echo isset($item['color']) && $item['color'] === $color ? 'selected' : ''; ?>>
                                <?php echo ucfirst($color); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="admin-form-group">
                <label class="admin-form-label">Available Sizes</label>
                <div class="size-options">
                    <?php 
                    $available_sizes = explode(',', $item['size'] ?? '');
                    $size_range = range(5, 13);
                    foreach ($size_range as $size): 
                    ?>
                        <label class="size-option">
                            <input type="checkbox" name="sizes[]" value="<?php echo $size; ?>" 
                                <?php echo in_array($size, $available_sizes) ? 'checked' : ''; ?>>
                            <?php echo $size; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="admin-form-group">
                <label for="description" class="admin-form-label">Description</label>
                <textarea id="description" name="description" class="admin-form-control" rows="4" required><?php echo htmlspecialchars($item['description']); ?></textarea>
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
            
            <div class="admin-form-group">
                <label for="image" class="admin-form-label">Product Image</label>
                <input type="file" id="image" name="image" class="admin-form-control" accept="image/jpeg,image/png,image/webp">
                <small class="form-text">Leave blank to keep the current image. Accepted formats: JPG, PNG, WEBP.</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Shoe
                </button>
                <a href="manage_items.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>

    <div class="admin-card">
        <h2><i class="fas fa-eye"></i> Shoe Preview</h2>
        
        <div class="product-preview">
            <div class="product-gallery">
                <div class="main-image">
                    <?php if (isset($item['image']) && $item['image']): ?>
                        <img src="assets/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <?php else: ?>
                        <img src="assets/images/placeholder.png" alt="Product image placeholder">
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="product-info">
                <div class="product-meta">
                    <?php if (isset($item['brand']) && $item['brand']): ?>
                        <span class="product-brand"><?php echo htmlspecialchars($item['brand']); ?></span>
                    <?php endif; ?>
                    
                    <?php if (isset($item['style']) && $item['style']): ?>
                        <span class="product-style"><?php echo ucfirst(htmlspecialchars($item['style'])); ?></span>
                    <?php endif; ?>
                    
                    <?php if (isset($item['gender']) && $item['gender']): ?>
                        <span class="product-gender"><?php echo ucfirst(htmlspecialchars($item['gender'])); ?></span>
                    <?php endif; ?>
                </div>
                
                <h1 class="product-title"><?php echo htmlspecialchars($item['name']); ?></h1>
                <div class="product-price">$<?php echo number_format($item['price'], 2); ?></div>
                
                <?php if (isset($item['color']) && $item['color']): ?>
                    <div class="product-color">
                        <span class="color-label">Color:</span>
                        <span class="color-value" style="background-color: <?php echo $item['color']; ?>"></span>
                        <span><?php echo ucfirst($item['color']); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($item['size']) && $item['size']): ?>
                    <div class="product-sizes">
                        <span class="sizes-label">Available Sizes:</span>
                        <div class="size-list">
                            <?php foreach (explode(',', $item['size']) as $size): ?>
                                <span class="size-box"><?php echo $size; ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($item['stock'] > 0): ?>
                    <div class="stock-status in-stock">
                        <i class="fas fa-check-circle"></i> In Stock (<?php echo $item['stock']; ?> pairs available)
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

<style>
    /* Edit Item specific styles */
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--space-4);
    }
    
    .form-actions {
        margin-top: var(--space-5);
        display: flex;
        gap: var(--space-3);
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
    
    .product-preview {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--space-6);
    }
    
    .main-image {
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: 0 5px 15px var(--shadow);
        aspect-ratio: 1 / 1;
    }
    
    .main-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    
    .product-meta {
        display: flex;
        gap: var(--space-3);
        margin-bottom: var(--space-3);
    }
    
    .product-brand,
    .product-style,
    .product-gender {
        background-color: var(--neutral-100);
        padding: 5px 10px;
        border-radius: var(--radius-md);
        font-size: 0.85rem;
        color: var(--neutral-700);
    }
    
    .product-title {
        font-size: var(--text-2xl);
        margin-bottom: var(--space-3);
    }
    
    .product-price {
        font-size: var(--text-xl);
        font-weight: var(--font-bold);
        color: var(--primary-600);
        margin-bottom: var(--space-4);
    }
    
    .product-color {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        margin-bottom: var(--space-3);
    }
    
    .color-label {
        font-weight: var(--font-medium);
    }
    
    .color-value {
        display: inline-block;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 1px solid var(--neutral-300);
    }
    
    .product-sizes {
        margin-bottom: var(--space-3);
    }
    
    .sizes-label {
        font-weight: var(--font-medium);
        display: block;
        margin-bottom: var(--space-2);
    }
    
    .size-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .size-box {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--neutral-300);
        border-radius: var(--radius-md);
        font-size: 0.9rem;
    }
    
    .stock-status {
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        padding: 8px 12px;
        border-radius: var(--radius-md);
        font-weight: var(--font-medium);
        margin-bottom: var(--space-4);
    }
    
    .in-stock {
        background-color: #d4edda;
        color: #155724;
    }
    
    .out-of-stock {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .product-description {
        margin-top: var(--space-4);
        padding-top: var(--space-4);
        border-top: 1px solid var(--neutral-200);
    }
    
    .product-description h3 {
        margin-bottom: var(--space-3);
    }
    
    .product-description p {
        line-height: 1.6;
    }
    
    @media (max-width: 768px) {
        .product-preview {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>