<?php
// database/migrations/20260310_120400_events_banner.php

// Retornamos um array de SQLs.
// Como não podemos fazer lógica PHP complexa aqui (o runner espera array),
// vamos tentar rodar o ALTER TABLE direto. Se der erro de coluna duplicada, o runner vai falhar.
// 
// Melhor abordagem: Criar um script PHP que verifica a coluna.
// Mas o runner atual espera que o require retorne um array de strings SQL.

// Solução: Vamos fazer o require retornar um array vazio se a coluna já existir,
// executando a lógica aqui mesmo.

$db = (new Database())->connect();

// Check if column exists
$exists = false;
try {
    $stmt = $db->query("SHOW COLUMNS FROM events LIKE 'banner_path'");
    if ($stmt->fetch()) {
        $exists = true;
    }
} catch (Exception $e) {
    // Tabela pode não existir ainda (embora improvável)
}

if (!$exists) {
    return [
        "ALTER TABLE events ADD COLUMN banner_path TEXT DEFAULT NULL;"
    ];
} else {
    return []; // Nada a fazer
}
