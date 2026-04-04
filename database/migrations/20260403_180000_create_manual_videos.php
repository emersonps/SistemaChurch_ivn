<?php

class CreateManualVideos {
    public function up($db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            $db->exec("
                CREATE TABLE IF NOT EXISTS manual_videos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    theme VARCHAR(120) NOT NULL,
                    description TEXT NULL,
                    youtube_url VARCHAR(500) NOT NULL,
                    youtube_video_id VARCHAR(40) NOT NULL,
                    sort_order INT NOT NULL DEFAULT 0,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            $db->exec("
                CREATE TABLE IF NOT EXISTS manual_video_targets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    manual_video_id INT NOT NULL,
                    target_type VARCHAR(30) NOT NULL,
                    target_key VARCHAR(120) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uniq_manual_target (manual_video_id, target_type, target_key),
                    KEY idx_manual_video (manual_video_id),
                    FOREIGN KEY (manual_video_id) REFERENCES manual_videos(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            return;
        }

        $db->exec("
            CREATE TABLE IF NOT EXISTS manual_videos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                theme TEXT NOT NULL,
                description TEXT NULL,
                youtube_url TEXT NOT NULL,
                youtube_video_id TEXT NOT NULL,
                sort_order INTEGER NOT NULL DEFAULT 0,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS manual_video_targets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                manual_video_id INTEGER NOT NULL,
                target_type TEXT NOT NULL,
                target_key TEXT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $db->exec("CREATE UNIQUE INDEX IF NOT EXISTS uniq_manual_target ON manual_video_targets(manual_video_id, target_type, target_key)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_manual_video ON manual_video_targets(manual_video_id)");
    }

    public function down($db) {
        $db->exec("DROP TABLE IF EXISTS manual_video_targets");
        $db->exec("DROP TABLE IF EXISTS manual_videos");
    }
}
