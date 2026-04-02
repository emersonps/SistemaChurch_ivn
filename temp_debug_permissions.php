<?php
require __DIR__ . '/src/config/database.php';
require __DIR__ . '/src/helpers.php'; // For hasPermission logic if needed, but let's just dump raw data first

$db = (new Database())->connect();

echo "--- Users ---\n";
$users = $db->query("SELECT id, username, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $user) {
    echo "ID: {$user['id']}, User: {$user['username']}, Role: {$user['role']}\n";
    
    // Check permissions
    $stmt = $db->prepare("SELECT permission_slug FROM user_permissions WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $perms = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "  DB Perms: " . implode(', ', $perms) . "\n";
    
    // Check config permissions
    $rbac = require __DIR__ . '/config/rbac.php';
    $configPerms = $rbac['roles'][$user['role']]['permissions'] ?? [];
    echo "  Config Perms (Default): " . implode(', ', $configPerms) . "\n";
    
    // Combined effective permissions (rough approximation of hasPermission logic)
    $effective = array_unique(array_merge($perms, $configPerms));
    // Filter out if user is developer (gets all)
    if ($user['role'] === 'developer') {
        echo "  Effective: ALL (Developer)\n";
    } else {
        echo "  Effective (DB + Config): " . implode(', ', $effective) . "\n";
    }
    echo "\n";
}
