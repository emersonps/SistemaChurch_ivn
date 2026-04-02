<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo implode(', ', $tables);
