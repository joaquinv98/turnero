<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$db_host = $_POST['db_host'] ?? 'localhost';
$db_name = $_POST['db_name'] ?? 'cafe_pelotero';
$db_user = $_POST['db_user'] ?? 'root';
$db_pass = $_POST['db_pass'] ?? '';

$admin_user = $_POST['admin_user'] ?? 'admin';
$admin_pass = $_POST['admin_pass'] ?? '';

$smtp_host = $_POST['smtp_host'] ?? '';
$smtp_user = $_POST['smtp_user'] ?? '';
$smtp_pass = $_POST['smtp_pass'] ?? '';

try {
    // 1. Test DB Connection
    $dsn = "mysql:host=$db_host;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Create DB if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
    $pdo->exec("USE `$db_name`");

    // 2. Create config/db.php
    $config_content = "<?php
\$host = '$db_host';
\$db   = '$db_name';
\$user = '$db_user';
\$pass = '$db_pass';
\$charset = 'utf8mb4';

\$dsn = \"mysql:host=\$host;dbname=\$db;charset=\$charset\";
\$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    \$pdo = new PDO(\$dsn, \$user, \$pass, \$options);
} catch (\PDOException \$e) {
    throw new \PDOException(\$e->getMessage(), (int)\$e->getCode());
}
?>";
    
    if (!is_dir('../config')) mkdir('../config', 0777, true);
    file_put_contents('../config/db.php', $config_content);

    // 3. Create Tables
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL
    );

    CREATE TABLE IF NOT EXISTS turns (
        id INT AUTO_INCREMENT PRIMARY KEY,
        child_names TEXT NOT NULL,
        start_time DATETIME NOT NULL,
        end_time DATETIME NOT NULL,
        duration_minutes INT NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        created_by INT,
        client_email VARCHAR(100),
        payment_method VARCHAR(50),
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        turn_id INT,
        amount DECIMAL(10,2),
        type VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT
    );

    CREATE TABLE IF NOT EXISTS pricing (
        id INT AUTO_INCREMENT PRIMARY KEY,
        child_count INT NOT NULL,
        duration_minutes INT NOT NULL,
        price DECIMAL(10,2) NOT NULL
    );
    ";
    $pdo->exec($sql);

    // 4. Insert Admin
    $hash = password_hash($admin_pass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?) ON DUPLICATE KEY UPDATE password = ?");
    $stmt->execute([$admin_user, $hash, $hash]);

    // 5. Insert Settings
    $settings = [
        'smtp_host' => $smtp_host,
        'smtp_port' => '587',
        'smtp_user' => $smtp_user,
        'smtp_pass' => $smtp_pass,
        'cleanup_minutes' => '30'
    ];
    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    }

    // 6. Insert Default Pricing
    $child_counts = [1, 2, 3, 4];
    $durations = [60, 120, 180];
    
    foreach ($child_counts as $count) {
        foreach ($durations as $duration) {
            $stmt = $pdo->prepare("SELECT id FROM pricing WHERE child_count = ? AND duration_minutes = ?");
            $stmt->execute([$count, $duration]);
            
            if (!$stmt->fetch()) {
                $price = $count * ($duration / 60) * 1500; 
                $insert = $pdo->prepare("INSERT INTO pricing (child_count, duration_minutes, price) VALUES (?, ?, ?)");
                $insert->execute([$count, $duration, $price]);
            }
        }
    }

    // 6. Handle Favicon
    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/img/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        move_uploaded_file($_FILES['favicon']['tmp_name'], $uploadDir . 'favicon.ico');
    }

    // 7. Self-Destruct
    // Delete setup files for security
    if (file_exists('../setup.php')) unlink('../setup.php');
    // We can't delete the running script immediately without issues on some servers, 
    // but we can try. Or better, register a shutdown function.
    register_shutdown_function(function() {
        if (file_exists(__FILE__)) unlink(__FILE__);
    });

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
