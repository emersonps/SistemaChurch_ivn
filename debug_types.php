<?php
require_once __DIR__ . '/config/database.php';

$db = (new Database())->connect();
$stmt = $db->query("SELECT DISTINCT type FROM events");
$types = $stmt->fetchAll(PDO::FETCH_COLUMN);

print_r($types);
