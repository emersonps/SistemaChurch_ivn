<?php
// database/migrations/add_session_token_to_users.php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = (new Database())->connect();
    
    // Check if column exists
    $cols = $db->query("PRAGMA table_info(users)")->fetchAll();
    $hasColumn = false;
    foreach ($cols as $col) {
        if ($col['name'] == 'session_token') {
            $hasColumn = true;
            break;
        }
    }
    
    if (!$hasColumn) {
        $db->exec("ALTER TABLE users ADD COLUMN session_token TEXT DEFAULT NULL");
        echo "Coluna 'session_token' adicionada à tabela 'users'.\n";
    } else {
        echo "Coluna 'session_token' já existe.\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
