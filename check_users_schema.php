<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Starting check...\n";

if (!file_exists('config/database.php')) {
    die("config/database.php not found!");
}

require 'config/database.php';

try {
    $db = (new Database())->connect();
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "Driver: $driver\n";
    
    if ($driver === 'sqlite') {
        $stmt = $db->query("PRAGMA table_info(users)");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo "Column: {$col['name']} ({$col['type']})\n";
        }
    } else {
        $stmt = $db->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo "Column: {$col['Field']} ({$col['Type']})\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
