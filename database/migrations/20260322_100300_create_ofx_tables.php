<?php
class CreateOfxTables {
    public function up($db) {
        $sql = "CREATE TABLE IF NOT EXISTS ofx_imports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            bank_account_id INT NOT NULL,
            filename VARCHAR(255) NOT NULL,
            import_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            status ENUM('pending', 'completed') DEFAULT 'pending',
            FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $db->exec($sql);
        
        $sql2 = "CREATE TABLE IF NOT EXISTS ofx_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ofx_import_id INT NOT NULL,
            bank_account_id INT NOT NULL,
            transaction_id VARCHAR(255) NOT NULL, /* FITID */
            transaction_date DATE NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            description VARCHAR(255) NOT NULL,
            type ENUM('credit', 'debit') NOT NULL,
            status ENUM('pending', 'conciliated', 'ignored') DEFAULT 'pending',
            related_tithe_id INT NULL,
            related_expense_id INT NULL,
            FOREIGN KEY (ofx_import_id) REFERENCES ofx_imports(id) ON DELETE CASCADE,
            FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id) ON DELETE CASCADE,
            FOREIGN KEY (related_tithe_id) REFERENCES tithes(id) ON DELETE SET NULL,
            FOREIGN KEY (related_expense_id) REFERENCES expenses(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $db->exec($sql2);
    }

    public function down($db) {
        $db->exec("DROP TABLE IF EXISTS ofx_transactions");
        $db->exec("DROP TABLE IF EXISTS ofx_imports");
    }
}
