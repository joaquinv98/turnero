<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$action = $_GET['action'] ?? 'daily';

if ($action === 'preview') {
    $start = $_GET['start'] ?? date('Y-m-d');
    $end = $_GET['end'] ?? date('Y-m-d');
    
    $stmt = $pdo->prepare("
        SELECT DATE(start_time) as date, 
               TIME(start_time) as time, 
               child_names, 
               duration_minutes, 
               total_price, 
               payment_method,
               client_email
        FROM turns 
        WHERE DATE(start_time) BETWEEN ? AND ? 
        ORDER BY start_time DESC
    ");
    $stmt->execute([$start, $end]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

if ($action === 'daily') {
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT SUM(total_price) as income, COUNT(*) as turns_count FROM turns WHERE DATE(start_time) = ?");
    $stmt->execute([$today]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Active turns
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM turns WHERE status = 'active'");
    $active_count = $stmt->fetch()['count'] ?? 0;

    echo json_encode([
        'success' => true, 
        'income' => $result['income'] ?? 0,
        'turns_count' => $result['turns_count'] ?? 0,
        'active_count' => $active_count
    ]);
    exit;
}
?>
