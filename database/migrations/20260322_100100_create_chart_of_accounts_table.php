<?php
class CreateChartOfAccountsTable {
    public function up($db) {
        $sql = "CREATE TABLE IF NOT EXISTS chart_of_accounts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) NOT NULL,
            name VARCHAR(255) NOT NULL,
            type ENUM('asset', 'liability', 'income', 'expense') NOT NULL,
            parent_id INT NULL,
            description TEXT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_id) REFERENCES chart_of_accounts(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $db->exec($sql);
        
        // Insert standard chart of accounts
        $stmt = $db->query("SELECT COUNT(*) FROM chart_of_accounts");
        if ($stmt->fetchColumn() == 0) {
            $accounts = [
                ['1', 'Ativo', 'asset', null],
                ['1.1', 'Ativo Circulante', 'asset', 1],
                ['1.1.1', 'Caixa e Equivalentes de Caixa', 'asset', 2],
                ['2', 'Passivo', 'liability', null],
                ['2.1', 'Passivo Circulante', 'liability', 4],
                ['2.1.1', 'Contas a Pagar', 'liability', 5],
                ['3', 'Receitas', 'income', null],
                ['3.1', 'Receitas Ordinárias', 'income', 7],
                ['3.1.1', 'Dízimos', 'income', 8],
                ['3.1.2', 'Ofertas', 'income', 8],
                ['4', 'Despesas', 'expense', null],
                ['4.1', 'Despesas Operacionais', 'expense', 11],
                ['4.1.1', 'Despesas com Pessoal', 'expense', 12],
                ['4.1.2', 'Despesas Administrativas (Água, Luz, Internet)', 'expense', 12]
            ];
            
            $insert = $db->prepare("INSERT INTO chart_of_accounts (code, name, type, parent_id) VALUES (?, ?, ?, ?)");
            foreach ($accounts as $acc) {
                $insert->execute($acc);
            }
        }
    }

    public function down($db) {
        $db->exec("DROP TABLE IF EXISTS chart_of_accounts");
    }
}
