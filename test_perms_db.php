<?php
require 'config/database.php';
$db = (new Database())->connect();
$stmt = $db->query('SELECT slug, label FROM permissions');
var_dump($stmt->fetchAll(PDO::FETCH_ASSOC));
