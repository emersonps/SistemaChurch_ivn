<?php
class CreateAccountOpeningBalances {
    public function up(PDO $db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $db->exec("CREATE TABLE IF NOT EXISTS account_opening_balances (
                id INT AUTO_INCREMENT PRIMARY KEY,
                account_set_id INT NOT NULL,
                account_id INT NOT NULL,
                balance DECIMAL(15,2) NOT NULL,
                balance_date DATE NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (account_set_id) REFERENCES account_sets(id) ON DELETE RESTRICT,
                FOREIGN KEY (account_id) REFERENCES chart_of_accounts(id) ON DELETE CASCADE,
                INDEX idx_account (account_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } else {
            $db->exec("CREATE TABLE IF NOT EXISTS account_opening_balances (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                account_set_id INTEGER NOT NULL,
                account_id INTEGER NOT NULL,
                balance REAL NOT NULL,
                balance_date DATE NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
        }
    }
    public function down(PDO $db) {
        $db->exec("DROP TABLE IF EXISTS account_opening_balances");
    }
}
