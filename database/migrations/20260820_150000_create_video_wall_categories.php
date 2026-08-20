<?php
// Mural de Vídeos: categorias administráveis (antes uma lista fixa em
// código) — permite adicionar, renomear e excluir categorias pela tela de
// admin, com renomeação em cascata para os vídeos que já usam a categoria.

class CreateVideoWallCategories {
    public function up($db) {
        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $db->exec("
                CREATE TABLE IF NOT EXISTS video_wall_categories (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL UNIQUE,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } else {
            $db->exec("
                CREATE TABLE IF NOT EXISTS video_wall_categories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL UNIQUE,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");
        }

        $stmt = $db->prepare('SELECT id FROM video_wall_categories WHERE name = ?');
        foreach (['Cultos', 'Mensagens', 'Louvores', 'Jovens', 'Eventos'] as $name) {
            $stmt->execute([$name]);
            if (!$stmt->fetchColumn()) {
                $db->prepare('INSERT INTO video_wall_categories (name) VALUES (?)')->execute([$name]);
            }
        }
    }

    public function down($db) {
        $db->exec('DROP TABLE IF EXISTS video_wall_categories');
    }
}
