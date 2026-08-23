<?php
// Galeria de Fotos: categorias administráveis (mesmo padrão do Mural de
// Vídeos) — permite adicionar, renomear e excluir categorias pela tela de
// admin, com renomeação em cascata para os álbuns que já usam a categoria.

class CreatePhotoAlbumCategories {
    public function up($db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            $db->exec("
                CREATE TABLE IF NOT EXISTS photo_album_categories (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL UNIQUE,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } else {
            $db->exec("
                CREATE TABLE IF NOT EXISTS photo_album_categories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL UNIQUE,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");
        }

        $stmt = $db->prepare('SELECT id FROM photo_album_categories WHERE name = ?');
        foreach (['Cultos', 'Eventos', 'Batismos', 'Jovens', 'Casais'] as $name) {
            $stmt->execute([$name]);
            if (!$stmt->fetchColumn()) {
                $db->prepare('INSERT INTO photo_album_categories (name) VALUES (?)')->execute([$name]);
            }
        }

        $hasColumn = false;
        if ($driver === 'mysql') {
            $hasColumn = (bool)$db->query("SHOW COLUMNS FROM photo_albums LIKE 'category'")->fetch();
        } else {
            $cols = $db->query("PRAGMA table_info(photo_albums)")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $col) {
                if (strtolower($col['name']) === 'category') {
                    $hasColumn = true;
                    break;
                }
            }
        }

        if (!$hasColumn) {
            $db->exec("ALTER TABLE photo_albums ADD COLUMN category VARCHAR(100) NULL");
        }
    }

    public function down($db) {
        $db->exec('DROP TABLE IF EXISTS photo_album_categories');
        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $db->exec("ALTER TABLE photo_albums DROP COLUMN category");
        }
    }
}
