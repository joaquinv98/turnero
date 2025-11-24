<?php
session_start();
require_once '../config/db.php';
require_once 'logger.php';

if (!isset($_SESSION['user_id'])) exit;

$start = $_GET['start'] ?? date('Y-m-d');
$end = $_GET['end'] ?? date('Y-m-d');

log_debug("Export API called", ['start' => $start, 'end' => $end]);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="reporte_turnos.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Fecha', 'Hora Inicio', 'Hora Fin', 'Niños', 'Duracion', 'Precio', 'Estado', 'Email', 'Pago']);

$stmt = $pdo->prepare("
    SELECT id, DATE(start_time), TIME(start_time), TIME(end_time), child_names, duration_minutes, total_price, status, client_email, payment_method 
    FROM turns 
    WHERE DATE(start_time) BETWEEN ? AND ? 
    ORDER BY start_time DESC
");
$stmt->execute([$start, $end]);

while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    fputcsv($output, $row);
}

fclose($output);
?>
