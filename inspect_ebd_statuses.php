<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    $stmt = $db->query("SELECT DISTINCT status FROM ebd_students");
    $statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Statuses in ebd_students:\n";
    foreach ($statuses as $s) {
        echo "- '$s'\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
