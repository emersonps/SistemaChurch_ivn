<?php
class AddBankDetailsToDonationAccounts {
    private function hasColumn($pdo, $column) {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $stmt = $pdo->prepare("SHOW COLUMNS FROM `donation_accounts` LIKE ?");
            $stmt->execute([$column]);
            return (bool)$stmt->fetch();
        }
        $cols = $pdo->query("PRAGMA table_info(donation_accounts)")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            if (($c['name'] ?? null) === $column) {
                return true;
            }
        }
        return false;
    }

    public function up($pdo) {
        if (!$this->hasColumn($pdo, 'agency')) {
            $pdo->exec("ALTER TABLE donation_accounts ADD COLUMN agency VARCHAR(50) NULL");
        }
        if (!$this->hasColumn($pdo, 'account_number')) {
            $pdo->exec("ALTER TABLE donation_accounts ADD COLUMN account_number VARCHAR(50) NULL");
        }
    }

    public function down($pdo) {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            try { $pdo->exec("ALTER TABLE donation_accounts DROP COLUMN agency"); } catch (Exception $e) {}
            try { $pdo->exec("ALTER TABLE donation_accounts DROP COLUMN account_number"); } catch (Exception $e) {}
        }
    }
}
