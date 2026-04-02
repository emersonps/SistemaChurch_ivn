<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';

echo "Starting migration...\n";

try {
    $db = (new Database())->connect();
    
    // Check if column exists
    $columns = $db->query("PRAGMA table_info(events)")->fetchAll();
    $hasStatus = false;
    foreach ($columns as $col) {
        if ($col['name'] === 'status') {
            $hasStatus = true;
            break;
        }
    }
    
    if (!$hasStatus) {
        echo "Adding 'status' column...\n";
        $db->exec("ALTER TABLE events ADD COLUMN status TEXT DEFAULT 'active'");
        echo "Column 'status' added successfully.\n";
    } else {
        echo "Column 'status' already exists.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Done.\n";
