<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();
$res = [];
foreach(['photo_albums', 'photos'] as $t) {
    $res[$t] = $pdo->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($res, JSON_PRETTY_PRINT);
