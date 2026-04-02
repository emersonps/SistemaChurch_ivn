<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    $stmt = $db->query("SELECT DISTINCT type FROM tithes");
    $types = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Types found in DB:\n";
    foreach ($types as $t) {
        echo "- '$t' (Hex: " . bin2hex($t) . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
