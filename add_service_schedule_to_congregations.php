<?php
// add_service_schedule_to_congregations.php

ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config/database.php';

echo "Iniciando atualização da tabela congregations...\n";

try {
    $db = (new Database())->connect();
    
    // Verificar se a coluna já existe
    $columns = $db->query("PRAGMA table_info(congregations)")->fetchAll(PDO::FETCH_ASSOC);
    $hasColumn = false;
    foreach ($columns as $col) {
        if ($col['name'] === 'service_schedule') {
            $hasColumn = true;
            break;
        }
    }
    
    if (!$hasColumn) {
        echo "Adicionando coluna 'service_schedule'...\n";
        $db->exec("ALTER TABLE congregations ADD COLUMN service_schedule TEXT");
        echo "Coluna 'service_schedule' adicionada com sucesso!\n";
    } else {
        echo "Coluna 'service_schedule' já existe.\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

echo "Concluído.\n";
