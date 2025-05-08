<?php
session_start();
require 'config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Set page title
$page_title = "Manage Users";

// Fetch all users
$stmt = $conn->query("SELECT * FROM users WHERE role = 'user'");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Delete user
if (isset($_GET['delete'])) {
    $userId = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);

    header("Location: view_users.php");
    exit();
}

// Include header after setting variables
include_once 'includes/header.php';
?>

<div class="container">
    <div class="dashboard-header">
        <h1 class="page-title">User Management</h1>
        <div class="dashboard-actions">
            <a href="admin_dashboard.php" class="btn btn-sm btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
    
    <div class="admin-stats">
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <h3>Total Users</h3>
            <p class="stat-number"><?php echo count($users); ?></p>
        </div>
    </div>

    <div class="admin-card">
        <h2><i class="fas fa-user-friends"></i> Registered Users</h2>
        
        <?php if (empty($users)): ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No Users Found</h3>
                <p>There are no registered users in the system yet.</p>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>#<?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <div class="admin-actions">
                                    <a href="view_users.php?delete=<?php echo $user['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');" 
                                       class="btn btn-sm btn-outline delete-btn">
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

<?php include 'includes/footer.php'; ?>