<?php

class AddCreatedByToStudies {
    public function up($pdo) {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        $hasColumn = false;
        if ($driver === 'sqlite') {
            $cols = $pdo->query("PRAGMA table_info(studies)")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $c) {
                if (($c['name'] ?? null) === 'created_by') {
                    $hasColumn = true;
                    break;
                }
            }
        } else {
            try {
                $cols = $pdo->query("DESCRIBE studies")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($cols as $c) {
                    if (($c['Field'] ?? null) === 'created_by') {
                        $hasColumn = true;
                        break;
                    }
                }
            } catch (Exception $e) {
                $hasColumn = false;
            }
        }

        if ($hasColumn) {
            return;
        }

        $pdo->exec("ALTER TABLE studies ADD COLUMN created_by INT NULL");
    }

    public function down($pdo) {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            try {
                $pdo->exec("ALTER TABLE studies DROP COLUMN created_by");
            } catch (Exception $e) {
            }
        }
    }
}
