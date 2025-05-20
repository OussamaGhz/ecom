<?php
include 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';
$page_title = "Register";

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
        
        if ($stmt->execute(['username' => $username, 'email' => $email, 'password' => $password])) {
            $success = "Registration successful! You can now login.";
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="auth-wrapper d-flex justify-content-center">
        <div class="auth-form card shadow">
            <div class="auth-header">
                <h1>Create an Account</h1>
                <p>Join our community and start shopping with us today!</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                    <a href="login.php">Login now</a>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" data-validate="true">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Choose a username" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Create a password" required>
                        <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-meter"></div>
                        <span class="strength-text">Password strength</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm your password" required>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>
            
            <div class="auth-separator">
                <span>Or register with</span>
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
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>