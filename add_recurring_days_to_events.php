<?php
// add_recurring_days_to_events.php
require_once 'config/database.php';

$db = (new Database())->connect();

echo "Adicionando coluna 'recurring_days' na tabela de eventos...\n";

try {
    $db->exec("ALTER TABLE events ADD COLUMN recurring_days TEXT");
    echo "Coluna 'recurring_days' adicionada com sucesso!\n";
} catch (PDOException $e) {
    echo "Nota: Coluna 'recurring_days' provavelmente já existe ou erro: " . $e->getMessage() . "\n";
}

echo "Atualização concluída.\n";
