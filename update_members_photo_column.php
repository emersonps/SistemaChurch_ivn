<?php
// update_members_photo_column.php

ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config/database.php';

echo "Verificando tabela 'members' para adicionar coluna 'photo'...\n";

try {
    $db = (new Database())->connect();
    
    $columns = $db->query("PRAGMA table_info(members)")->fetchAll(PDO::FETCH_ASSOC);
    $existingCols = array_column($columns, 'name');
    
    if (!in_array('photo', $existingCols)) {
        echo "Adicionando coluna 'photo'...\n";
        $db->exec("ALTER TABLE members ADD COLUMN photo TEXT");
        echo "Coluna 'photo' adicionada com sucesso.\n";
    } else {
        echo "Coluna 'photo' já existe.\n";
    }
    
    echo "Atualização concluída!\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
