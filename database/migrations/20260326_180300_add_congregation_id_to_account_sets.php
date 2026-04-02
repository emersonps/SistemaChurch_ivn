<?php
class AddCongregationIdToAccountSets {
    public function up(PDO $db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $db->exec("ALTER TABLE account_sets ADD COLUMN congregation_id INT NULL AFTER description");
            try {
                $db->exec("ALTER TABLE account_sets ADD CONSTRAINT fk_account_sets_congregation FOREIGN KEY (congregation_id) REFERENCES congregations(id) ON DELETE SET NULL");
            } catch (Exception $e) {}
        } else {
            $db->exec("ALTER TABLE account_sets ADD COLUMN congregation_id INTEGER");
        }
    }
    public function down(PDO $db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            try { $db->exec("ALTER TABLE account_sets DROP FOREIGN KEY fk_account_sets_congregation"); } catch (Exception $e) {}
            $db->exec("ALTER TABLE account_sets DROP COLUMN congregation_id");
        } else {
            // SQLite down migration omitted
        }
    }
}
