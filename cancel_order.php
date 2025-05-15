<?php
session_start();
require 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => '', 'debug' => []];

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $response['message'] = "No order ID provided";
    header("Location: order_history.php?error=" . urlencode($response['message']));
    exit();
}

$order_id = (int)$_GET['id'];
$response['debug']['order_id'] = $order_id;

try {
    // Verify the order exists and belongs to the current user
    $stmt = $conn->prepare("SELECT id, user_id, status FROM orders WHERE id = :id");
    $stmt->execute([':id' => $order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $response['debug']['order_check'] = $order ? 'Found' : 'Not found';
    
    if (!$order) {
        throw new Exception("Order not found");
    }
    
    if ($order['user_id'] != $user_id) {
        $response['debug']['user_mismatch'] = true;
        throw new Exception("This order doesn't belong to your account");
    }
    
    $response['debug']['status_check'] = $order['status'];
    
    // Only allow cancellation of pending orders
    if ($order['status'] !== 'pending') {
        throw new Exception("Only pending orders can be cancelled");
    }
    
    // Call the stored procedure to cancel the order
    $stmt = $conn->prepare("CALL CancelOrder(:order_id, :reason)");
    $stmt->execute([
        ':order_id' => $order_id,
        ':reason' => 'Cancelled by customer'
    ]);
    
    // Double check the cancellation worked
    $stmt = $conn->prepare("SELECT status FROM orders WHERE id = :id");
    $stmt->execute([':id' => $order_id]);
    $updated = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $response['debug']['updated_status'] = $updated['status'];
    
    if ($updated['status'] !== 'cancelled') {
        throw new Exception("Failed to cancel order");
    }
    
    $response['success'] = true;
    $response['message'] = "Order #{$order_id} has been cancelled successfully";
    header("Location: order_history.php?success=" . urlencode($response['message']));
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    header("Location: order_history.php?error=" . urlencode($response['message']));
}
exit();
?>