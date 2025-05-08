<?php
session_start();
include_once 'includes/header.php';
require 'config/database.php';

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = $_POST['user'];
    $password = md5($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :user AND password = :password AND role = 'admin'");
    $stmt->execute([':user' => $user, ':password' => $password]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = 1;
        // log the user for debugging purposes
        error_log("User ID: " . $_SESSION['user_id'] . " logged in as " . $_SESSION['role']);

        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid login credentials.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Admin Login</h1>
    <form method="POST" action="">
        <input name="user" placeholder="user" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <?php if (isset($error)) echo "<p>$error</p>"; ?>
</body>
</html>
