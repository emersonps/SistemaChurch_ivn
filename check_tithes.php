<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    
    $sql = "SELECT id, name FROM congregations";
    $stmt = $db->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        echo "ID: " . $row['id'] . " - Name: " . $row['name'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
