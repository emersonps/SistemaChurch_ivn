<?php

// Escalas Litúrgicas: modelos reutilizáveis (papéis ativos por igreja/congregação)
// + escalas geradas a partir deles (uma linha por culto/data).

class CreateLiturgySchedules {
    public function up($db) {
        $isMysql = $db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';

        if ($isMysql) {
            $db->exec("
                CREATE TABLE IF NOT EXISTS liturgy_schedule_templates (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    congregation_id INT NULL,
                    roles_config TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            $db->exec("
                CREATE TABLE IF NOT EXISTS liturgy_schedules (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    template_id INT NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    congregation_id INT NULL,
                    period_type VARCHAR(20) NOT NULL DEFAULT 'monthly',
                    reference_month VARCHAR(7) NULL,
                    notes TEXT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            $db->exec("
                CREATE TABLE IF NOT EXISTS liturgy_schedule_entries (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    schedule_id INT NOT NULL,
                    service_date DATE NOT NULL,
                    service_time TIME NULL,
                    service_label VARCHAR(255) NULL,
                    values_json TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_lse_schedule (schedule_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } else {
            $db->exec("
                CREATE TABLE IF NOT EXISTS liturgy_schedule_templates (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    congregation_id INTEGER NULL,
                    roles_config TEXT NOT NULL,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");

            $db->exec("
                CREATE TABLE IF NOT EXISTS liturgy_schedules (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    template_id INTEGER NOT NULL,
                    title TEXT NOT NULL,
                    congregation_id INTEGER NULL,
                    period_type TEXT NOT NULL DEFAULT 'monthly',
                    reference_month TEXT NULL,
                    notes TEXT NULL,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");

            $db->exec("
                CREATE TABLE IF NOT EXISTS liturgy_schedule_entries (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    schedule_id INTEGER NOT NULL,
                    service_date TEXT NOT NULL,
                    service_time TEXT NULL,
                    service_label TEXT NULL,
                    values_json TEXT NOT NULL,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_lse_schedule ON liturgy_schedule_entries (schedule_id)");
        }

        $stmt = $db->prepare('SELECT id FROM permissions WHERE slug = ?');
        foreach ([
            ['liturgy_schedules.view', 'Ver Escalas Litúrgicas', 'Visualizar modelos e escalas de culto'],
            ['liturgy_schedules.manage', 'Gerenciar Escalas Litúrgicas', 'Criar, editar e excluir modelos e escalas de culto'],
        ] as $perm) {
            $stmt->execute([$perm[0]]);
            if (!$stmt->fetchColumn()) {
                $db->prepare('INSERT INTO permissions (slug, label, description) VALUES (?, ?, ?)')->execute($perm);
            }
        }
    }

    public function down($db) {
        $db->exec('DROP TABLE IF EXISTS liturgy_schedule_entries');
        $db->exec('DROP TABLE IF EXISTS liturgy_schedules');
        $db->exec('DROP TABLE IF EXISTS liturgy_schedule_templates');
    }
}
