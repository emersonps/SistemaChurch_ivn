<?php

class CreateSiteSettingsTable {
    public function up($pdo) {
        // Criar tabela de configurações do site (layout frontend)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS site_settings (
                id INT AUTO_INCREMENT PRIMARY KEY, 
                theme_id VARCHAR(50) DEFAULT 'theme-1', 
                primary_color VARCHAR(20) DEFAULT '#0d6efd', 
                secondary_color VARCHAR(20) DEFAULT '#6c757d', 
                font_family VARCHAR(100) DEFAULT 'Inter, sans-serif', 
                hero_bg_image VARCHAR(255) DEFAULT 'hero_bg_1.jpg', 
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Inserir configuração padrão se não existir
        $stmt = $pdo->query("SELECT COUNT(*) FROM site_settings");
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO site_settings (theme_id) VALUES ('theme-1')");
        }
    }

    public function down($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS site_settings");
    }
}
