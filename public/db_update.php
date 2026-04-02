<?php
// public/db_update.php

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Atualização do Banco de Dados</h1>";
echo "<pre>";

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->connect();
    echo "Conectado ao banco de dados com sucesso.\n\n";

    // 1. Atualizar Tabela Events (Adicionar status)
    echo "Verificando tabela 'events'...\n";
    $columns = $db->query("PRAGMA table_info(events)")->fetchAll(PDO::FETCH_ASSOC);
    $hasStatus = false;
    foreach ($columns as $col) {
        if ($col['name'] === 'status') {
            $hasStatus = true;
            break;
        }
    }

    if (!$hasStatus) {
        echo "Adicionando coluna 'status' na tabela 'events'...\n";
        $db->exec("ALTER TABLE events ADD COLUMN status TEXT DEFAULT 'active'");
        echo "Coluna 'status' adicionada!\n";
    } else {
        echo "Coluna 'status' já existe.\n";
    }

    // 2. Atualizar Tabela Congregations (Adicionar photo, zip_code, city, state)
    echo "\nVerificando tabela 'congregations'...\n";
    $columns = $db->query("PRAGMA table_info(congregations)")->fetchAll(PDO::FETCH_ASSOC);
    $existingCols = array_column($columns, 'name');
    
    $newCols = [
        'photo' => 'TEXT',
        'zip_code' => 'TEXT',
        'city' => 'TEXT',
        'state' => 'TEXT'
    ];

    foreach ($newCols as $colName => $colType) {
        if (!in_array($colName, $existingCols)) {
            echo "Adicionando coluna '$colName' na tabela 'congregations'...\n";
            $db->exec("ALTER TABLE congregations ADD COLUMN $colName $colType");
            echo "Coluna '$colName' adicionada!\n";
        } else {
            echo "Coluna '$colName' já existe.\n";
        }
    }
    
    // 3. Verificar/Corrigir tipos de congregação
    echo "\nVerificando tipos de congregação...\n";
    // Atualizar registros com type NULL para 'congregation'
    $count = $db->exec("UPDATE congregations SET type = 'congregation' WHERE type IS NULL OR type = ''");
    if ($count > 0) {
        echo "Atualizados $count registros de congregação sem tipo definido.\n";
    } else {
        echo "Todos os registros de congregação parecem ter tipo definido.\n";
    }

    echo "\n\nAtualização concluída com sucesso!";

} catch (Exception $e) {
    echo "ERRO CRÍTICO: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "</pre>";
echo '<br><a href="/">Voltar para a Home</a>';
