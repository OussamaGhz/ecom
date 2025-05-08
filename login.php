<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'config/database.php';
include_once 'includes/header.php';
include_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session only if it's not already active
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = :username";
    $stmt = $conn->prepare($query);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<div class="container">
    <h1>Login</h1>
    <?php if ($error): ?>
        <div class="error"><?= $error; ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <div class="admin-login">
        <p>Are you an admin? <a href="admin_login.php">Login as Admin</a></p>
    </div>
</div>