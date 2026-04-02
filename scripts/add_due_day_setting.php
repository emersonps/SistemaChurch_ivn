<?php
// add_due_day_setting.php

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->connect();
    
    // Check if 'payment_due_day' exists in settings
    $stmt = $db->query("SELECT COUNT(*) FROM settings WHERE setting_key = 'payment_due_day'");
    if ($stmt->fetchColumn() == 0) {
        // Insert default due day: 5
        $db->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('payment_due_day', '5')");
        echo "Configuração 'payment_due_day' criada com valor padrão '5'.\n";
    } else {
        echo "Configuração 'payment_due_day' já existe.\n";
    }

} catch (Exception $e) {
    // If settings table doesn't exist or other error
    echo "Erro: " . $e->getMessage() . "\n";
    
    // Try to create table if not exists (fallback)
    try {
         $db->exec("CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(50) NOT NULL UNIQUE,
            setting_value TEXT,
            description VARCHAR(255),
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        echo "Tabela 'settings' verificada/criada.\n";
        
        // Retry insert
        $db->exec("INSERT IGNORE INTO settings (setting_key, setting_value, description) VALUES ('payment_due_day', '5', 'Dia de vencimento do pagamento do sistema')");
        echo "Configuração inserida após criar tabela.\n";
        
    } catch (Exception $e2) {
        echo "Erro fatal: " . $e2->getMessage() . "\n";
    }
}
