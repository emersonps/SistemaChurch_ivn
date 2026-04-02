<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();

try { 
    $pdo->query("ALTER TABLE congregations ADD COLUMN status VARCHAR(20) DEFAULT 'active'"); 
    echo "Column 'status' added to congregations.\n"; 
} catch(Exception $e) { 
    echo "Note: " . $e->getMessage() . "\n"; 
}

try { 
    $pdo->query("UPDATE congregations SET status = 'active'"); 
    echo "Congregations status updated to 'active'.\n"; 
} catch(Exception $e) { 
    echo "Error updating: " . $e->getMessage() . "\n"; 
}
