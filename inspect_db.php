<?php
require_once __DIR__ . '/config/database.php';

$db = new Database();
$pdo = $db->connect();

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$schema = [];
foreach ($tables as $table) {
    $columns = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    $schema[$table] = $columns;
}
echo json_encode($schema, JSON_PRETTY_PRINT);
