<?php
// public/db_update_congregation_schedule.php

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Atualização da Tabela de Congregações</h1>";
echo "<pre>";

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->connect();
    echo "Conectado ao banco de dados com sucesso.\n\n";

    // Atualizar Tabela Congregations (Adicionar service_schedule)
    echo "Verificando tabela 'congregations'...\n";
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
    
    echo "\n\nAtualização concluída com sucesso!";

} catch (Exception $e) {
    echo "ERRO CRÍTICO: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "</pre>";
echo '<br><a href="/">Voltar para a Home</a>';
