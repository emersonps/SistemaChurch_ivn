<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();
echo json_encode(array_column($pdo->query('SHOW COLUMNS FROM `groups`')->fetchAll(PDO::FETCH_ASSOC), 'Type', 'Field'), JSON_PRETTY_PRINT);
