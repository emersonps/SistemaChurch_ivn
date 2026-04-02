<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();

    echo "Columns in groups table:\n";
    $stmt = $db->query("DESCRIBE `groups`");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($columns);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
