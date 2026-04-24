<?php

class CreatePrayerRequestsTables {
    public function up($db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            $db->exec("
                CREATE TABLE IF NOT EXISTS prayer_requests (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(120) NULL,
                    is_anonymous TINYINT(1) NOT NULL DEFAULT 0,
                    message TEXT NOT NULL,
                    status VARCHAR(20) NOT NULL DEFAULT 'published',
                    amen_count INT NOT NULL DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    KEY idx_prayer_requests_status_created (status, created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            $db->exec("
                CREATE TABLE IF NOT EXISTS prayer_request_amens (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    prayer_request_id INT NOT NULL,
                    session_key VARCHAR(64) NOT NULL,
                    ip_hash VARCHAR(64) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uniq_prayer_request_session (prayer_request_id, session_key),
                    KEY idx_prayer_request_amens_request (prayer_request_id),
                    CONSTRAINT fk_prayer_request_amens_request
                        FOREIGN KEY (prayer_request_id) REFERENCES prayer_requests(id)
                        ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            return;
        }

        $db->exec("
            CREATE TABLE IF NOT EXISTS prayer_requests (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NULL,
                is_anonymous INTEGER NOT NULL DEFAULT 0,
                message TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT 'published',
                amen_count INTEGER NOT NULL DEFAULT 0,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_prayer_requests_status_created ON prayer_requests(status, created_at)");

        $db->exec("
            CREATE TABLE IF NOT EXISTS prayer_request_amens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                prayer_request_id INTEGER NOT NULL,
                session_key TEXT NOT NULL,
                ip_hash TEXT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (prayer_request_id) REFERENCES prayer_requests(id) ON DELETE CASCADE
            )
        ");
        $db->exec("CREATE UNIQUE INDEX IF NOT EXISTS uniq_prayer_request_session ON prayer_request_amens(prayer_request_id, session_key)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_prayer_request_amens_request ON prayer_request_amens(prayer_request_id)");
    }

    public function down($db) {
        $db->exec("DROP TABLE IF EXISTS prayer_request_amens");
        $db->exec("DROP TABLE IF EXISTS prayer_requests");
    }
}
