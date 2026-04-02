<?php
class CreateBankAccountsTable {
    public function up($db) {
        $sql = "CREATE TABLE IF NOT EXISTS bank_accounts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            type ENUM('caixa', 'conta_corrente', 'poupanca', 'investimento') DEFAULT 'conta_corrente',
            bank_name VARCHAR(255) NULL,
            agency VARCHAR(50) NULL,
            account_number VARCHAR(50) NULL,
            initial_balance DECIMAL(10,2) DEFAULT 0.00,
            current_balance DECIMAL(10,2) DEFAULT 0.00,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $db->exec($sql);
        
        // Add default cash account
        $stmt = $db->query("SELECT COUNT(*) FROM bank_accounts");
        if ($stmt->fetchColumn() == 0) {
            $db->exec("INSERT INTO bank_accounts (name, type, initial_balance, current_balance) VALUES ('Caixa Geral', 'caixa', 0, 0)");
        }
    }

    public function down($db) {
        $db->exec("DROP TABLE IF EXISTS bank_accounts");
    }
}
