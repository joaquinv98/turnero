<?php
function log_debug($message, $data = []) {
    $logFile = __DIR__ . '/../debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $context = !empty($data) ? ' | Data: ' . json_encode($data) : '';
    $entry = "[$timestamp] $message$context" . PHP_EOL;
    file_put_contents($logFile, $entry, FILE_APPEND);
}
?>
