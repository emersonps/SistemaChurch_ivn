<?php
// scripts/create_developer_user.php

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->connect();
    
    $username = 'emerson';
    $password = 'Overid@392216';
    $role = 'developer';
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $existingUser = $stmt->fetch();
    
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    if ($existingUser) {
        echo "Updating existing user '$username'...\n";
        $stmt = $db->prepare("UPDATE users SET password = ?, role = ? WHERE id = ?");
        $stmt->execute([$hash, $role, $existingUser['id']]);
        echo "User updated successfully.\n";
    } else {
        echo "Creating new user '$username'...\n";
        $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hash, $role]);
        echo "User created successfully.\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
