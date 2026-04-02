<?php
class AlterFinanceTablesForAccounts {
    public function up($db) {
        // Add columns to tithes
        try {
            $db->exec("ALTER TABLE tithes ADD COLUMN bank_account_id INT NULL");
            $db->exec("ALTER TABLE tithes ADD COLUMN chart_account_id INT NULL");
            
            $db->exec("ALTER TABLE tithes ADD CONSTRAINT fk_tithes_bank FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id) ON DELETE SET NULL");
            $db->exec("ALTER TABLE tithes ADD CONSTRAINT fk_tithes_chart FOREIGN KEY (chart_account_id) REFERENCES chart_of_accounts(id) ON DELETE SET NULL");
        } catch(PDOException $e) {
            // Columns might already exist
        }

        // Add columns to expenses
        try {
            $db->exec("ALTER TABLE expenses ADD COLUMN bank_account_id INT NULL");
            $db->exec("ALTER TABLE expenses ADD COLUMN chart_account_id INT NULL");
            
            $db->exec("ALTER TABLE expenses ADD CONSTRAINT fk_expenses_bank FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id) ON DELETE SET NULL");
            $db->exec("ALTER TABLE expenses ADD CONSTRAINT fk_expenses_chart FOREIGN KEY (chart_account_id) REFERENCES chart_of_accounts(id) ON DELETE SET NULL");
        } catch(PDOException $e) {
            // Columns might already exist
        }
    }

    public function down($db) {
        try {
            $db->exec("ALTER TABLE tithes DROP FOREIGN KEY fk_tithes_bank");
            $db->exec("ALTER TABLE tithes DROP FOREIGN KEY fk_tithes_chart");
            $db->exec("ALTER TABLE tithes DROP COLUMN bank_account_id");
            $db->exec("ALTER TABLE tithes DROP COLUMN chart_account_id");
            
            $db->exec("ALTER TABLE expenses DROP FOREIGN KEY fk_expenses_bank");
            $db->exec("ALTER TABLE expenses DROP FOREIGN KEY fk_expenses_chart");
            $db->exec("ALTER TABLE expenses DROP COLUMN bank_account_id");
            $db->exec("ALTER TABLE expenses DROP COLUMN chart_account_id");
        } catch(PDOException $e) {
            // Ignore
        }
    }
}
