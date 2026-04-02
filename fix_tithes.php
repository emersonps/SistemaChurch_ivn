<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'config/database.php';
$db = (new Database())->connect();

// Try to execute and catch exception if any
try {
    $count = $db->query("SELECT count(*) FROM tithes WHERE congregation_id IS NULL")->fetchColumn();
    echo "NULL congregation_id count: $count\n";
    
    // BACKFILL LOGIC
    echo "Backfilling from Service Reports...\n";
    $sql = "UPDATE tithes 
            SET congregation_id = (SELECT congregation_id FROM service_reports WHERE service_reports.id = tithes.service_report_id) 
            WHERE service_report_id IS NOT NULL AND congregation_id IS NULL";
    $affected = $db->exec($sql);
    echo "Updated $affected rows linked to service reports.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
