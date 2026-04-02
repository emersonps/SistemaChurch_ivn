<?php
// scripts/import_dump_to_mysql.php

require_once __DIR__ . '/../config/database.php';

$dumpFile = __DIR__ . '/../database/dump_mysql.sql';

if (!file_exists($dumpFile)) {
    die("Dump file not found: $dumpFile\n");
}

echo "Reading dump file...\n";
$sql = file_get_contents($dumpFile);

// Wrap with Foreign Key Checks disable
$sql = "SET FOREIGN_KEY_CHECKS = 0;\n" . $sql . "\nSET FOREIGN_KEY_CHECKS = 1;";

echo "Connecting to MySQL...\n";
$db = (new Database())->connect();

echo "Importing SQL to MySQL database...\n";

try {
    // Execute all at once if possible
    $db->exec($sql);
    echo "Import successful!\n";
} catch (PDOException $e) {
    echo "Import failed: " . $e->getMessage() . "\n";
    echo "Retrying statement by statement with FK checks disabled...\n";
    
    // Disable FK checks for session
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
    
    $statements = explode(';', $sql);
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (!empty($stmt) && strpos($stmt, 'SET FOREIGN_KEY_CHECKS') === false) {
            try {
                $db->exec($stmt);
            } catch (PDOException $e2) {
                echo "Error executing statement: " . substr($stmt, 0, 50) . "...\n";
                echo "Error: " . $e2->getMessage() . "\n";
            }
        }
    }
    
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
}
