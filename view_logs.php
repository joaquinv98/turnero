<?php
$logFile = __DIR__ . '/debug.log';
if (!file_exists($logFile)) {
    echo "<h1>No logs found</h1>";
    exit;
}

$logs = file_get_contents($logFile);
echo "<h1>Debug Logs</h1>";
echo "<pre>" . htmlspecialchars($logs) . "</pre>";
echo "<hr>";
echo "<a href='view_logs.php?clear=1'>Clear Logs</a>";

if (isset($_GET['clear'])) {
    file_put_contents($logFile, '');
    header('Location: view_logs.php');
    exit;
}
?>
