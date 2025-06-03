<?php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/functions.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query) || strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$suggestions = getSearchSuggestions($query, 5);
echo json_encode($suggestions);
?>
