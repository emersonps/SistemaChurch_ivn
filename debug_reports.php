<?php
require_once __DIR__ . '/config/database.php';

$db = (new Database())->connect();
$stmt = $db->query("SELECT spa.action_type, COUNT(*) as total FROM service_people_actions spa JOIN service_reports sr ON spa.service_report_id = sr.id GROUP BY spa.action_type");
$actions = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($actions);

$stmt2 = $db->query("SELECT * FROM service_reports LIMIT 1");
$sr = $stmt2->fetch(PDO::FETCH_ASSOC);
print_r($sr);
