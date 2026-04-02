<?php
require_once __DIR__ . '/../config/database.php';

echo "--- VERIFICANDO EVENTOS NO BANCO ---\n";

try {
    $db = (new Database())->connect();
    
    // Todos os eventos
    echo "--- TODOS OS EVENTOS ---\n";
    $all = $db->query("SELECT id, title, type, event_date FROM events")->fetchAll();
    foreach ($all as $e) {
        echo "ID: {$e['id']} | Título: {$e['title']} | Tipo: {$e['type']} | Data: {$e['event_date']}\n";
    }

    // Testando a query de CULTOS
    echo "\n--- QUERY CULTOS ---\n";
    $cultos = $db->query("SELECT * FROM events WHERE type = 'culto' ORDER BY event_date ASC")->fetchAll();
    echo "Encontrados: " . count($cultos) . "\n";

    // Testando a query de EVENTOS
    echo "\n--- QUERY OUTROS EVENTOS (Filtro: data >= hoje) ---\n";
    echo "Data de hoje (SQLite): " . date('Y-m-d') . "\n";
    $eventos = $db->query("SELECT * FROM events WHERE type != 'culto' AND event_date >= date('now') ORDER BY event_date ASC")->fetchAll();
    echo "Encontrados: " . count($eventos) . "\n";
    
    if (empty($eventos)) {
        echo "⚠️  ATENÇÃO: Se você cadastrou um evento com data PASSADA, ele não aparecerá aqui por causa do filtro 'event_date >= date('now')'.\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
