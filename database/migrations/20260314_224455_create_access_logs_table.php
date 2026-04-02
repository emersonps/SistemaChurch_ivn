<?php
class CreateAccessLogsTable {
    public function up($pdo) {
        // Tabela para registrar acessos (quem, quando, onde, IP)
        $sql = "
            CREATE TABLE IF NOT EXISTS access_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NULL,
                user_name VARCHAR(100) NULL,
                user_type VARCHAR(20) NULL, -- 'admin', 'member', 'visitor'
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT NULL,
                requested_url TEXT NOT NULL,
                request_method VARCHAR(10) NOT NULL,
                session_id VARCHAR(100) NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_activity DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            -- Tabela para log de ações/auditoria (o que foi feito: insert, update, delete)
            CREATE TABLE IF NOT EXISTS audit_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NULL,
                user_name VARCHAR(100) NULL,
                action VARCHAR(50) NOT NULL, -- 'create', 'update', 'delete', 'login', 'logout'
                table_name VARCHAR(50) NULL,
                record_id INTEGER NULL,
                old_values TEXT NULL, -- JSON
                new_values TEXT NULL, -- JSON
                ip_address VARCHAR(45) NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ";
        
        // Adaptação para MySQL se necessário (Auto increment)
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql = str_replace("INTEGER PRIMARY KEY AUTOINCREMENT", "INT AUTO_INCREMENT PRIMARY KEY", $sql);
            $sql = str_replace("DATETIME DEFAULT CURRENT_TIMESTAMP", "TIMESTAMP DEFAULT CURRENT_TIMESTAMP", $sql);
        }
        
        $pdo->exec($sql);
    }

    public function down($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS audit_logs");
        $pdo->exec("DROP TABLE IF EXISTS access_logs");
    }
}
