<?php
session_start();
require_once '../config/db.php';
require_once 'logger.php';

header('Content-Type: application/json');
date_default_timezone_set('America/Argentina/Buenos_Aires');

$action = $_GET['action'] ?? '';
log_debug("Auth API called", ['action' => $action]);

if ($action === 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        log_debug("Login success", ['user' => $username]);
        echo json_encode(['success' => true, 'redirect' => 'admin.php']);
    } else {
        log_debug("Login failed", ['user' => $username]);
        echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
    }

} elseif ($action === 'logout') {
    session_destroy();
    log_debug("Logout");
    echo json_encode(['success' => true]);

} elseif ($action === 'change_password') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $new_password = $data['password'] ?? '';

    if (strlen($new_password) < 6) {
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
        exit;
    }

    $hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hash, $_SESSION['user_id']]);

    log_debug("Password changed", ['user_id' => $_SESSION['user_id']]);
    echo json_encode(['success' => true, 'message' => 'Contraseña actualizada']);
}
?>
