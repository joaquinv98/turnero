<?php
session_start();
require_once '../config/db.php';
require_once 'logger.php';

header('Content-Type: application/json');
date_default_timezone_set('America/Argentina/Buenos_Aires');

$action = $_GET['action'] ?? 'get';
log_debug("Pricing API called", ['action' => $action]);

if ($action === 'get') {
    $stmt = $pdo->query("SELECT * FROM pricing ORDER BY child_count ASC, duration_minutes ASC");
    $pricing = $stmt->fetchAll();
    echo json_encode(['success' => true, 'pricing' => $pricing]);

} elseif ($action === 'update') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(403);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];
    $price = $data['price'];
    
    log_debug("Updating price", ['id' => $id, 'price' => $price]);

    $stmt = $pdo->prepare("UPDATE pricing SET price = ? WHERE id = ?");
    $stmt->execute([$price, $id]);
    
    echo json_encode(['success' => true]);
}
?>
