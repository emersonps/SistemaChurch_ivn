<?php
// scripts/update_system_payments_table.php

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->connect();
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    
    echo "Creating system_payments table...\n";
    
    if ($driver === 'sqlite') {
        $sql = "CREATE TABLE IF NOT EXISTS system_payments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            reference_month VARCHAR(7) NOT NULL UNIQUE, -- YYYY-MM
            amount DECIMAL(10,2),
            payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            status VARCHAR(20) DEFAULT 'paid'
        )";
    } else { // mysql
        $sql = "CREATE TABLE IF NOT EXISTS system_payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            reference_month VARCHAR(7) NOT NULL UNIQUE, -- YYYY-MM
            amount DECIMAL(10,2),
            payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            status VARCHAR(20) DEFAULT 'paid'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    }
    
    $db->exec($sql);
    echo "Table system_payments created successfully.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
