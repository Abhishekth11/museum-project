<?php
/**
 * Theme API Endpoint
 * Handles theme switching via AJAX
 */
session_start();
require_once '../includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Get theme from POST data
$theme = isset($_POST['theme']) ? $_POST['theme'] : null;

// Validate theme
if (!in_array($theme, ['light', 'dark'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid theme specified'
    ]);
    exit;
}

// Set theme preference
$result = handleThemeSwitch($theme);

// Return result
echo json_encode($result);
?>
