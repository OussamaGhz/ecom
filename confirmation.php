<?php
session_start();
require 'config/database.php';
include 'includes/header.php';

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;
?>

<div class="container">
    <h1 class="page-title">Order Confirmation</h1>
    
    <div class="confirmation-message">
        <i class="fas fa-check-circle"></i>
        <h2>Thank you for your order!</h2>
        <p>Your order #<?php echo htmlspecialchars($order_id); ?> has been placed successfully.</p>
    </div>
    
    <div class="confirmation-actions">
        <a href="index.php" class="btn btn-primary">Continue Shopping</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>