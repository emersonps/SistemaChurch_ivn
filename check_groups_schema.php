<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    
    // Check se a coluna já existe
    $stmt = $db->query("SHOW COLUMNS FROM `groups` LIKE 'host_name'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE `groups` ADD COLUMN `host_name` VARCHAR(100) NULL AFTER `host_id`");
        echo "Coluna host_name adicionada com sucesso!\n";
    } else {
        echo "Coluna host_name já existe.\n";
    }
    
} catch (Exception $e) {
    echo $e->getMessage();
}
