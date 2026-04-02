<?php
// Diagnóstico da API de Roles
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/config/database.php';

// Autoload
spl_autoload_register(function ($class_name) {
    $paths = [
        __DIR__ . '/src/controllers/',
        __DIR__ . '/src/models/',
        __DIR__ . '/config/'
    ];
    foreach ($paths as $path) {
        $file = $path . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

try {
    echo "<h1>Diagnóstico de Roles</h1>";
    
    // 1. Testar Conexão e Tabela
    $db = (new Database())->connect();
    echo "<p>✅ Conexão com banco estabelecida.</p>";
    
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "<p>Driver: $driver</p>";

    // Verificar se tabela existe
    $tableExists = false;
    if ($driver === 'sqlite') {
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='roles'");
        if ($stmt->fetch()) $tableExists = true;
    } else {
        $stmt = $db->query("SHOW TABLES LIKE 'roles'");
        if ($stmt->fetch()) $tableExists = true;
    }

    if ($tableExists) {
        echo "<p>✅ Tabela 'roles' EXISTE.</p>";
        
        // Listar roles atuais
        $roleModel = new Role();
        $roles = $roleModel->getAll();
        echo "<h3>Cargos Atuais no Banco (" . count($roles) . "):</h3><ul>";
        foreach ($roles as $r) {
            echo "<li>ID: {$r['id']} - Nome: <strong>{$r['name']}</strong></li>";
        }
        echo "</ul>";
        
    } else {
        echo "<p style='color:red'>❌ Tabela 'roles' NÃO EXISTE. A migração falhou ou não foi executada.</p>";
        echo "<p>Tentando criar tabela agora...</p>";
        
        // Tentar rodar migração forçada
        require_once __DIR__ . '/database/migrations/20260316_150000_create_roles_table.php';
        $migration = new CreateRolesTable();
        $migration->up($db);
        echo "<p>✅ Tentativa de criação executada. Recarregue a página.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erro Fatal: " . $e->getMessage() . "</p>";
}
?>