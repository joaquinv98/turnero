<?php
session_start();
require_once '../config/db.php';
require_once 'smtp.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$action = $_GET['action'] ?? 'get';

if ($action === 'get') {
    $stmt = $pdo->query("SELECT * FROM settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    echo json_encode(['success' => true, 'settings' => $settings]);
    exit;
}

if ($action === 'update') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $pdo->beginTransaction();
        foreach ($data as $key => $value) {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        }
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Configuración guardada']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'test_email') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? '';
    
    if (!$email) {
        echo json_encode(['success' => false, 'message' => 'Email requerido']);
        exit;
    }

    try {
        $smtp = new SimpleSMTP();
        $smtp->send($email, "Test de Configuración", "<h1>Correo de Prueba</h1><p>Si ves esto, la configuración SMTP funciona correctamente.</p>");
        echo json_encode(['success' => true, 'message' => 'Email enviado correctamente']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error SMTP: ' . $e->getMessage()]);
    }
    exit;
}
?>
