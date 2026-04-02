<?php

class AddIsAccountableToTithes {
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
        if ($this->columnExists($db, 'tithes', 'is_accountable')) {
            return;
        }

        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        $db->exec("ALTER TABLE tithes ADD COLUMN is_accountable " . ($driver === 'mysql' ? 'TINYINT(1) NOT NULL DEFAULT 1' : 'INTEGER NOT NULL DEFAULT 1'));
    }

    public function down($db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver !== 'mysql' || !$this->columnExists($db, 'tithes', 'is_accountable')) {
            return;
        }

        $db->exec("ALTER TABLE tithes DROP COLUMN is_accountable");
    }
}
