<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();
$pdo->query("UPDATE events SET status = 'active'");
echo "Eventos atualizados para 'active'.\n";
