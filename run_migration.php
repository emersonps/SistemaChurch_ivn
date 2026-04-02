<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/database/MigrationRunner.php';

try {
    $runner = new MigrationRunner();
    $log = $runner->run();
    print_r($log);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
