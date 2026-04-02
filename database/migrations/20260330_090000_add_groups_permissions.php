<?php
require_once __DIR__ . '/../../config/database.php';

try {
    $db = (new Database())->connect();
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    $ignore = $driver === 'sqlite' ? 'OR IGNORE' : 'IGNORE';
    $db->exec("INSERT $ignore INTO permissions (slug, label, description) VALUES
        ('groups.view', 'Ver Grupos/Células', 'Visualizar grupos/células'),
        ('groups.manage', 'Gerenciar Grupos/Células', 'Criar, editar e excluir grupos/células')
    ");
    echo 'Permissões de Grupos/Células adicionadas.';
} catch (Exception $e) {
    echo 'Erro ao adicionar permissões: ' . $e->getMessage();
}
