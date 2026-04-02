<?php
// setup_gallery_tables.php
require_once 'config/database.php';

$db = (new Database())->connect();

echo "Criando tabelas para a Galeria de Fotos...\n";

try {
    // Tabela de Álbuns (Categorias)
    $db->exec("CREATE TABLE IF NOT EXISTS photo_albums (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        description TEXT,
        event_date DATE,
        location TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Tabela 'photo_albums' criada.\n";

    // Tabela de Fotos
    $db->exec("CREATE TABLE IF NOT EXISTS photos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        album_id INTEGER NOT NULL,
        filename TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (album_id) REFERENCES photo_albums(id) ON DELETE CASCADE
    )");
    echo "Tabela 'photos' criada.\n";

} catch (PDOException $e) {
    echo "Erro ao criar tabelas: " . $e->getMessage() . "\n";
}

echo "Configuração da galeria concluída.\n";
