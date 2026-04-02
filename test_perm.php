<?php
session_start();
require_once __DIR__ . '/../src/config/database.php';

function getDB() {
    return (new Database())->connect();
}

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['user_role'] ?? 'guest';

echo "<h1>Debug Permissions</h1>";
echo "User ID: " . ($userId ?? 'Not logged in') . "<br>";
echo "Role: $role<br>";

if ($userId) {
    $db = getDB();
    
    // Check DB Permissions
    echo "<h3>Custom Permissions in DB:</h3>";
    $stmt = $db->prepare("SELECT * FROM user_permissions WHERE user_id = ?");
    $stmt->execute([$userId]);
    $perms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($perms) {
        echo "<ul>";
        foreach ($perms as $p) {
            echo "<li>{$p['permission_slug']}</li>";
        }
        echo "</ul>";
    } else {
        echo "None<br>";
    }
    
    // Check Role Config
    echo "<h3>Role Permissions (Config):</h3>";
    $rbac = require __DIR__ . '/../config/rbac.php';
    if (isset($rbac['roles'][$role])) {
        echo "<ul>";
        foreach ($rbac['roles'][$role]['permissions'] as $p) {
            echo "<li>$p</li>";
        }
        echo "</ul>";
    } else {
        echo "Role not found in config<br>";
    }
    
    // Test Specific
    echo "<h3>Test Checks:</h3>";
    $tests = ['events.view', 'events.manage', 'banners.view', 'banners.manage'];
    foreach ($tests as $t) {
        $has = hasPermissionDebug($t, $userId, $role) ? 'YES' : 'NO';
        echo "$t: $has<br>";
    }
}

function hasPermissionDebug($permission, $userId, $role) {
    // 1. Check Role
    $rbac = require __DIR__ . '/../config/rbac.php';
    $rolePermissions = [];
    if (isset($rbac['roles'][$role])) {
        $rolePermissions = $rbac['roles'][$role]['permissions'];
    }
    if (in_array($permission, $rolePermissions)) return true;

    // 2. Check DB
    $db = (new Database())->connect();
    $stmt = $db->prepare("SELECT 1 FROM user_permissions WHERE user_id = ? AND permission_slug = ?");
    $stmt->execute([$userId, $permission]);
    if ($stmt->fetch()) return true;
    
    return false;
}
