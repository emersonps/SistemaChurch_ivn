<?php
// add_end_time_to_events.php
require_once 'config/database.php';

$db = (new Database())->connect();

echo "Adicionando coluna 'end_time' na tabela de eventos...\n";

try {
    $db->exec("ALTER TABLE events ADD COLUMN end_time TEXT");
    echo "Coluna 'end_time' adicionada com sucesso!\n";
} catch (PDOException $e) {
    echo "Nota: Coluna 'end_time' provavelmente já existe ou erro: " . $e->getMessage() . "\n";
}

echo "Atualização concluída.\n";
