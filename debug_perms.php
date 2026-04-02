<?php
// Adjust path if needed
require 'config/database.php';
$db = (new Database())->connect();

echo "--- PERMISSIONS TABLE ---\n";
try {
    $perms = $db->query('SELECT * FROM permissions')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($perms as $p) {
        echo $p['slug'] . " | " . $p['label'] . "\n";
    }
} catch (Exception $e) {
    echo "Error fetching permissions: " . $e->getMessage() . "\n";
}

echo "\n--- USER PERMISSIONS (Latest User) ---\n";
try {
    $lastUser = $db->query('SELECT id, username, role FROM users ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    if ($lastUser) {
        echo "User: " . $lastUser['username'] . " (ID: " . $lastUser['id'] . ", Role: " . $lastUser['role'] . ")\n";
        $uPerms = $db->query("SELECT * FROM user_permissions WHERE user_id = " . $lastUser['id'])->fetchAll(PDO::FETCH_ASSOC);
        foreach ($uPerms as $up) {
            echo "- " . $up['permission_slug'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error fetching user permissions: " . $e->getMessage() . "\n";
}
