<?php
session_start();
require 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login with return URL
    header("Location: login.php?redirect=item.php?id=" . $_POST['item_id']);
    exit();
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get item details
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    // Validate item exists and has sufficient stock
    $stmt = $conn->prepare("SELECT id, stock FROM items WHERE id = :id");
    $stmt->execute([':id' => $item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        $_SESSION['error'] = "The selected item does not exist.";
        header("Location: index.php");
        exit();
    }
    
    // Validate quantity
    if ($quantity <= 0) {
        $quantity = 1;
    }
    
    // Check stock
    if ($quantity > $item['stock']) {
        $quantity = $item['stock'];
        $_SESSION['warning'] = "Quantity adjusted to match available stock.";
    }
    
    // Initialize cart if needed
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Add to cart or update quantity
    if (isset($_SESSION['cart'][$item_id])) {
        $new_quantity = $_SESSION['cart'][$item_id] + $quantity;
        // Check if new total exceeds stock
        if ($new_quantity > $item['stock']) {
            $new_quantity = $item['stock'];
            $_SESSION['warning'] = "Quantity adjusted to match available stock.";
        }
        $_SESSION['cart'][$item_id] = $new_quantity;
    } else {
        $_SESSION['cart'][$item_id] = $quantity;
    }
    
    $_SESSION['success'] = "Item added to your cart.";
    
    // Redirect back to previous page or cart
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : "cart.php";
    header("Location: $redirect");
    exit();
} else {
    // If someone tries to access this directly, redirect to home
    header("Location: index.php");
    exit();
}
?>