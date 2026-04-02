<?php
class CreateAccountSets {
    public function up(PDO $db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $db->exec("CREATE TABLE IF NOT EXISTS account_sets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                description TEXT NULL,
                is_default TINYINT(1) NOT NULL DEFAULT 0,
                active TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } else {
            $db->exec("CREATE TABLE IF NOT EXISTS account_sets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT,
                is_default INTEGER NOT NULL DEFAULT 0,
                active INTEGER NOT NULL DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
        }
        $count = (int)$db->query("SELECT COUNT(*) FROM account_sets")->fetchColumn();
        if ($count === 0) {
            $stmt = $db->prepare("INSERT INTO account_sets (name, description, is_default, active) VALUES (?, ?, ?, ?)");
            $stmt->execute(['Plano Padrão', 'Conjunto inicial de contas', 1, 1]);
        }
    }
    public function down(PDO $db) {
        $db->exec("DROP TABLE IF EXISTS account_sets");
    }
}
