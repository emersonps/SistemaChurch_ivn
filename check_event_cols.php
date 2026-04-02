<?php
require 'config/database.php';
$db = (new Database())->connect();
$cols = $db->query('PRAGMA table_info(events)')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo $c['name'] . "\n";
}
