<?php
// update_congregations_table_v2.php
require_once 'config/database.php';

$db = (new Database())->connect();

echo "Atualizando tabela de congregações (V2)...\n";

try {
    $db->exec("ALTER TABLE congregations ADD COLUMN photo TEXT");
    echo "Coluna 'photo' adicionada.\n";
} catch (PDOException $e) { echo "Coluna 'photo' já existe ou erro: " . $e->getMessage() . "\n"; }

try {
    $db->exec("ALTER TABLE congregations ADD COLUMN zip_code TEXT");
    echo "Coluna 'zip_code' adicionada.\n";
} catch (PDOException $e) { echo "Coluna 'zip_code' já existe ou erro: " . $e->getMessage() . "\n"; }

try {
    $db->exec("ALTER TABLE congregations ADD COLUMN city TEXT");
    echo "Coluna 'city' adicionada.\n";
} catch (PDOException $e) { echo "Coluna 'city' já existe ou erro: " . $e->getMessage() . "\n"; }

try {
    $db->exec("ALTER TABLE congregations ADD COLUMN state TEXT");
    echo "Coluna 'state' adicionada.\n";
} catch (PDOException $e) { echo "Coluna 'state' já existe ou erro: " . $e->getMessage() . "\n"; }

echo "Atualização V2 concluída.\n";
