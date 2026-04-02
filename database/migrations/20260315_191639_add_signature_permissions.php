<?php
class AddSignaturePermissions {
    public function up($pdo) {
        $permissions = [
            ['signatures.view', 'Visualizar Assinaturas'],
            ['signatures.manage', 'Gerenciar Assinaturas']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO permissions (slug, label) VALUES (?, ?)");
        
        foreach ($permissions as $p) {
            try {
                // Tenta inserir, ignora se duplicado
                $stmt->execute($p);
            } catch (Exception $e) {
                // Ignora erro de duplicata
            }
        }
    }

    public function down($pdo) {
        $pdo->exec("DELETE FROM permissions WHERE slug IN ('signatures.view', 'signatures.manage')");
    }
}
