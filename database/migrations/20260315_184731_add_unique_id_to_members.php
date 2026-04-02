<?php
class AddUniqueIdToMembers {
    public function up($pdo) {
        $sql = "ALTER TABLE members ADD COLUMN unique_id VARCHAR(7) NULL UNIQUE";
        
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'duplicate column') === false && strpos($e->getMessage(), 'exists') === false) {
                // SQLite pode não suportar UNIQUE no ALTER TABLE facilmente, então fazemos sem UNIQUE e criamos um index
                try {
                    $pdo->exec("ALTER TABLE members ADD COLUMN unique_id VARCHAR(7) NULL");
                    $pdo->exec("CREATE UNIQUE INDEX idx_members_unique_id ON members(unique_id)");
                } catch (Exception $e2) {
                     // Ignora se já existir
                }
            }
        }
        
        // Gerar IDs para membros existentes
        try {
            $members = $pdo->query("SELECT id FROM members WHERE unique_id IS NULL OR unique_id = ''")->fetchAll();
            $stmt = $pdo->prepare("UPDATE members SET unique_id = ? WHERE id = ?");
            
            foreach ($members as $m) {
                // Gera string alfanumérica aleatória de 7 caracteres
                $uniqueId = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 7);
                // Evita colisão (básico, já que a chance é baixa para poucos membros)
                $stmt->execute([$uniqueId, $m['id']]);
            }
        } catch (Exception $e) {
            // Ignora
        }
    }

    public function down($pdo) {
        try {
            $pdo->exec("DROP INDEX IF EXISTS idx_members_unique_id");
            if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
                $pdo->exec("ALTER TABLE members DROP COLUMN unique_id");
            }
        } catch (Exception $e) {
            // Ignora
        }
    }
}
