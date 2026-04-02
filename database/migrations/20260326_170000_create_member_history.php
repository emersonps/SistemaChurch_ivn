<?php
class CreateMemberHistory {
    public function up(PDO $db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $db->exec("CREATE TABLE IF NOT EXISTS member_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                member_id INT NOT NULL,
                user_id INT NULL,
                category VARCHAR(100) NOT NULL,
                note TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_member (member_id),
                FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } else {
            $db->exec("CREATE TABLE IF NOT EXISTS member_history (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                member_id INTEGER NOT NULL,
                user_id INTEGER,
                category TEXT NOT NULL,
                note TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_member ON member_history(member_id)");
        }
    }
    public function down(PDO $db) {
        $db->exec("DROP TABLE IF EXISTS member_history");
    }
}
