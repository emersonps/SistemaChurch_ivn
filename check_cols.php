<?php
$db = new PDO('sqlite:database/SistemaChurch.db');
$cols = $db->query('PRAGMA table_info(events)')->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $c) {
    echo $c['name'] . "\n";
}
