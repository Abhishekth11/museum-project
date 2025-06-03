<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check for logout message
$logout_message = '';
if (isset($_SESSION['logout_message'])) {
    $logout_message = $_SESSION['logout_message'];
    unset($_SESSION['logout_message']);
}

// If user is already logged in, redirect based on role
if (isLoggedIn()) {
    redirectBasedOnRole();
    exit;
}

// Initialize variables
$error = '';
$success = '';
$email = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validate input
        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password.';
        } else {
            try {
                // Get user by email
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['first_name'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['login_time'] = time();
                    
                    // Update last login time
                    $update_stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $update_stmt->execute([$user['id']]);
                    
                    // Log the login activity
                    logUserActivity($user['id'], 'login', 'User logged in successfully');
                    
                    // Set success message for admin dashboard
                    if (in_array($user['role'], ['admin', 'moderator', 'staff'])) {
                        $_SESSION['login_success'] = "Welcome back, {$user['first_name']}! You've successfully logged in to the admin panel.";
                    }
                    
                    // Redirect based on role
                    redirectBasedOnRole();
                    exit;
                } else {
                    $error = 'Invalid email or password. Please try again.';
                    // Log failed login attempt
                    if ($user) {
                        logUserActivity($user['id'], 'login_failed', 'Failed login attempt');
                    }
                }
            } catch(PDOException $e) {
                $error = 'An error occurred. Please try again later.';
                error_log("Login error: " . $e->getMessage());
            }
        }
    }
}

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        
        // Validate input
        if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
            $error = 'Please fill out all fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } else {
            try {
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->fetch()) {
                    $error = 'Email address is already registered.';
                } else {
                    // Hash password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new user
                    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role, status, created_at) VALUES (?, ?, ?, ?, 'user', 'active', NOW())");
                    $stmt->execute([$firstName, $lastName, $email, $hashedPassword]);
                    
                    $userId = $pdo->lastInsertId();
                    
                    // Log the registration
                    logUserActivity($userId, 'registration', 'New user registered');
                    
                    $success = 'Registration successful! You can now log in.';
                }
            } catch(PDOException $e) {
                $error = 'An error occurred. Please try again later.';
                error_log("Registration error: " . $e->getMessage());
            }
        }
    }
}

$page_title = "Login - National Museum of Art & Culture";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="light-theme">
    <?php include 'includes/header.php'; ?>

    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <?php if ($logout_message): ?>
                    <div class="alert alert-success logout-alert">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($logout_message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="auth-form-container">
                    <div class="auth-form-wrapper <?php echo isset($_GET['register']) ? 'slide-out-left' : ''; ?>" id="login-wrapper">
                        <div class="form-header">
                            <h2>Welcome Back</h2>
                            <p>Enter your credentials to access your account</p>
                        </div>
                        
                        <?php if ($error && !isset($_GET['register'])): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success && !isset($_GET['register'])): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form id="login-form" class="auth-form" method="POST" action="login.php">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="action" value="login">
                            
                            <div class="form-group">
                                <label for="login-email">Email</label>
                                <input type="email" id="login-email" name="email" placeholder="your@email.com" value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <div class="password-header">
                                    <label for="login-password">Password</label>
                                    <a href="forgot-password.php" class="forgot-password">Forgot password?</a>
                                </div>
                                <input type="password" id="login-password" name="password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">Login</button>
                        </form>
                        
                        <div class="form-toggle">
                            <p>Don't have an account?</p>
                            <a href="?register=1" class="toggle-link" id="show-register">Create an Account</a>
                        </div>
                    </div>
                    
                    <div class="auth-form-wrapper <?php echo isset($_GET['register']) ? 'slide-in-right' : 'slide-out-right'; ?>" id="register-wrapper">
                        <div class="form-header">
                            <h2>Create an Account</h2>
                            <p>Register to book events, leave reviews, and more</p>
                        </div>
                        
                        <?php if ($error && isset($_GET['register'])): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success && isset($_GET['register'])): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form id="register-form" class="auth-form" method="POST" action="login.php?register=1">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="action" value="register">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first-name">First Name</label>
                                    <input type="text" id="first-name" name="firstName" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="last-name">Last Name</label>
                                    <input type="text" id="last-name" name="lastName" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="register-email">Email</label>
                                <input type="email" id="register-email" name="email" placeholder="your@email.com" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="register-password">Password</label>
                                <input type="password" id="register-password" name="password" required>
                                <small>Password must be at least 8 characters long</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm-password">Confirm Password</label>
                                <input type="password" id="confirm-password" name="confirmPassword" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                        </form>
                        
                        <div class="form-toggle">
                            <p>Already have an account?</p>
                            <a href="login.php" class="toggle-link" id="show-login">Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="js/main.js"></script>
    <script src="js/auth.js"></script>
    <script>
        // Auto-hide logout message after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const logoutAlert = document.querySelector('.logout-alert');
            if (logoutAlert) {
                setTimeout(() => {
                    logoutAlert.style.opacity = '0';
                    setTimeout(() => {
                        logoutAlert.remove();
                    }, 300);
                }, 5000);
            }
        });
    </script>
</body>
</html>
