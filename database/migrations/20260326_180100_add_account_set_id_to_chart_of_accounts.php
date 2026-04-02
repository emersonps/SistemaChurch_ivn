<?php
class AddAccountSetIdToChartOfAccounts {
    public function up(PDO $db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $db->exec("ALTER TABLE chart_of_accounts ADD COLUMN account_set_id INT NULL AFTER id");
            $defaultId = (int)$db->query("SELECT id FROM account_sets WHERE is_default = 1 LIMIT 1")->fetchColumn();
            if ($defaultId <= 0) {
                $defaultId = (int)$db->query("SELECT id FROM account_sets LIMIT 1")->fetchColumn();
            }
            if ($defaultId > 0) {
                $stmt = $db->prepare("UPDATE chart_of_accounts SET account_set_id = ? WHERE account_set_id IS NULL");
                $stmt->execute([$defaultId]);
            }
            try {
                $db->exec("ALTER TABLE chart_of_accounts MODIFY account_set_id INT NOT NULL");
                $db->exec("ALTER TABLE chart_of_accounts ADD CONSTRAINT fk_chart_accounts_set FOREIGN KEY (account_set_id) REFERENCES account_sets(id) ON DELETE RESTRICT");
            } catch (Exception $e) {}
            try {
                $db->exec("CREATE UNIQUE INDEX idx_code_per_set ON chart_of_accounts(account_set_id, code)");
            } catch (Exception $e) {}
        } else {
            $db->exec("ALTER TABLE chart_of_accounts ADD COLUMN account_set_id INTEGER");
            $defaultId = (int)$db->query("SELECT id FROM account_sets WHERE is_default = 1 LIMIT 1")->fetchColumn();
            if ($defaultId <= 0) {
                $defaultId = (int)$db->query("SELECT id FROM account_sets LIMIT 1")->fetchColumn();
            }
            if ($defaultId > 0) {
                $stmt = $db->prepare("UPDATE chart_of_accounts SET account_set_id = ? WHERE account_set_id IS NULL");
                $stmt->execute([$defaultId]);
            }
        }
    }
    public function down(PDO $db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            try { $db->exec("ALTER TABLE chart_of_accounts DROP FOREIGN KEY fk_chart_accounts_set"); } catch (Exception $e) {}
            try { $db->exec("DROP INDEX idx_code_per_set ON chart_of_accounts"); } catch (Exception $e) {}
            $db->exec("ALTER TABLE chart_of_accounts DROP COLUMN account_set_id");
        } else {
            // SQLite down migration omitted
        }
    }
}
