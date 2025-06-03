<?php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/functions.php';

$status = $_GET['status'] ?? 'all';
$category = $_GET['category'] ?? 'all';

$sql = "SELECT * FROM exhibitions WHERE 1=1";
$params = [];

if ($status !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status;
}

if ($category !== 'all') {
    $sql .= " AND category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY start_date DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $exhibitions = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $exhibitions]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
