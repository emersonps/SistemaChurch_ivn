<?php
require 'config/database.php';
$db = (new Database())->connect();
$stmt = $db->query("SHOW TABLES LIKE 'site_settings'");
var_dump($stmt->fetchAll());
$stmt = $db->query("SELECT * FROM migrations ORDER BY id DESC LIMIT 5");
var_dump($stmt->fetchAll(PDO::FETCH_ASSOC));
