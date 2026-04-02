<?php
require_once __DIR__ . '/../../config/database.php';

try {
    $db = (new Database())->connect();
    
    // SQLite doesn't support INSERT IGNORE, use INSERT OR IGNORE
    // MySQL uses INSERT IGNORE
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    $ignore = $driver === 'sqlite' ? 'OR IGNORE' : 'IGNORE';

    $db->exec("INSERT $ignore INTO permissions (slug, label, description) VALUES 
        ('permissions.manage', 'Gerenciar Permissões/RBAC', 'Permite acessar a tela de RBAC e gerenciar cargos no sistema.')
    ");
    
    echo "Permissão adicionada ao catálogo.";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
