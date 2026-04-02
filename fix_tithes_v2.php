<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (file_exists('../config/database.php')) {
    echo "Found config/database.php\n";
    require '../config/database.php';
} else {
    echo "config/database.php NOT FOUND\n";
    exit(1);
}

try {
    $db = (new Database())->connect();
    echo "Connected to DB\n";
    
    $stmt = $db->query("SELECT count(*) FROM tithes WHERE congregation_id IS NULL");
    if ($stmt) {
        $count = $stmt->fetchColumn();
        echo "NULL congregation_id count: $count\n";
    } else {
        echo "Query failed\n";
    }

    $sql = "UPDATE tithes 
            SET congregation_id = (SELECT congregation_id FROM service_reports WHERE service_reports.id = tithes.service_report_id) 
            WHERE service_report_id IS NOT NULL AND congregation_id IS NULL";
    
    $affected = $db->exec($sql);
    echo "Updated $affected rows linked to service reports.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
