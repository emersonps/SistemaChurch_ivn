<?php
require 'config/database.php';
$db = (new Database())->connect();

$newPerms = [
    ['slug' => 'financial_accounts.manage', 'label' => 'Gerenciar Contas Bancárias e Plano de Contas', 'description' => 'Criar e editar contas bancárias e categorias de plano de contas.'],
    ['slug' => 'financial_ofx.manage', 'label' => 'Gerenciar Conciliação OFX', 'description' => 'Importar extratos e realizar a conciliação bancária.']
];

$stmt = $db->prepare("INSERT INTO permissions (slug, label, description) VALUES (?, ?, ?)");
foreach ($newPerms as $perm) {
    $check = $db->prepare("SELECT id FROM permissions WHERE slug = ?");
    $check->execute([$perm['slug']]);
    if (!$check->fetch()) {
        $stmt->execute([$perm['slug'], $perm['label'], $perm['description']]);
        echo "Adicionada: {$perm['slug']}\n";
    } else {
        echo "Já existe: {$perm['slug']}\n";
    }
}
