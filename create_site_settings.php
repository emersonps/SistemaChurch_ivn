<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();
try {
    $pdo->query("CREATE TABLE IF NOT EXISTS site_settings (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        theme_id VARCHAR(50) DEFAULT 'theme-1', 
        primary_color VARCHAR(20) DEFAULT '#0d6efd', 
        secondary_color VARCHAR(20) DEFAULT '#6c757d', 
        font_family VARCHAR(100) DEFAULT 'Inter, sans-serif', 
        hero_bg_image VARCHAR(255) DEFAULT 'hero_bg_1.jpg', 
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM site_settings");
    if ($stmt->fetchColumn() == 0) {
        $pdo->query("INSERT INTO site_settings (theme_id) VALUES ('theme-1')");
    }
    echo "Tabela site_settings criada e inicializada com sucesso.\n";
} catch(Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
