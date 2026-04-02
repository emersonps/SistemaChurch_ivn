<?php
require_once __DIR__ . '/config/database.php';
try {
    $db = (new Database())->connect();
    $stmt = $db->prepare("UPDATE migrations SET migration = '20260311_123000_create_groups_tables.php' WHERE migration = 'create_groups_tables.php'");
    $stmt->execute();
    echo "Migration record updated successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
