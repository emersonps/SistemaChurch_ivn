<?php

use App\Database;

class CreateRolesTable {
    public function up($db) {
        // $db = (new \App\Database())->connect();
        
        try {
            // Detecta driver
            $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
            
            $idType = ($driver === 'sqlite') ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT AUTO_INCREMENT PRIMARY KEY";
            
            // Criar tabela roles
            $sql = "CREATE TABLE IF NOT EXISTS roles (
                id $idType,
                name VARCHAR(50) NOT NULL UNIQUE,
                description TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )";
            $db->exec($sql);
            echo "Tabela 'roles' criada com sucesso.\n";

            // Popular com cargos iniciais
            $initialRoles = [
                'Membro', 'Auxiliar', 'Diácono', 'Presbítero', 
                'Evangelista', 'Pastor', 'Missionário(a)', 'Cooperador'
            ];

            $stmt = $db->prepare("INSERT INTO roles (name) VALUES (?)");
            foreach ($initialRoles as $role) {
                // Verificar se já existe antes de inserir (para evitar erro de UNIQUE em re-execução)
                $check = $db->prepare("SELECT id FROM roles WHERE name = ?");
                $check->execute([$role]);
                if (!$check->fetch()) {
                    $stmt->execute([$role]);
                    echo "Cargo '$role' inserido.\n";
                }
            }

        } catch (PDOException $e) {
            echo "Erro ao criar tabela roles: " . $e->getMessage() . "\n";
        }
    }

    public function down() {
        $db = (new Database())->connect();
        $db->exec("DROP TABLE IF EXISTS roles");
        echo "Tabela 'roles' removida.\n";
    }
}
