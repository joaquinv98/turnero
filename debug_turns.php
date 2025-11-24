<?php
require_once 'config/db.php';
date_default_timezone_set('America/Argentina/Buenos_Aires');

echo "<h1>Debug Turns</h1>";

// 1. Check Timezones
echo "<h2>Time Info</h2>";
echo "PHP Time: " . date('Y-m-d H:i:s') . "<br>";
echo "PHP Timezone: " . date_default_timezone_get() . "<br>";

try {
    $stmt = $pdo->query("SELECT NOW() as db_time, @@global.time_zone as global_tz, @@session.time_zone as session_tz");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "DB Time: " . $row['db_time'] . "<br>";
    echo "DB Global TZ: " . $row['global_tz'] . "<br>";
    echo "DB Session TZ: " . $row['session_tz'] . "<br>";
} catch (Exception $e) {
    echo "Error fetching DB time: " . $e->getMessage();
}

// 2. Check Cleanup Setting
echo "<h2>Settings</h2>";
$cleanup_minutes = 30;
try {
    $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'cleanup_minutes'");
    $val = $stmt->fetchColumn();
    if ($val) $cleanup_minutes = $val;
    echo "Cleanup Minutes: $cleanup_minutes<br>";
} catch (Exception $e) {
    echo "Error fetching settings: " . $e->getMessage();
}

// 3. Check Cutoff Calculation
$cutoff_time = date('Y-m-d H:i:s', strtotime("-$cleanup_minutes minutes"));
echo "Cutoff Time (PHP calculated): $cutoff_time<br>";

// 4. Check Turns Data
echo "<h2>Turns Data (Last 5)</h2>";
try {
    $stmt = $pdo->query("SELECT * FROM turns ORDER BY id DESC LIMIT 5");
    $turns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'><tr><th>ID</th><th>Start</th><th>End</th><th>Status</th><th>Visible?</th></tr>";
    foreach ($turns as $turn) {
        $isVisible = ($turn['end_time'] > $cutoff_time) ? 'YES' : 'NO';
        echo "<tr>";
        echo "<td>{$turn['id']}</td>";
        echo "<td>{$turn['start_time']}</td>";
        echo "<td>{$turn['end_time']}</td>";
        echo "<td>{$turn['status']}</td>";
        echo "<td><strong style='color:" . ($isVisible == 'YES' ? 'green' : 'red') . "'>$isVisible</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "Error fetching turns: " . $e->getMessage();
}
?>
