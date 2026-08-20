<?php
// Campanhas de arrecadação: uma campanha tem uma meta (goal_amount) e
// membros participantes com um cronograma de parcelas mensais
// (campaign_installments). Pagamentos marcados geram também um lançamento
// em `tithes` (CampaignController::payInstallment), então o dinheiro conta
// nos relatórios financeiros normais da igreja.

class CreateCampaigns {
    public function up($db) {
        $isMysql = $db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';

        if ($isMysql) {
            $db->exec("
                CREATE TABLE IF NOT EXISTS campaigns (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    description TEXT NULL,
                    goal_amount DECIMAL(10,2) NOT NULL,
                    commitment_type VARCHAR(20) NOT NULL DEFAULT 'fixed',
                    congregation_id INT NULL,
                    start_date DATE NOT NULL,
                    end_date DATE NULL,
                    status VARCHAR(20) NOT NULL DEFAULT 'active',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            $db->exec("
                CREATE TABLE IF NOT EXISTS campaign_participants (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    campaign_id INT NOT NULL,
                    member_id INT NOT NULL,
                    monthly_amount DECIMAL(10,2) NULL,
                    months_committed INT NULL,
                    status VARCHAR(20) NOT NULL DEFAULT 'active',
                    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            $db->exec("
                CREATE TABLE IF NOT EXISTS campaign_installments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    campaign_id INT NOT NULL,
                    participant_id INT NOT NULL,
                    member_id INT NOT NULL,
                    reference_month VARCHAR(7) NOT NULL,
                    committed_amount DECIMAL(10,2) NOT NULL,
                    paid_amount DECIMAL(10,2) NULL,
                    paid_date DATE NULL,
                    status VARCHAR(20) NOT NULL DEFAULT 'pending',
                    tithe_id INT NULL,
                    notes TEXT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } else {
            $db->exec("
                CREATE TABLE IF NOT EXISTS campaigns (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    description TEXT NULL,
                    goal_amount REAL NOT NULL,
                    commitment_type TEXT NOT NULL DEFAULT 'fixed',
                    congregation_id INTEGER NULL,
                    start_date TEXT NOT NULL,
                    end_date TEXT NULL,
                    status TEXT NOT NULL DEFAULT 'active',
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $db->exec("
                CREATE TABLE IF NOT EXISTS campaign_participants (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    campaign_id INTEGER NOT NULL,
                    member_id INTEGER NOT NULL,
                    monthly_amount REAL NULL,
                    months_committed INTEGER NULL,
                    status TEXT NOT NULL DEFAULT 'active',
                    joined_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $db->exec("
                CREATE TABLE IF NOT EXISTS campaign_installments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    campaign_id INTEGER NOT NULL,
                    participant_id INTEGER NOT NULL,
                    member_id INTEGER NOT NULL,
                    reference_month TEXT NOT NULL,
                    committed_amount REAL NOT NULL,
                    paid_amount REAL NULL,
                    paid_date TEXT NULL,
                    status TEXT NOT NULL DEFAULT 'pending',
                    tithe_id INTEGER NULL,
                    notes TEXT NULL,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");
        }

        $stmt = $db->prepare('SELECT id FROM permissions WHERE slug = ?');
        foreach ([
            ['campaigns.view', 'Ver Campanhas', 'Visualizar campanhas de arrecadação'],
            ['campaigns.manage', 'Gerenciar Campanhas', 'Criar, editar campanhas, participantes e pagamentos'],
        ] as $perm) {
            $stmt->execute([$perm[0]]);
            if (!$stmt->fetchColumn()) {
                $db->prepare('INSERT INTO permissions (slug, label, description) VALUES (?, ?, ?)')->execute($perm);
            }
        }
    }

    public function down($db) {
        $db->exec('DROP TABLE IF EXISTS campaign_installments');
        $db->exec('DROP TABLE IF EXISTS campaign_participants');
        $db->exec('DROP TABLE IF EXISTS campaigns');
    }
}
