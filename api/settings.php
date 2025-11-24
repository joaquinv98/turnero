<?php
session_start();
require_once '../config/db.php';
require_once 'smtp.php';
require_once 'logger.php';

header('Content-Type: application/json');
date_default_timezone_set('America/Argentina/Buenos_Aires');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$action = $_GET['action'] ?? 'get';
log_debug("Settings API called", ['action' => $action]);

if ($action === 'get') {
    $stmt = $pdo->query("SELECT * FROM settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    echo json_encode(['success' => true, 'settings' => $settings]);

} elseif ($action === 'update') {
    $data = json_decode(file_get_contents('php://input'), true);
    log_debug("Updating settings", $data);
    
    foreach ($data as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Configuración guardada']);

} elseif ($action === 'test_email') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? '';
    log_debug("Testing email", ['to' => $email]);
    
    if (!$email) {
        echo json_encode(['success' => false, 'message' => 'Email requerido']);
        exit;
    }

    $smtp = new SimpleSMTP();
    try {
        $smtp->send($email, "Prueba de Configuración", "<h1>Funciona!</h1><p>El sistema de correo está configurado correctamente.</p>");
        log_debug("Test email sent success");
        echo json_encode(['success' => true, 'message' => 'Email enviado correctamente']);
    } catch (Exception $e) {
        log_debug("Test email failed: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>
