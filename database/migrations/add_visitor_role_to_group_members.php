<?php
// database/migrations/add_visitor_role_to_group_members.php

require_once __DIR__ . '/../../config/database.php';

echo "Iniciando atualização da tabela group_members (Visitante)...\n";

try {
    $db = (new Database())->connect();
    
    // Verificar se é MySQL
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    
    if ($driver === 'mysql') {
        $stmt = $db->query("SHOW COLUMNS FROM group_members LIKE 'role'");
        $col = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (strpos($col['Type'], "'visitor'") === false) {
            echo "Adicionando 'visitor' ao ENUM role...\n";
            // Manter valores existentes e adicionar 'visitor'
            // Lista completa: member, assistant, host, leader, visitor
            $sql = "ALTER TABLE group_members MODIFY COLUMN role ENUM('member', 'assistant', 'host', 'leader', 'visitor') DEFAULT 'member'";
            $db->exec($sql);
            echo "Coluna 'role' atualizada com sucesso!\n";
        } else {
            echo "Role 'visitor' já existe.\n";
        }
    } else {
        echo "Driver não é MySQL ($driver). Verifique suporte a ENUM.\n";
    }

} catch (Exception $e) {
    echo "Erro na migração: " . $e->getMessage() . "\n";
}
