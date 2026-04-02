<?php
require 'config/database.php';
$db = (new Database())->connect();
$events = $db->query('SELECT id, title, type, location FROM events')->fetchAll(PDO::FETCH_ASSOC);
foreach($events as $ev) {
    echo "ID: " . $ev['id'] . " | Title: " . $ev['title'] . " | Type: " . $ev['type'] . " | Location: [" . $ev['location'] . "]\n";
}
