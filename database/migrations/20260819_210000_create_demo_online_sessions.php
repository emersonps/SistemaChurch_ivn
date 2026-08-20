<?php
// Tracks who's currently viewing the demo landing page (src/views/public/
// demo_landing.php), for a small discreet "N pessoas online" counter shown
// only there. Presence is heartbeat-based (see DemoPresenceService) —
// rows older than the heartbeat timeout are just stale viewers, not an
// explicit "offline" state, since browser close/crash can't be reliably
// detected any other way.

class CreateDemoOnlineSessions {
    public function up($db) {
        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $db->exec("
                CREATE TABLE IF NOT EXISTS demo_online_sessions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    session_key VARCHAR(64) NOT NULL UNIQUE,
                    last_seen_at DATETIME NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } else {
            $db->exec("
                CREATE TABLE IF NOT EXISTS demo_online_sessions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    session_key TEXT NOT NULL UNIQUE,
                    last_seen_at TEXT NOT NULL
                )
            ");
        }
    }

    public function down($db) {
        $db->exec('DROP TABLE IF EXISTS demo_online_sessions');
    }
}
