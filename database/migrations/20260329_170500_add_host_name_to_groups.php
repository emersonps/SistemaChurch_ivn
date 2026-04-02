<?php
// database/migrations/20260329_170500_add_host_name_to_groups.php

class AddHostNameToGroups {
    public function up(PDO $db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        $hasColumn = false;
        if ($driver === 'mysql') {
            $stmt = $db->prepare("SHOW COLUMNS FROM `groups` LIKE ?");
            $stmt->execute(['host_name']);
            $hasColumn = (bool)$stmt->fetch();
        } else {
            $stmt = $db->query("PRAGMA table_info(`groups`)");
            $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $col) {
                $name = isset($col['Field']) ? $col['Field'] : (isset($col['name']) ? $col['name'] : null);
                if ($name && strtolower($name) === 'host_name') {
                    $hasColumn = true;
                    break;
                }
            }
        }

        if (!$hasColumn) {
            if ($driver === 'mysql') {
                $db->exec("ALTER TABLE `groups` ADD COLUMN `host_name` VARCHAR(100) NULL AFTER `host_id`");
            } else {
                $db->exec("ALTER TABLE `groups` ADD COLUMN `host_name` TEXT NULL");
            }
        }
    }
}
