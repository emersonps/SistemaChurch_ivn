<?php

class AddEventDatesToEvents {
    public function up($pdo) {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        $hasColumn = false;
        if ($driver === 'sqlite') {
            $cols = $pdo->query("PRAGMA table_info(events)")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $c) {
                if (($c['name'] ?? null) === 'event_dates') {
                    $hasColumn = true;
                    break;
                }
            }
        } else {
            $stmt = $pdo->prepare("SHOW COLUMNS FROM `events` LIKE ?");
            $stmt->execute(['event_dates']);
            $hasColumn = (bool)$stmt->fetch();
        }

        if ($hasColumn) {
            return;
        }

        $pdo->exec("ALTER TABLE events ADD COLUMN event_dates TEXT NULL");
    }

    public function down($pdo) {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            try {
                $pdo->exec("ALTER TABLE events DROP COLUMN event_dates");
            } catch (Exception $e) {
            }
        }
    }
}
