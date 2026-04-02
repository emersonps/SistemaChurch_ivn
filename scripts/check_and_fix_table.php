<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->connect();
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "Driver: $driver\n";
    
    if ($driver === 'sqlite') {
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='system_payments'");
    } else {
        $stmt = $db->query("SHOW TABLES LIKE 'system_payments'");
    }
    
    $table = $stmt->fetchColumn();
    
    if ($table) {
        echo "Table 'system_payments' EXISTS.\n";
    } else {
        echo "Table 'system_payments' DOES NOT EXIST.\n";
        
        // Try creating it again explicitly here to debug
        echo "Attempting to create...\n";
        if ($driver === 'sqlite') {
            $sql = "CREATE TABLE IF NOT EXISTS system_payments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                reference_month VARCHAR(7) NOT NULL UNIQUE,
                amount DECIMAL(10,2),
                payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                status VARCHAR(20) DEFAULT 'paid'
            )";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS system_payments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                reference_month VARCHAR(7) NOT NULL UNIQUE,
                amount DECIMAL(10,2),
                payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                status VARCHAR(20) DEFAULT 'paid'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        }
        $db->exec($sql);
        echo "Create command executed.\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
