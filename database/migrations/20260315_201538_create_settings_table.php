<?php
class CreateSettingsTable {
    public function up($pdo) {
        $sql = "CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql = "CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) NOT NULL UNIQUE,
                setting_value TEXT,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        }

        $pdo->exec($sql);

        // Inserir chaves padrão para WhatsApp API
        $defaults = [
            ['whatsapp_api_url', ''], // ex: http://localhost:8080 ou https://sua-api.render.com
            ['whatsapp_api_instance', ''], // ex: impvc
            ['whatsapp_api_token', ''], // Global API Key ou Instance Token
        ];

        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        
        foreach ($defaults as $d) {
            try {
                $stmt->execute($d);
            } catch (Exception $e) {
                // Ignora duplicata
            }
        }
    }

    public function down($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS settings");
    }
}
