<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $theme = $input['theme'] ?? '';
    
    if (in_array($theme, ['light-theme', 'dark-theme'])) {
        // Save to session
        $_SESSION['theme'] = $theme;
        
        // Set cookie
        setcookie('theme', $theme, time() + (365 * 24 * 60 * 60), '/', '', false, true);
        
        echo json_encode(['success' => true, 'theme' => $theme]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid theme']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
