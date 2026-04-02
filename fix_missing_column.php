<?php
// fix_missing_column.php

ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config/database.php';

echo "Verificando e corrigindo tabela 'congregations'...\n";

try {
    $db = (new Database())->connect();
    
    // 1. Verificar colunas existentes
    $columns = $db->query("PRAGMA table_info(congregations)")->fetchAll(PDO::FETCH_ASSOC);
    $existingCols = [];
    foreach ($columns as $col) {
        $existingCols[] = $col['name'];
        echo " - Coluna encontrada: " . $col['name'] . "\n";
    }
    
    // 2. Adicionar 'service_schedule' se não existir
    if (!in_array('service_schedule', $existingCols)) {
        echo "Coluna 'service_schedule' NÃO encontrada. Adicionando...\n";
        $db->exec("ALTER TABLE congregations ADD COLUMN service_schedule TEXT");
        echo "SUCESSO: Coluna 'service_schedule' adicionada!\n";
    } else {
        echo "Coluna 'service_schedule' já existe.\n";
    }
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}

echo "Operação concluída.\n";
