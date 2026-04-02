<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    $stmt = $db->query("SHOW COLUMNS FROM ebd_students");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columns in ebd_students:\n";
    foreach ($cols as $c) {
        echo "- " . $c['Field'] . " (" . $c['Type'] . ")\n";
    }
    
    // Check sample data
    $stmt = $db->query("SELECT * FROM ebd_students LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nSample Data:\n";
    print_r($rows);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
