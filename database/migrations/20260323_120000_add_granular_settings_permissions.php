<?php
class AddGranularSettingsPermissions {
    public function up($pdo) {
        $permissions = [
            ['settings.system.view', 'Ver Configurações do Sistema', 'Visualizar configurações gerais (Whatsapp, Chaves API)'],
            ['settings.layout.view', 'Ver Layout do Site', 'Visualizar e editar cores e informações do site'],
            ['settings.card.view', 'Ver Layout da Carteirinha', 'Visualizar e editar o modelo de impressão da carteirinha']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO permissions (slug, label, description) VALUES (?, ?, ?)");
        
        foreach ($permissions as $p) {
            try {
                $stmt->execute($p);
            } catch (Exception $e) {
                // Ignora duplicata
            }
        }
    }

    public function down($pdo) {
        $pdo->exec("DELETE FROM permissions WHERE slug IN ('settings.system.view', 'settings.layout.view', 'settings.card.view')");
    }
}
