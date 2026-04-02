<?php
// setup_job_titles.php

require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    
    // 1. Create Table
    $sql = "CREATE TABLE IF NOT EXISTS job_titles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($sql);
    echo "Tabela 'job_titles' verificada/criada.\n";
    
    // 2. Insert Defaults
    $defaults = [
        'Membro',
        'Auxiliar',
        'Diácono',
        'Presbítero',
        'Evangelista',
        'Pastor',
        'Missionário(a)',
        'Cooperador'
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO job_titles (name) VALUES (?)");
    foreach ($defaults as $title) {
        $stmt->execute([$title]);
    }
    echo "Cargos padrão inseridos (se não existiam).\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
