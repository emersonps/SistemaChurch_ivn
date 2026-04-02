<?php
class AddGeneralReportsPermission {
    public function up($pdo) {
        $permissions = [
            ['general_reports.view', 'Visualizar Estatísticas Gerais']
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
        $pdo->exec("DELETE FROM permissions WHERE slug = 'general_reports.view'");
    }
}
