<?php
require 'config/database.php';
$db = (new Database())->connect();
$stmt = $db->query('SHOW TABLES');
var_dump($stmt->fetchAll(PDO::FETCH_COLUMN));

$stmt = $db->query("DESCRIBE tithes");
echo "\nTithes:\n";
var_dump($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt = $db->query("DESCRIBE expenses");
echo "\nExpenses:\n";
var_dump($stmt->fetchAll(PDO::FETCH_ASSOC));
