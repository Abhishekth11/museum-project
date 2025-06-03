<?php
session_start();
require_once 'includes/db.php'; // Add this line to include database connection
require_once 'includes/functions.php';

// Log the logout activity if user is logged in
if (isLoggedIn()) {
    logUserActivity($_SESSION['user_id'], 'logout', 'User logged out successfully');
}

// Store logout message before destroying session
$logout_message = 'You have been successfully logged out.';

// Clear all session variables
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Start a new session for the logout message
session_start();
$_SESSION['logout_message'] = $logout_message;

// Redirect to login page
header('Location: login.php');
exit;
?>
