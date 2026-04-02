<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    $stmt = $db->query("SHOW COLUMNS FROM group_members LIKE 'role'");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($col);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
