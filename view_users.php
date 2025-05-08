<?php
session_start();
include_once 'includes/header.php';
require 'config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

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
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Users</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>All Users</h1>

    <table>
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
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <a href="view_users.php?delete=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="admin_dashboard.php">Back to Dashboard</a>
</body>
</html>
