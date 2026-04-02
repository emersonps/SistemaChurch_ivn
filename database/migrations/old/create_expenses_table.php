<?php
// database/migrations/create_expenses_table.php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = (new Database())->connect();
    
    // Check if table exists
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='expenses'")->fetchAll();
    
    if (count($tables) == 0) {
        $sql = "CREATE TABLE expenses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            description TEXT NOT NULL,
            amount REAL NOT NULL,
            expense_date DATE NOT NULL,
            category TEXT, -- e.g., Manutenção, Eventos, Ajuda de Custo
            congregation_id INTEGER,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (congregation_id) REFERENCES congregations(id)
        )";
        
        $db->exec($sql);
        echo "Tabela 'expenses' criada com sucesso.\n";
    } else {
        echo "Tabela 'expenses' já existe.\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
