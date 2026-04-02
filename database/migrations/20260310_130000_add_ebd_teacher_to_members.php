<?php
// database/migrations/20260310_130000_add_ebd_teacher_to_members.php

// Verifica se a coluna já existe antes de tentar adicionar
$db = (new Database())->connect();
$exists = false;
try {
    $stmt = $db->query("SHOW COLUMNS FROM members LIKE 'is_ebd_teacher'");
    if ($stmt->fetch()) {
        $exists = true;
    }
} catch (Exception $e) {}

if (!$exists) {
    return [
        "ALTER TABLE members ADD COLUMN is_ebd_teacher TINYINT(1) DEFAULT 0;"
    ];
} else {
    return [];
}
