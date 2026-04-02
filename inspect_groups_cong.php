<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();
$res = $pdo->query('SELECT g.id, g.name, g.congregation_id, c.name as c_name FROM `groups` g LEFT JOIN congregations c ON g.congregation_id = c.id')->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($res, JSON_PRETTY_PRINT);
