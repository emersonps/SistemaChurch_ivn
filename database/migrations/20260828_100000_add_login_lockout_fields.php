<?php
// database/migrations/20260828_100000_add_login_lockout_fields.php
//
// Protecao contra forca bruta: bloqueio temporario por conta (users e
// members) apos N tentativas de senha erradas seguidas, mais uma tabela
// de log de falhas por IP pra detectar/bloquear ataques que giram entre
// varias contas a partir do mesmo IP (credential stuffing).

class AddLoginLockoutFields {
    public function up($db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        foreach (['users', 'members'] as $table) {
            if ($driver === 'mysql') {
                $exists = $db->query("SHOW COLUMNS FROM $table LIKE 'failed_login_attempts'")->fetchAll(PDO::FETCH_ASSOC);
                if (empty($exists)) {
                    $db->exec("ALTER TABLE $table ADD COLUMN failed_login_attempts INT NOT NULL DEFAULT 0");
                }
                $exists2 = $db->query("SHOW COLUMNS FROM $table LIKE 'locked_until'")->fetchAll(PDO::FETCH_ASSOC);
                if (empty($exists2)) {
                    $db->exec("ALTER TABLE $table ADD COLUMN locked_until DATETIME NULL");
                }
            } else {
                $cols = $db->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC);
                $existingNames = array_column($cols, 'name');
                if (!in_array('failed_login_attempts', $existingNames, true)) {
                    $db->exec("ALTER TABLE $table ADD COLUMN failed_login_attempts INTEGER NOT NULL DEFAULT 0");
                }
                if (!in_array('locked_until', $existingNames, true)) {
                    $db->exec("ALTER TABLE $table ADD COLUMN locked_until TEXT NULL");
                }
            }
        }

        if ($driver === 'mysql') {
            $db->exec("
                CREATE TABLE IF NOT EXISTS login_failure_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    ip_address VARCHAR(45) NOT NULL,
                    login_type VARCHAR(20) NOT NULL,
                    identifier VARCHAR(255) NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_login_failure_ip_time (ip_address, created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } else {
            $db->exec("
                CREATE TABLE IF NOT EXISTS login_failure_log (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    ip_address TEXT NOT NULL,
                    login_type TEXT NOT NULL,
                    identifier TEXT NULL,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $db->exec('CREATE INDEX IF NOT EXISTS idx_login_failure_ip_time ON login_failure_log(ip_address, created_at)');
        }
    }

    public function down($db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        try {
            foreach (['users', 'members'] as $table) {
                if ($driver === 'mysql') {
                    $db->exec("ALTER TABLE $table DROP COLUMN failed_login_attempts");
                    $db->exec("ALTER TABLE $table DROP COLUMN locked_until");
                } else {
                    $db->exec("ALTER TABLE $table DROP COLUMN failed_login_attempts");
                    $db->exec("ALTER TABLE $table DROP COLUMN locked_until");
                }
            }
            $db->exec('DROP TABLE IF EXISTS login_failure_log');
        } catch (Throwable $e) {
            // Melhor esforco.
        }
    }
}
