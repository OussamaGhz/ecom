<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'config/database.php';
include_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$page_title = "Login";

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

include_once 'includes/header.php';
?>

<div class="container">
    <div class="auth-wrapper d-flex justify-content-center">
        <div class="auth-form card shadow">
            <div class="auth-header">
                <h1>Login to Your Account</h1>
                <p>Welcome back! Please enter your credentials to access your account.</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" data-validate="true">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                        <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="auth-separator">
                <span>Or login with</span>
            </div>
            
            <div class="social-auth">
                <button class="btn btn-outline btn-social">
                    <i class="fab fa-google"></i> Google
                </button>
                <button class="btn btn-outline btn-social">
                    <i class="fab fa-facebook-f"></i> Facebook
                </button>
            </div>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Register</a></p>
                <p>Are you an admin? <a href="admin_login.php">Login as Admin</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>