<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) exit;

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

// Find distinct emails and the most recent child name associated with them
// This is a heuristic since one email might have multiple kids.
// We'll just return the email and let the frontend handle it, or return a list of names.
try {
    $stmt = $pdo->prepare("
        SELECT DISTINCT client_email, child_names 
        FROM turns 
        WHERE client_email LIKE ? 
        ORDER BY start_time DESC 
        LIMIT 5
    ");
    $stmt->execute(["%$query%"]);

    echo json_encode($stmt->fetchAll());
} catch (Exception $e) {
    error_log("Autocomplete Error: " . $e->getMessage());
    echo json_encode([]);
}
?>
