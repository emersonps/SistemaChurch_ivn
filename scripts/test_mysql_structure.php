<?php
// scripts/test_mysql_structure.php

require_once __DIR__ . '/../config/database.php';

echo "Testing MySQL Connection...\n";

try {
    $db = (new Database())->connect();
    echo "Connection successful!\n\n";

    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        echo "No tables found in database.\n";
    } else {
        echo "Tables found: " . implode(", ", $tables) . "\n\n";
        
        foreach ($tables as $table) {
            echo "Structure for table '$table':\n";
            $stmt = $db->query("DESCRIBE `$table`");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate padding for alignment
            $maxFieldLen = 0;
            $maxTypeLen = 0;
            foreach ($columns as $col) {
                $maxFieldLen = max($maxFieldLen, strlen($col['Field']));
                $maxTypeLen = max($maxTypeLen, strlen($col['Type']));
            }
            
            foreach ($columns as $col) {
                echo str_pad($col['Field'], $maxFieldLen + 2) . 
                     str_pad($col['Type'], $maxTypeLen + 2) . 
                     ($col['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . 
                     ($col['Key'] ? " ({$col['Key']})" : "") . 
                     "\n";
            }
            echo "\n";
        }
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
