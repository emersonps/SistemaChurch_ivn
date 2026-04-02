<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/database/MigrationRunner.php';

$runner = new MigrationRunner();
$log = $runner->run();
foreach ($log as $line) {
    echo $line . PHP_EOL;
}
