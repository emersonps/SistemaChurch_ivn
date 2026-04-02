<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();
$res = [];
foreach(['service_reports', 'signatures', 'service_people_actions'] as $t) {
    try {
        $res[$t] = array_column($pdo->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_ASSOC), 'Type', 'Field');
    } catch(Exception $e) {
        $res[$t] = $e->getMessage();
    }
}
echo json_encode($res, JSON_PRETTY_PRINT);
