<?php
// update_congregations_table.php
require_once 'config/database.php';

$db = (new Database())->connect();

echo "Atualizando tabela de congregações...\n";

try {
    $db->exec("ALTER TABLE congregations ADD COLUMN opening_date DATE");
    echo "Coluna 'opening_date' adicionada.\n";
} catch (PDOException $e) { echo "Coluna 'opening_date' já existe ou erro: " . $e->getMessage() . "\n"; }

try {
    $db->exec("ALTER TABLE congregations ADD COLUMN phone TEXT");
    echo "Coluna 'phone' adicionada.\n";
} catch (PDOException $e) { echo "Coluna 'phone' já existe ou erro: " . $e->getMessage() . "\n"; }

try {
    $db->exec("ALTER TABLE congregations ADD COLUMN email TEXT");
    echo "Coluna 'email' adicionada.\n";
} catch (PDOException $e) { echo "Coluna 'email' já existe ou erro: " . $e->getMessage() . "\n"; }

echo "Atualização concluída.\n";
