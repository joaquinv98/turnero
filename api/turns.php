<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'create') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    $child_names = $data['child_names'] ?? []; 
    $duration = $data['duration'] ?? 60;
    $total_price = $data['price'] ?? 0;
    $email = $data['email'] ?? null;
    $payment_method = $data['payment_method'] ?? 'efectivo';
    
    if (empty($child_names)) {
        echo json_encode(['success' => false, 'message' => 'Debe ingresar al menos un niño']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $start_time = date('Y-m-d H:i:s');
        $end_time = date('Y-m-d H:i:s', strtotime("+$duration minutes"));
        
        $count = count($child_names);
        $price_per_turn = $total_price / $count;

        $first_turn_id = null;

        foreach ($child_names as $index => $name) {
            $stmt = $pdo->prepare("INSERT INTO turns (child_names, start_time, end_time, duration_minutes, total_price, created_by, client_email, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $start_time, $end_time, $duration, $price_per_turn, $_SESSION['user_id'], $email, $payment_method]);
            
            if ($index === 0) {
                $first_turn_id = $pdo->lastInsertId();
            }
        }

        $stmt = $pdo->prepare("INSERT INTO transactions (turn_id, amount, type) VALUES (?, ?, ?)");
        $stmt->execute([$first_turn_id, $total_price, $payment_method]);

        $pdo->commit();

        // Send Email if provided
        if ($email) {
            require_once 'smtp.php';
            $smtp = new SimpleSMTP();
            $subject = "Reserva Confirmada - Cafe Pelotero";
            $body = "<h1>¡Gracias por venir!</h1>
                     <p>Detalles de su turno:</p>
                     <ul>
                        <li>Niños: " . implode(', ', $child_names) . "</li>
                        <li>Inicio: $start_time</li>
                        <li>Fin: $end_time</li>
                        <li>Total: $$total_price</li>
                     </ul>
                     <p>Se adjunta evento de calendario.</p>";
            
            $ics = generateICS($start_time, $end_time, "Juego en Cafe Pelotero", "Turno para: " . implode(', ', $child_names));
            
            try {
                $smtp->send($email, $subject, $body, $ics);
            } catch (Exception $e) {
                // Log error but don't fail the request
                error_log("Mail error: " . $e->getMessage());
            }
        }

        echo json_encode(['success' => true, 'message' => 'Turnos iniciados']);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

} elseif ($action === 'list') {
    // Active turns + Finished turns within cleanup window
    $cleanup_stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'cleanup_minutes'");
    $cleanup_minutes = $cleanup_stmt->fetchColumn() ?: 30;

    // Calculate cutoff time in PHP to avoid DB timezone issues
    $cutoff_time = date('Y-m-d H:i:s', strtotime("-$cleanup_minutes minutes"));

    $stmt = $pdo->prepare("
        SELECT * FROM turns 
        WHERE end_time > ?
        ORDER BY 
            CASE WHEN status = 'finished' THEN 0 ELSE 1 END ASC,
            end_time ASC
    ");
    $stmt->execute([$cutoff_time]);
    $turns = $stmt->fetchAll();
    
    foreach ($turns as &$turn) {
        $now = new DateTime();
        $end = new DateTime($turn['end_time']);
        
        // If finished, remaining is 0 or negative
        if ($turn['status'] === 'finished') {
            $turn['remaining_seconds'] = 0;
            $turn['is_overtime'] = false;
        } else {
            $turn['remaining_seconds'] = ($end > $now) ? ($end->getTimestamp() - $now->getTimestamp()) : 0;
            $turn['is_overtime'] = ($now > $end);
        }
    }

    echo json_encode(['success' => true, 'turns' => $turns]);

} elseif ($action === 'history') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(403);
        exit;
    }
    
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT * FROM turns WHERE DATE(start_time) = ? ORDER BY start_time DESC");
    $stmt->execute([$today]);
    
    echo json_encode(['success' => true, 'turns' => $stmt->fetchAll()]);

} elseif ($action === 'finish') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(403);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    $turn_id = $data['id'];

    // Update status and set end_time to NOW if we want to cut it short? 
    // Or just mark finished. Let's just mark finished.
    $stmt = $pdo->prepare("UPDATE turns SET status = 'finished' WHERE id = ?");
    $stmt->execute([$turn_id]);
    
    echo json_encode(['success' => true]);
}
?>
