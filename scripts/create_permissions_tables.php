<?php
require_once __DIR__ . '/../config/database.php';

echo "Creating 'permissions' and 'user_permissions' tables...\n";

$db = (new Database())->connect();
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

// Permissions Table (To store all available permissions in DB instead of just file)
if ($driver === 'sqlite') {
    $sqlPerms = "CREATE TABLE IF NOT EXISTS permissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT NOT NULL UNIQUE,
        label TEXT NOT NULL,
        description TEXT
    )";
} else {
    $sqlPerms = "CREATE TABLE IF NOT EXISTS permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(100) NOT NULL UNIQUE,
        label VARCHAR(255) NOT NULL,
        description TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
}

try {
    $db->exec($sqlPerms);
    echo "Table 'permissions' created.\n";
} catch (PDOException $e) {
    echo "Error creating 'permissions': " . $e->getMessage() . "\n";
}

// User Permissions Table (Many-to-Many)
if ($driver === 'sqlite') {
    $sqlUserPerms = "CREATE TABLE IF NOT EXISTS user_permissions (
        user_id INTEGER NOT NULL,
        permission_slug TEXT NOT NULL,
        PRIMARY KEY (user_id, permission_slug),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
} else {
    $sqlUserPerms = "CREATE TABLE IF NOT EXISTS user_permissions (
        user_id INT NOT NULL,
        permission_slug VARCHAR(100) NOT NULL,
        PRIMARY KEY (user_id, permission_slug),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
}

try {
    $db->exec($sqlUserPerms);
    echo "Table 'user_permissions' created.\n";
} catch (PDOException $e) {
    echo "Error creating 'user_permissions': " . $e->getMessage() . "\n";
}

// Populate Permissions Table with Defaults
$defaultPermissions = [
    ['slug' => 'dashboard.view', 'label' => 'Ver Dashboard', 'description' => 'Acesso ao painel principal'],
    ['slug' => 'members.view', 'label' => 'Ver Membros', 'description' => 'Visualizar lista de membros'],
    ['slug' => 'members.manage', 'label' => 'Gerenciar Membros', 'description' => 'Criar, editar e excluir membros'],
    ['slug' => 'congregations.view', 'label' => 'Ver Congregações', 'description' => 'Visualizar lista de congregações'],
    ['slug' => 'congregations.manage', 'label' => 'Gerenciar Congregações', 'description' => 'Criar, editar e excluir congregações'],
    ['slug' => 'financial.view', 'label' => 'Ver Finanças', 'description' => 'Visualizar dízimos e ofertas'],
    ['slug' => 'financial.manage', 'label' => 'Gerenciar Finanças', 'description' => 'Lançar dízimos e ofertas'],
    ['slug' => 'events.view', 'label' => 'Ver Eventos', 'description' => 'Visualizar agenda de eventos'],
    ['slug' => 'events.manage', 'label' => 'Gerenciar Eventos', 'description' => 'Criar, editar e excluir eventos'],
    ['slug' => 'gallery.view', 'label' => 'Ver Galeria', 'description' => 'Visualizar álbuns de fotos'],
    ['slug' => 'gallery.manage', 'label' => 'Gerenciar Galeria', 'description' => 'Upload e exclusão de fotos'],
    ['slug' => 'banners.view', 'label' => 'Ver Banners', 'description' => 'Visualizar banners do site'],
    ['slug' => 'banners.manage', 'label' => 'Gerenciar Banners', 'description' => 'Upload e edição de banners'],
    ['slug' => 'studies.view', 'label' => 'Ver Estudos', 'description' => 'Visualizar estudos bíblicos'],
    ['slug' => 'studies.manage', 'label' => 'Gerenciar Estudos', 'description' => 'Publicar estudos bíblicos'],
    ['slug' => 'service_reports.view', 'label' => 'Ver Relatórios de Culto', 'description' => 'Visualizar relatórios'],
    ['slug' => 'service_reports.manage', 'label' => 'Gerenciar Relatórios', 'description' => 'Criar relatórios de culto'],
    ['slug' => 'users.manage', 'label' => 'Gerenciar Usuários', 'description' => 'Criar e editar usuários do sistema'],
    ['slug' => 'system_payments.view', 'label' => 'Ver Pagamento Sistema', 'description' => 'Visualizar status do pagamento do sistema'],
];

try {
    $stmt = $db->prepare("INSERT INTO permissions (slug, label, description) VALUES (?, ?, ?)");
    foreach ($defaultPermissions as $perm) {
        // Check if exists to avoid error
        $check = $db->prepare("SELECT id FROM permissions WHERE slug = ?");
        $check->execute([$perm['slug']]);
        if (!$check->fetch()) {
            $stmt->execute([$perm['slug'], $perm['label'], $perm['description']]);
        }
    }
    echo "Default permissions populated.\n";
} catch (PDOException $e) {
    echo "Error populating permissions: " . $e->getMessage() . "\n";
}
