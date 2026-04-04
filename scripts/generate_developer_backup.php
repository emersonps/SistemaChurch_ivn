<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/models/DatabaseBackupManager.php';

try {
    $manager = new DatabaseBackupManager();
    $backup = $manager->ensureWeeklyBackup();

    if ($backup) {
        echo 'Backup gerado: ' . $backup['filename'] . PHP_EOL;
    } else {
        echo 'Nenhum backup novo foi necessário nesta execução.' . PHP_EOL;
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'Erro ao gerar backup: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
