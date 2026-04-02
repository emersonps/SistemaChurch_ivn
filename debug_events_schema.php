<?php
require_once __DIR__ . '/config/database.php';
$db = (new Database())->connect();
try {
    $cols = $db->query("DESCRIBE events")->fetchAll(PDO::FETCH_COLUMN);
    print_r($cols);
} catch (Exception $e) {
    echo $e->getMessage();
}
