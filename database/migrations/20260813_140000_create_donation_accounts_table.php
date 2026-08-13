<?php
class CreateDonationAccountsTable {
    public function up($pdo) {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            $sql = "CREATE TABLE IF NOT EXISTS donation_accounts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                bank_name VARCHAR(255) NOT NULL,
                beneficiary_name VARCHAR(255) NOT NULL,
                beneficiary_city VARCHAR(255) NOT NULL,
                pix_key VARCHAR(255) NOT NULL,
                pix_key_type VARCHAR(20) NOT NULL DEFAULT 'aleatoria',
                display_order INT DEFAULT 0,
                status VARCHAR(20) DEFAULT 'active',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS donation_accounts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                bank_name TEXT NOT NULL,
                beneficiary_name TEXT NOT NULL,
                beneficiary_city TEXT NOT NULL,
                pix_key TEXT NOT NULL,
                pix_key_type TEXT NOT NULL DEFAULT 'aleatoria',
                display_order INTEGER DEFAULT 0,
                status TEXT DEFAULT 'active',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );";
        }

        $pdo->exec($sql);
    }

    public function down($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS donation_accounts");
    }
}
