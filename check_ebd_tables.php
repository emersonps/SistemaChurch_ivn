<?php
// public/check_ebd_tables.php
require_once __DIR__ . '/../config/database.php';

echo "<h1>Verificação de Tabelas EBD</h1>";

try {
    $db = (new Database())->connect();
    
    $tables = ['ebd_classes', 'ebd_students', 'ebd_teachers', 'ebd_lessons', 'ebd_attendance'];
    
    foreach ($tables as $table) {
        $exists = false;
        
        // Detectar Driver
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        
        if ($driver === 'sqlite') {
            $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'");
            if ($stmt->fetch()) $exists = true;
        } else {
            // MySQL
            $stmt = $db->query("SHOW TABLES LIKE '$table'");
            if ($stmt->fetch()) $exists = true;
        }

        if ($exists) {
            echo "<p style='color:green'>✅ Tabela <strong>$table</strong> existe.</p>";
            
            // Check columns
            if ($driver === 'sqlite') {
                $cols = $db->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC);
                echo "<ul>";
                foreach ($cols as $col) {
                    echo "<li>{$col['name']} ({$col['type']})</li>";
                }
                echo "</ul>";
            } else {
                $cols = $db->query("SHOW COLUMNS FROM $table")->fetchAll(PDO::FETCH_ASSOC);
                echo "<ul>";
                foreach ($cols as $col) {
                    echo "<li>{$col['Field']} ({$col['Type']})</li>";
                }
                echo "</ul>";
            }
            
        } else {
            echo "<p style='color:red'>❌ Tabela <strong>$table</strong> NÃO encontrada.</p>";
        }
    }
    
    // Check Permissions
    echo "<h2>Permissões no Banco</h2>";
    $perms = $db->query("SELECT * FROM permissions WHERE slug LIKE 'ebd.%'")->fetchAll();
    if (count($perms) > 0) {
        echo "<p style='color:green'>✅ Permissões EBD encontradas:</p><ul>";
        foreach ($perms as $p) {
            echo "<li>{$p['slug']} - {$p['label']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red'>❌ Nenhuma permissão EBD encontrada na tabela 'permissions'.</p>";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
