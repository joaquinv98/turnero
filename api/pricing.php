<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    $stmt = $pdo->query("SELECT * FROM pricing ORDER BY child_count ASC, duration_minutes ASC");
    echo json_encode(['success' => true, 'pricing' => $stmt->fetchAll()]);
} elseif ($action === 'update') {
    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Solo administradores pueden modificar precios']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];
    $price = $data['price'];

    $stmt = $pdo->prepare("UPDATE pricing SET price = ? WHERE id = ?");
    if ($stmt->execute([$price, $id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
    }
}
?>
