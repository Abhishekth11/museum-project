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
    // Get upcoming events by month
    $stmt = $pdo->prepare("SELECT 
                DATE_FORMAT(event_date, '%Y-%m') as month, 
                COUNT(*) as count 
            FROM events 
            WHERE event_date >= CURDATE() 
            GROUP BY month 
            ORDER BY month 
            LIMIT 6");
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    $labels = [];
    $data = [];
    
    foreach ($results as $row) {
        // Format month for display
        $date = new DateTime($row['month'] . '-01');
        $labels[] = $date->format('M Y');
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
