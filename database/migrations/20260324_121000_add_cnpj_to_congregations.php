<?php
// Migration: Add CNPJ column to congregations

class AddCnpjToCongregations {
    public function up(PDO $db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        // Check if column already exists
        $hasColumn = false;
        if ($driver === 'mysql') {
            $stmt = $db->query("SHOW COLUMNS FROM congregations LIKE 'cnpj'");
            $hasColumn = (bool)$stmt->fetch();
        } else {
            // sqlite / others
            $stmt = $db->query("PRAGMA table_info(congregations)");
            $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $col) {
                if (strtolower($col['name']) === 'cnpj') {
                    $hasColumn = true;
                    break;
                }
            }
        }

        if (!$hasColumn) {
            $sql = "ALTER TABLE congregations ADD COLUMN cnpj VARCHAR(20) NULL";
            $db->exec($sql);
        }
    }

    public function down(PDO $db) {
        // Optional: remove column (not trivial on SQLite). We'll implement for MySQL only.
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $db->exec("ALTER TABLE congregations DROP COLUMN cnpj");
        }
        // For SQLite, dropping a column would require table recreation; skipping.
    }
}
