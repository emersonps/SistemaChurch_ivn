<?php
// database/migrations/add_spiritual_status_to_members.php

require_once __DIR__ . '/../../config/database.php';

echo "Iniciando migração de status espiritual na tabela members...\n";

try {
    $db = (new Database())->connect();
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    
    // Obter colunas existentes
    if ($driver === 'sqlite') {
        $columns = $db->query("PRAGMA table_info(members)")->fetchAll(PDO::FETCH_ASSOC);
        $existingCols = array_column($columns, 'name');
    } else {
        $columns = $db->query("SHOW COLUMNS FROM members")->fetchAll(PDO::FETCH_COLUMN);
        $existingCols = $columns;
    }

    // 1. is_new_convert (Novo Convertido)
    if (!in_array('is_new_convert', $existingCols)) {
        echo "Adicionando coluna 'is_new_convert'...\n";
        $type = ($driver === 'sqlite') ? 'INTEGER DEFAULT 0' : 'TINYINT(1) DEFAULT 0';
        $db->exec("ALTER TABLE members ADD COLUMN is_new_convert $type");
    } else {
        echo "Coluna 'is_new_convert' já existe.\n";
    }

    // 2. accepted_jesus_at (Data de Aceitação)
    if (!in_array('accepted_jesus_at', $existingCols)) {
        echo "Adicionando coluna 'accepted_jesus_at'...\n";
        $type = 'DATE DEFAULT NULL';
        $db->exec("ALTER TABLE members ADD COLUMN accepted_jesus_at $type");
    } else {
        echo "Coluna 'accepted_jesus_at' já existe.\n";
    }

    // 3. reconciled_at (Data de Reconciliação)
    if (!in_array('reconciled_at', $existingCols)) {
        echo "Adicionando coluna 'reconciled_at'...\n";
        $type = 'DATE DEFAULT NULL';
        $db->exec("ALTER TABLE members ADD COLUMN reconciled_at $type");
    } else {
        echo "Coluna 'reconciled_at' já existe.\n";
    }

    echo "Migração concluída com sucesso!\n";

} catch (Exception $e) {
    echo "Erro na migração: " . $e->getMessage() . "\n";
}
