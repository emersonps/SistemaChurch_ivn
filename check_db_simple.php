<?php
require_once __DIR__ . '/config/database.php';
$db = (new Database())->connect();
echo "Columns:\n";
print_r($db->query("DESCRIBE `groups`")->fetchAll(PDO::FETCH_ASSOC));
