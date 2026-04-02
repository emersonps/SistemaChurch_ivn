<?php
class AddIndexesToAccessLogs {
    public function up($pdo) {
        // Índices para melhorar a performance de busca e limpeza
        // MySQL não suporta IF NOT EXISTS em CREATE INDEX nas versões mais antigas (antes 8.0)
        // Então usamos uma query segura que funciona em ambos (MySQL e SQLite) com try/catch silencioso para MySQL
        
        $indices = [
            "CREATE INDEX idx_access_logs_session ON access_logs (session_id)",
            "CREATE INDEX idx_access_logs_activity ON access_logs (last_activity)",
            "CREATE INDEX idx_access_logs_created ON access_logs (created_at)",
            "CREATE INDEX idx_access_logs_type ON access_logs (user_type)"
        ];

        foreach ($indices as $sql) {
            try {
                $pdo->exec($sql);
            } catch (PDOException $e) {
                // Ignora erro se índice já existir (MySQL Error 1061: Duplicate key name)
                // Ou se a sintaxe não for suportada (mas CREATE INDEX padrão é bem universal)
                if (strpos($e->getMessage(), 'Duplicate key name') === false && strpos($e->getMessage(), 'already exists') === false) {
                    // Se for outro erro, lançamos
                    // Mas para garantir deploy seguro, apenas logamos
                    error_log("Aviso ao criar índice: " . $e->getMessage());
                }
            }
        }
    }

    public function down($pdo) {
        // MySQL DROP INDEX syntax: DROP INDEX index_name ON table_name
        // SQLite DROP INDEX syntax: DROP INDEX index_name
        
        $isMysql = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql');
        
        $indices = [
            'idx_access_logs_session',
            'idx_access_logs_activity',
            'idx_access_logs_created',
            'idx_access_logs_type'
        ];

        foreach ($indices as $index) {
            try {
                if ($isMysql) {
                    $pdo->exec("DROP INDEX $index ON access_logs");
                } else {
                    $pdo->exec("DROP INDEX IF EXISTS $index");
                }
            } catch (PDOException $e) {
                // Ignora se não existir
            }
        }
    }
}
