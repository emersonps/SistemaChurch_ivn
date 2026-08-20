<?php
// Mural de Vídeos: suporte a transmissão ao vivo — um vídeo pode ser
// marcado como live com um horário de início agendado. A UI calcula o
// estado (contagem regressiva / ao vivo) no cliente a partir desse horário.

class AddLivestreamToVideoWall {
    public function up($db) {
        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $db->exec("ALTER TABLE video_wall ADD COLUMN is_livestream TINYINT(1) NOT NULL DEFAULT 0");
            $db->exec("ALTER TABLE video_wall ADD COLUMN livestream_scheduled_at DATETIME NULL");
        } else {
            $db->exec("ALTER TABLE video_wall ADD COLUMN is_livestream INTEGER NOT NULL DEFAULT 0");
            $db->exec("ALTER TABLE video_wall ADD COLUMN livestream_scheduled_at TEXT NULL");
        }
    }

    public function down($db) {
        // SQLite has no DROP COLUMN before 3.35; not worth the table-rebuild
        // dance for a rollback path that's unlikely to be used.
        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $db->exec("ALTER TABLE video_wall DROP COLUMN is_livestream");
            $db->exec("ALTER TABLE video_wall DROP COLUMN livestream_scheduled_at");
        }
    }
}
