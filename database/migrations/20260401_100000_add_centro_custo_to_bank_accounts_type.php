<?php
class AddCentroCustoToBankAccountsType {
    public function up($db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver !== 'mysql') {
            return;
        }

        $stmt = $db->query("SHOW COLUMNS FROM bank_accounts LIKE 'type'");
        $column = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if (!$column) {
            return;
        }

        $columnType = strtolower($column['Type'] ?? '');
        if (strpos($columnType, "'centro_custo'") !== false) {
            return;
        }

        $db->exec("ALTER TABLE bank_accounts MODIFY COLUMN type ENUM('caixa', 'conta_corrente', 'poupanca', 'investimento', 'centro_custo') DEFAULT 'conta_corrente'");
    }

    public function down($db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver !== 'mysql') {
            return;
        }

        $stmt = $db->query("SHOW COLUMNS FROM bank_accounts LIKE 'type'");
        $column = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if (!$column) {
            return;
        }

        $columnType = strtolower($column['Type'] ?? '');
        if (strpos($columnType, "'centro_custo'") === false) {
            return;
        }

        $db->exec("ALTER TABLE bank_accounts MODIFY COLUMN type ENUM('caixa', 'conta_corrente', 'poupanca', 'investimento') DEFAULT 'conta_corrente'");
    }
}
