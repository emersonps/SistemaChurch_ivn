<?php
require_once __DIR__ . '/config/database.php';

$db = (new Database())->connect();
$stmt = $db->query("SELECT id, title, type, event_date, status, recurring_days FROM events WHERE title LIKE '%NEGUE-SE%'");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($events);
