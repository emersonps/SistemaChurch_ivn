<?php
class AddMemberIdToUsers {
    public function up($pdo) {
        // Adiciona a coluna member_id na tabela users
        // Verifica se é MySQL ou SQLite para a sintaxe correta (embora ADD COLUMN seja padrão)
        
        $sql = "ALTER TABLE users ADD COLUMN member_id INTEGER NULL";
        
        // No MySQL, podemos adicionar a constraint de FK
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql .= ", ADD CONSTRAINT fk_users_member FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL";
        }
        
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            // Ignora se a coluna já existir (SQLite não tem IF NOT EXISTS para colunas em versões antigas)
            if (strpos($e->getMessage(), 'duplicate column') === false && strpos($e->getMessage(), 'exists') === false) {
                throw $e;
            }
        }
    }

    public function down($pdo) {
        // SQLite não suporta DROP COLUMN facilmente em versões antigas, mas vamos tentar
        // MySQL suporta
        
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $pdo->exec("ALTER TABLE users DROP FOREIGN KEY fk_users_member");
            $pdo->exec("ALTER TABLE users DROP COLUMN member_id");
        } else {
            // SQLite (simplificado, geralmente requer recriar a tabela, mas vamos tentar o drop se for versão nova)
            try {
                $pdo->exec("ALTER TABLE users DROP COLUMN member_id");
            } catch (Exception $e) {
                // Ignora erro no SQLite antigo
            }
        }
    }
}
