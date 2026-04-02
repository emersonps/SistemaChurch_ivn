<?php
class CreateUserMembersTable {
    public function up($pdo) {
        // Cria tabela de relacionamento N:N entre usuários e membros
        $sql = "CREATE TABLE IF NOT EXISTS user_members (
            user_id INTEGER NOT NULL,
            member_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id, member_id)
        )";
        
        // Se for MySQL, adiciona FKs
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
             $sql .= " ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
             $pdo->exec($sql);
             
             // Adicionar FKs separadamente para garantir compatibilidade
             try {
                 $pdo->exec("ALTER TABLE user_members ADD CONSTRAINT fk_um_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
                 $pdo->exec("ALTER TABLE user_members ADD CONSTRAINT fk_um_member FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE");
             } catch (Exception $e) {
                 // Ignora se já existir
             }
        } else {
             // SQLite
             $pdo->exec($sql);
        }

        // Migrar dados existentes da coluna member_id para a nova tabela
        try {
            // Verifica se a coluna member_id existe antes de tentar migrar
            // No SQLite/MySQL um SELECT simples resolve se a coluna existir
            $users = $pdo->query("SELECT id, member_id FROM users WHERE member_id IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $pdo->prepare("INSERT INTO user_members (user_id, member_id) VALUES (?, ?)");
            foreach ($users as $user) {
                // Verifica se já não existe para evitar duplicata (caso rode migration 2x)
                $check = $pdo->prepare("SELECT 1 FROM user_members WHERE user_id = ? AND member_id = ?");
                $check->execute([$user['id'], $user['member_id']]);
                if (!$check->fetch()) {
                    $stmt->execute([$user['id'], $user['member_id']]);
                }
            }
        } catch (Exception $e) {
            // Coluna member_id pode não existir ou erro na migração de dados
            // Apenas seguimos em frente
        }
    }

    public function down($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS user_members");
    }
}
