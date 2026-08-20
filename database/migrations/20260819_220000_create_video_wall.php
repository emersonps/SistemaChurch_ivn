<?php
// Mural de Vídeos: catálogo de vídeos do YouTube por categoria, com um
// vídeo em destaque exibido na home pública.

class CreateVideoWall {
    public function up($db) {
        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $db->exec("
                CREATE TABLE IF NOT EXISTS video_wall (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    youtube_url VARCHAR(500) NOT NULL,
                    youtube_video_id VARCHAR(20) NOT NULL,
                    category VARCHAR(50) NOT NULL DEFAULT 'Mensagens',
                    speaker VARCHAR(255) NULL,
                    description TEXT NULL,
                    video_date DATE NULL,
                    is_featured TINYINT(1) NOT NULL DEFAULT 0,
                    views INT NOT NULL DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } else {
            $db->exec("
                CREATE TABLE IF NOT EXISTS video_wall (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    youtube_url TEXT NOT NULL,
                    youtube_video_id TEXT NOT NULL,
                    category TEXT NOT NULL DEFAULT 'Mensagens',
                    speaker TEXT NULL,
                    description TEXT NULL,
                    video_date TEXT NULL,
                    is_featured INTEGER NOT NULL DEFAULT 0,
                    views INTEGER NOT NULL DEFAULT 0,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");
        }

        $stmt = $db->prepare('SELECT id FROM permissions WHERE slug = ?');
        foreach ([
            ['video_wall.view', 'Ver Mural de Vídeos', 'Visualizar o mural de vídeos'],
            ['video_wall.manage', 'Gerenciar Mural de Vídeos', 'Criar, editar e excluir vídeos e definir destaque'],
        ] as $perm) {
            $stmt->execute([$perm[0]]);
            if (!$stmt->fetchColumn()) {
                $db->prepare('INSERT INTO permissions (slug, label, description) VALUES (?, ?, ?)')->execute($perm);
            }
        }
    }

    public function down($db) {
        $db->exec('DROP TABLE IF EXISTS video_wall');
    }
}
