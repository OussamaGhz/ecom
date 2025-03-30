<?php
include 'config/database.php';
include 'includes/header.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if the user already exists
    $checkQuery = "SELECT * FROM users WHERE email = :email OR username = :username";
    $stmt = $conn->prepare($checkQuery);
    $stmt->execute(['email' => $email, 'username' => $username]);

    if ($stmt->rowCount() > 0) {
        $error = "Username or Email already exists!";
    } else {
        $query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $conn->prepare($query);
        $stmt->execute(['username' => $username, 'email' => $email, 'password' => $password]);

        header("Location: login.php");
        exit;
    }
}
?>

<div class="container">
    <h1>Register</h1>
    <?php if ($error): ?>
        <div class="error"><?= $error; ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
    </form>
</div>
