<?php
class AddSettingsPermissionsToCatalog {
    public function up($pdo) {
        $permissions = [
            ['settings.view', 'Ver Configurações', 'Visualizar menu de configurações do sistema, whatsapp e layout'],
            ['settings.manage', 'Gerenciar Configurações', 'Alterar configurações gerais do sistema e layout']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO permissions (slug, label, description) VALUES (?, ?, ?)");
        
        foreach ($permissions as $p) {
            try {
                $stmt->execute($p);
            } catch (Exception $e) {
                // Ignora erro de duplicata se já existir
            }
        }
    }

    public function down($pdo) {
        $pdo->exec("DELETE FROM permissions WHERE slug IN ('settings.view', 'settings.manage')");
    }
}
