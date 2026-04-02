<?php
// setup_event_attendance.php

require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    
    // Create Table
    $sql = "CREATE TABLE IF NOT EXISTS event_attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        member_id INT NOT NULL,
        scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
        UNIQUE KEY unique_attendance (event_id, member_id)
    )";
    $db->exec($sql);
    echo "Tabela 'event_attendance' criada com sucesso.\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
