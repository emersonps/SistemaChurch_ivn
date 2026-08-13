<?php
class AddDonationPermissions {
    public function up($pdo) {
        $permissions = [
            ['donations.view', 'Ver Doações'],
            ['donations.manage', 'Gerenciar Doações']
        ];

        $stmt = $pdo->prepare("INSERT INTO permissions (slug, label) VALUES (?, ?)");

        foreach ($permissions as $p) {
            try {
                $stmt->execute($p);
            } catch (Exception $e) {
                // Ignora erro de duplicata
            }
        }
    }

    public function down($pdo) {
        $pdo->exec("DELETE FROM permissions WHERE slug IN ('donations.view', 'donations.manage')");
    }
}
