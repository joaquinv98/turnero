<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'login';

if ($action === 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
    }
    exit;
}

if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'change_password') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $new_password = $data['password'] ?? '';

    if (strlen($new_password) < 4) {
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 4 caracteres']);
        exit;
    }

    $hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hash, $_SESSION['user_id']]);

    echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
    exit;
}
?>
