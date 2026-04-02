<?php
// update_members_table_full.php

ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config/database.php';

echo "Iniciando atualização completa da tabela members...\n";

try {
    $db = (new Database())->connect();
    
    $newColumns = [
        'gender' => 'TEXT', // Sexo
        'cpf' => 'TEXT', // CPF
        'rg' => 'TEXT', // Identidade
        'marital_status' => 'TEXT', // Estado Civil
        'address' => 'TEXT', // Endereço (já existe na maioria dos sistemas, mas vamos garantir)
        'address_number' => 'TEXT', // Numero da casa
        'neighborhood' => 'TEXT', // Bairro
        'complement' => 'TEXT', // Complemento
        'reference_point' => 'TEXT', // Ponto de referencias
        'zip_code' => 'TEXT', // CEP
        'state' => 'TEXT', // Estado
        'city' => 'TEXT', // Cidade
        'role' => 'TEXT', // Cargo
        'nationality' => 'TEXT', // Nacionalidade
        'birthplace' => 'TEXT', // Natural de
        'father_name' => 'TEXT', // Pai
        'mother_name' => 'TEXT', // Mae
        'children_count' => 'INTEGER DEFAULT 0', // Filhos (quantidade)
        // member_status já temos 'status', vamos usar o existente ou aprimorar
        'profession' => 'TEXT', // Profissao
        'church_origin' => 'TEXT', // Origem da igreja
        'admission_method' => 'TEXT', // Membro aceito: aclamação, transferido, batizado, congregado
        'admission_date' => 'DATE', // Data de aceite
        'exit_date' => 'DATE', // Data de saída
        'is_tither' => 'INTEGER DEFAULT 0' // Dizimista
    ];

    $columns = $db->query("PRAGMA table_info(members)")->fetchAll(PDO::FETCH_ASSOC);
    $existingCols = array_column($columns, 'name');

    foreach ($newColumns as $colName => $colType) {
        if (!in_array($colName, $existingCols)) {
            echo "Adicionando coluna '$colName'...\n";
            $db->exec("ALTER TABLE members ADD COLUMN $colName $colType");
            echo "Coluna '$colName' adicionada.\n";
        } else {
            echo "Coluna '$colName' já existe.\n";
        }
    }
    
    echo "Atualização concluída com sucesso!\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
