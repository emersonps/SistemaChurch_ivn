<?php
// Script de correção emergencial para adicionar a coluna is_founder
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/config/database.php';

use App\Database;

try {
    $db = (new Database())->connect();
    
    echo "Verificando estrutura da tabela 'members'...\n";
    
    // Detecta se é MySQL ou SQLite
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "Driver detectado: $driver\n";
    
    $columnExists = false;
    
    if ($driver === 'sqlite') {
        $stmt = $db->query("PRAGMA table_info(members)");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            if ($col['name'] === 'is_founder') {
                $columnExists = true;
                break;
            }
        }
    } else { // MySQL
        $stmt = $db->query("SHOW COLUMNS FROM members LIKE 'is_founder'");
        if ($stmt->fetch()) {
            $columnExists = true;
        }
    }
    
    if ($columnExists) {
        echo "✅ A coluna 'is_founder' JÁ EXISTE na tabela 'members'.\n";
    } else {
        echo "⚠️ A coluna 'is_founder' NÃO EXISTE. Tentando adicionar...\n";
        
        if ($driver === 'sqlite') {
            $db->exec("ALTER TABLE members ADD COLUMN is_founder INTEGER DEFAULT 0");
        } else { // MySQL
            $db->exec("ALTER TABLE members ADD COLUMN is_founder TINYINT(1) DEFAULT 0 AFTER reconciled_at");
        }
        
        echo "✅ Coluna 'is_founder' adicionada com sucesso!\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>