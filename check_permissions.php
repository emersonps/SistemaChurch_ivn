<?php
require 'config/database.php';
$db = (new Database())->connect();

echo "--- USERS ---\n";
$users = $db->query("SELECT id, username, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    echo "ID: {$u['id']} | User: {$u['username']} | Role: {$u['role']}\n";
    $perms = $db->query("SELECT permission_slug FROM user_permissions WHERE user_id = {$u['id']}")->fetchAll(PDO::FETCH_COLUMN);
    if ($perms) {
        echo "  Custom Perms: " . implode(', ', $perms) . "\n";
    } else {
        echo "  Custom Perms: (none)\n";
    }
}
