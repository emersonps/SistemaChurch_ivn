<?php

class CreateChartAccountNatures {
    private function tableExists($db, $table) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            $stmt = $db->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            return (bool)$stmt->fetchColumn();
        }

        $stmt = $db->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name = ?");
        $stmt->execute([$table]);
        return (bool)$stmt->fetchColumn();
    }

    private function columnExists($db, $table, $column) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            $stmt = $db->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
            $stmt->execute([$column]);
            return (bool)$stmt->fetch();
        }

        $stmt = $db->query("PRAGMA table_info($table)");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
            if (($col['name'] ?? '') === $column) {
                return true;
            }
        }
        return false;
    }

    public function up($db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if (!$this->tableExists($db, 'chart_account_natures')) {
            if ($driver === 'mysql') {
                $db->exec("CREATE TABLE chart_account_natures (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(150) NOT NULL,
                    base_type VARCHAR(30) NOT NULL,
                    status VARCHAR(20) NOT NULL DEFAULT 'active',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            } else {
                $db->exec("CREATE TABLE chart_account_natures (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    base_type TEXT NOT NULL,
                    status TEXT NOT NULL DEFAULT 'active',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )");
            }
        }

        if (!$this->columnExists($db, 'chart_of_accounts', 'nature_id')) {
            $db->exec("ALTER TABLE chart_of_accounts ADD COLUMN nature_id " . ($driver === 'mysql' ? 'INT NULL' : 'INTEGER NULL'));
        }

        $defaults = [
            ['Ativo (Bens e Direitos)', 'asset'],
            ['Passivo (Obrigações)', 'liability'],
            ['Receita (Entradas)', 'income'],
            ['Despesa (Saídas)', 'expense'],
        ];

        $check = $db->prepare("SELECT id FROM chart_account_natures WHERE name = ? LIMIT 1");
        $insert = $db->prepare("INSERT INTO chart_account_natures (name, base_type, status) VALUES (?, ?, 'active')");
        foreach ($defaults as $nature) {
            $check->execute([$nature[0]]);
            if (!$check->fetchColumn()) {
                $insert->execute($nature);
            }
        }

        $selectDefault = $db->prepare("SELECT id FROM chart_account_natures WHERE base_type = ? ORDER BY id ASC LIMIT 1");
        $updateAccounts = $db->prepare("UPDATE chart_of_accounts SET nature_id = ? WHERE type = ? AND nature_id IS NULL");
        foreach (['asset', 'liability', 'income', 'expense'] as $baseType) {
            $selectDefault->execute([$baseType]);
            $natureId = $selectDefault->fetchColumn();
            if ($natureId) {
                $updateAccounts->execute([$natureId, $baseType]);
            }
        }
    }

    public function down($db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql' && $this->columnExists($db, 'chart_of_accounts', 'nature_id')) {
            $db->exec("ALTER TABLE chart_of_accounts DROP COLUMN nature_id");
        }

        if ($this->tableExists($db, 'chart_account_natures')) {
            $db->exec("DROP TABLE IF EXISTS chart_account_natures");
        }
    }
}
