<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();

    echo "Columns in groups table:\n";
    $stmt = $db->query("DESCRIBE `groups`");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo $col['Field'] . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
