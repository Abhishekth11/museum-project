<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = "Login - National Museum of Art & Culture";

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$login_error = '';
$register_error = '';
$register_success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $login_error = 'Please enter both email and password';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, email, password, role, first_name, last_name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect to homepage or previous page
                $redirect = $_SESSION['redirect_url'] ?? 'index.php';
                unset($_SESSION['redirect_url']);
                header("Location: $redirect");
                exit;
            } else {
                $login_error = 'Invalid email or password';
            }
        } catch(PDOException $e) {
            $login_error = 'An error occurred. Please try again.';
        }
    }
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $register_error = 'Please fill out all fields';
    } elseif ($password !== $confirm_password) {
        $register_error = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $register_error = 'Password must be at least 8 characters long';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $register_error = 'Email already exists';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, 'user')");
                $stmt->execute([$first_name, $last_name, $email, $hashed_password]);
                
                $register_success = 'Registration successful! You can now log in.';
            }
        } catch(PDOException $e) {
            $register_error = 'An error occurred. Please try again later.';
        }
    }
}

include 'includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-tabs">
                <button class="auth-tab <?php echo empty($register_success) && empty($register_error) ? 'active' : ''; ?>" data-tab="login">Login</button>
                <button class="auth-tab <?php echo !empty($register_success) || !empty($register_error) ? 'active' : ''; ?>" data-tab="register">Register</button>
            </div>
            
            <div class="auth-content">
                <div class="auth-panel <?php echo empty($register_success) && empty($register_error) ? 'active' : ''; ?>" id="login-panel">
                    <h2>Login</h2>
                    <p>Enter your credentials to access your account.</p>
                    
                    <?php if (!empty($login_error)): ?>
                        <div class="alert alert-error"><?php echo $login_error; ?></div>
                    <?php endif; ?>
                    
                    <form id="login-form" class="auth-form" method="POST">
                        <div class="form-group">
                            <label for="login-email">Email</label>
                            <input type="email" id="login-email" name="email" placeholder="your@email.com" required>
                        </div>
                        
                        <div class="form-group">
                            <div class="password-header">
                                <label for="login-password">Password</label>
                                <a href="forgot-password.php" class="forgot-password">Forgot password?</a>
                            </div>
                            <input type="password" id="login-password" name="password" required>
                        </div>
                        
                        <button type="submit" name="login" class="btn btn-primary btn-block">Login</button>
                    </form>
                </div>
                
                <div class="auth-panel <?php echo !empty($register_success) || !empty($register_error) ? 'active' : ''; ?>" id="register-panel">
                    <h2>Create an Account</h2>
                    <p>Register to book events, leave reviews, and more.</p>
                    
                    <?php if (!empty($register_error)): ?>
                        <div class="alert alert-error"><?php echo $register_error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($register_success)): ?>
                        <div class="alert alert-success"><?php echo $register_success; ?></div>
                    <?php endif; ?>
                    
                    <form id="register-form" class="auth-form" method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first-name">First Name</label>
                                <input type="text" id="first-name" name="first_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last-name">Last Name</label>
                                <input type="text" id="last-name" name="last_name" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="register-email">Email</label>
                            <input type="email" id="register-email" name="email" placeholder="your@email.com" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="register-password">Password</label>
                            <input type="password" id="register-password" name="password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm-password">Confirm Password</label>
                            <input type="password" id="confirm-password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" name="register" class="btn btn-primary btn-block">Create Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
$page_scripts = ['js/auth.js'];
include 'includes/footer.php'; 
?>
