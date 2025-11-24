<?php
session_start();
require_once '../config/db.php';
require_once 'logger.php';

header('Content-Type: application/json');
date_default_timezone_set('America/Argentina/Buenos_Aires');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$action = $_GET['action'] ?? '';
log_debug("Reports API called", ['action' => $action]);

if ($action === 'daily') {
    $today = date('Y-m-d');
    
    // Total income for today
    $stmt = $pdo->prepare("SELECT SUM(amount) FROM transactions WHERE DATE(created_at) = ?");
    $stmt->execute([$today]);
    $income = $stmt->fetchColumn() ?: 0;
    
    // Total turns today
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM turns WHERE DATE(start_time) = ?");
    $stmt->execute([$today]);
    $count = $stmt->fetchColumn() ?: 0;
    
    echo json_encode(['success' => true, 'income' => $income, 'turns_count' => $count]);

} elseif ($action === 'preview') {
    $start = $_GET['start'] ?? date('Y-m-d');
    $end = $_GET['end'] ?? date('Y-m-d');
    
    log_debug("Fetching report preview", ['start' => $start, 'end' => $end]);

    $stmt = $pdo->prepare("
        SELECT 
            DATE(start_time) as date,
            TIME(start_time) as time,
            child_names,
            client_email,
            duration_minutes,
            total_price,
            payment_method
        FROM turns 
        WHERE DATE(start_time) BETWEEN ? AND ?
        ORDER BY start_time DESC
    ");
    $stmt->execute([$start, $end]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    log_debug("Report data fetched", ['count' => count($data)]);
    echo json_encode(['success' => true, 'data' => $data]);
}
?>
