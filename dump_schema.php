<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();

$tables = [
    'members', 'tithes', 'system_payments', 
    'ebd_classes', 'ebd_students', 'ebd_lessons', 'ebd_attendance',
    'studies', 'gallery', 'events', 'event_attendance'
];

$schema = [];
foreach ($tables as $t) {
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_ASSOC);
        $schema[$t] = array_column($cols, 'Type', 'Field');
    } catch (Exception $e) {
        $schema[$t] = "Table not found: " . $e->getMessage();
    }
}
file_put_contents('schema_dump.json', json_encode($schema, JSON_PRETTY_PRINT));
