<?php
// add_status_to_events.php
require_once 'config/database.php';

$db = (new Database())->connect();

echo "Adicionando coluna 'status' na tabela de eventos...\n";

try {
    // Adicionar coluna status se não existir
    // SQLite não suporta ADD COLUMN IF NOT EXISTS diretamente em versões antigas, mas o comando falha se já existir
    // Vamos tentar adicionar e capturar o erro se já existir
    $db->exec("ALTER TABLE events ADD COLUMN status TEXT DEFAULT 'active'");
    echo "Coluna 'status' adicionada com sucesso!\n";
} catch (PDOException $e) {
    echo "Nota: Coluna 'status' provavelmente já existe ou erro: " . $e->getMessage() . "\n";
}

echo "Atualização concluída.\n";
