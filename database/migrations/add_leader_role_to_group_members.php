<?php
// database/migrations/add_leader_role_to_group_members.php

require_once __DIR__ . '/../../config/database.php';

echo "Iniciando atualização da tabela group_members...\n";

try {
    $db = (new Database())->connect();
    
    // Verificar se é MySQL ou SQLite
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    
    if ($driver === 'mysql') {
        // MySQL: ALTER TABLE MODIFY
        $stmt = $db->query("SHOW COLUMNS FROM group_members LIKE 'role'");
        $col = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (strpos($col['Type'], "'leader'") === false) {
            echo "Adicionando 'leader' ao ENUM role...\n";
            // Manter compatibilidade com os valores existentes ('member','assistant','host') e adicionar 'leader'
            // Também preservar se permite NULL ou Default
            $sql = "ALTER TABLE group_members MODIFY COLUMN role ENUM('member', 'assistant', 'host', 'leader') DEFAULT 'member'";
            $db->exec($sql);
            echo "Coluna 'role' atualizada com sucesso!\n";
        } else {
            echo "Role 'leader' já existe.\n";
        }
    } else {
        // SQLite não suporta modificar ENUM facilmente (geralmente é TEXT com CHECK constraint ou apenas TEXT)
        // Se for SQLite, provavelmente não precisa fazer nada se for apenas TEXT, 
        // mas se tiver CHECK constraint, precisaria recriar a tabela.
        // Assumindo que o erro "Data truncated" veio do MySQL (code 1265), então é MySQL.
        echo "Driver não é MySQL ($driver). Verifique se suporte a ENUM é necessário.\n";
    }

} catch (Exception $e) {
    echo "Erro na migração: " . $e->getMessage() . "\n";
}
