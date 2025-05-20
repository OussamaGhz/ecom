<?php
session_start();
require 'config/database.php';

$page_title = "Admin Login";
$error = '';

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $password = $_POST['password']; 

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username AND role = 'admin'");
    $stmt->execute([':username' => $username]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $isValid = false;
        
        // Check if the password is stored as MD5 hash
        if (strlen($user['password']) == 32) {
            $isValid = (md5($password) === $user['password']);
        } 
        // Check if the password is stored with password_hash
        else {
            $isValid = password_verify($password, $user['password']);
        }
        
        if ($isValid) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = 1;
            
            // log the user for debugging purposes
            error_log("User ID: " . $_SESSION['user_id'] . " logged in as " . $_SESSION['role']);

            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid admin credentials.";
        }
    } else {
        $error = "Invalid admin credentials.";
    }
}

include_once 'includes/header.php';
?>

<div class="container">
    <div class="auth-wrapper d-flex justify-content-center">
        <div class="auth-form card shadow">
            <div class="auth-header">
                <h1>Admin Login</h1>
                <p>Enter your administrator credentials to access the dashboard.</p>
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
                    <input type="text" id="username" name="username" class="form-control" placeholder="Admin username" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Admin password" required>
                        <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-lock"></i> Admin Login
                </button>
            </form>
            
            <div class="auth-footer">
                <p><a href="login.php">Return to user login</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>