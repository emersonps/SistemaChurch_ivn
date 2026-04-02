<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    $stmt = $db->query("SHOW COLUMNS FROM ebd_students LIKE 'status'");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Column status:\n";
    print_r($col);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
