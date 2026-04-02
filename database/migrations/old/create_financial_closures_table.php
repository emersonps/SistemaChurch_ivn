<?php
// database/migrations/create_financial_closures_table.php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = (new Database())->connect();
    
    // Check if table exists
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='financial_closures'")->fetchAll();
    
    if (count($tables) == 0) {
        $sql = "CREATE TABLE financial_closures (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            congregation_id INTEGER,
            type TEXT NOT NULL, -- 'Mensal' or 'Anual'
            period TEXT NOT NULL, -- '2023-10' for monthly, '2023' for annual
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            total_entries REAL NOT NULL,
            total_tithes REAL NOT NULL,
            total_offerings REAL NOT NULL,
            total_expenses REAL NOT NULL,
            balance REAL NOT NULL,
            previous_balance REAL DEFAULT 0, -- Saldo anterior (do fechamento passado)
            final_balance REAL NOT NULL, -- Saldo atual + Saldo anterior
            status TEXT DEFAULT 'Fechado', -- 'Fechado', 'Rascunho' (se quiser permitir edição antes de travar)
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            created_by INTEGER, -- User ID
            FOREIGN KEY (congregation_id) REFERENCES congregations(id),
            FOREIGN KEY (created_by) REFERENCES users(id)
        )";
        
        $db->exec($sql);
        echo "Tabela 'financial_closures' criada com sucesso.\n";
    } else {
        echo "Tabela 'financial_closures' já existe.\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
