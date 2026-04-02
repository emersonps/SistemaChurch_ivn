<?php
// database/migrations/add_banner_to_events.php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = (new Database())->connect();
    
    // Check if column exists
    $cols = $db->query("PRAGMA table_info(events)")->fetchAll();
    $hasColumn = false;
    foreach ($cols as $col) {
        if ($col['name'] == 'banner_path') {
            $hasColumn = true;
            break;
        }
    }
    
    if (!$hasColumn) {
        $db->exec("ALTER TABLE events ADD COLUMN banner_path TEXT DEFAULT NULL");
        echo "Coluna 'banner_path' adicionada à tabela 'events'.\n";
    } else {
        echo "Coluna 'banner_path' já existe.\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
