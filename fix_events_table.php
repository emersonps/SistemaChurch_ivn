<?php
// fix_events_table.php
require_once 'config/database.php';

$db = (new Database())->connect();

echo "Corrigindo tabela de eventos...\n";

// 1. Criar tabela temporária com a estrutura correta (sem NOT NULL em event_date)
$db->exec("CREATE TABLE events_new (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    event_date DATETIME, -- Agora permite NULL
    location TEXT,
    type TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 2. Copiar dados da tabela antiga para a nova
$db->exec("INSERT INTO events_new (id, title, description, event_date, location, type, created_at)
           SELECT id, title, description, event_date, location, type, created_at FROM events");

// 3. Remover tabela antiga
$db->exec("DROP TABLE events");

// 4. Renomear tabela nova para o nome original
$db->exec("ALTER TABLE events_new RENAME TO events");

echo "Tabela 'events' corrigida com sucesso! Agora 'event_date' aceita NULL.\n";
