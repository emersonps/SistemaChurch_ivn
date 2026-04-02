<?php
$db = new PDO('sqlite:database/SistemaChurch.db');
$stmt = $db->query("SELECT * FROM system_payments ORDER BY id DESC LIMIT 5");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
?>