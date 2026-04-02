<?php

class AddDueDateToSystemPayments {
    public function up(PDO $db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        $hasColumn = false;

        if ($driver === 'mysql') {
            $stmt = $db->prepare("SHOW COLUMNS FROM `system_payments` LIKE ?");
            $stmt->execute(['due_date']);
            $hasColumn = (bool)$stmt->fetch();
        } else {
            $stmt = $db->query("PRAGMA table_info(system_payments)");
            $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $col) {
                if (($col['name'] ?? '') === 'due_date') {
                    $hasColumn = true;
                    break;
                }
            }
        }

        if (!$hasColumn) {
            if ($driver === 'mysql') {
                $db->exec("ALTER TABLE `system_payments` ADD COLUMN `due_date` DATETIME NULL AFTER `amount`");
            } else {
                $db->exec("ALTER TABLE system_payments ADD COLUMN due_date DATETIME NULL");
            }
        }

        if ($driver === 'mysql') {
            $db->exec("UPDATE system_payments SET due_date = payment_date WHERE due_date IS NULL AND status <> 'paid' AND payment_date IS NOT NULL");
        } else {
            $db->exec("UPDATE system_payments SET due_date = payment_date WHERE due_date IS NULL AND status <> 'paid' AND payment_date IS NOT NULL");
        }
    }
}
