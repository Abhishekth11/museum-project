<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';

// Check if user is logged in and is admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // Get exhibition categories and counts
    $stmt = $pdo->prepare("SELECT category, COUNT(*) as count FROM exhibitions WHERE category IS NOT NULL AND category != '' GROUP BY category ORDER BY count DESC");
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    $labels = [];
    $data = [];
    
    foreach ($results as $row) {
        $labels[] = $row['category'];
        $data[] = $row['count'];
    }
    
    echo json_encode([
        'labels' => $labels,
        'data' => $data
    ]);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
