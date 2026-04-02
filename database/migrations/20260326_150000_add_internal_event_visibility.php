<?php
class AddInternalEventVisibility {
    public function up(PDO $db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $db->exec("CREATE TABLE IF NOT EXISTS event_allowed_members (id INT AUTO_INCREMENT PRIMARY KEY, event_id INT NOT NULL, member_id INT NOT NULL, UNIQUE KEY uniq_member (event_id, member_id), INDEX idx_event_member (event_id, member_id), FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE, FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $db->exec("CREATE TABLE IF NOT EXISTS event_allowed_congregations (id INT AUTO_INCREMENT PRIMARY KEY, event_id INT NOT NULL, congregation_id INT NOT NULL, UNIQUE KEY uniq_congregation (event_id, congregation_id), INDEX idx_event_congregation (event_id, congregation_id), FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE, FOREIGN KEY (congregation_id) REFERENCES congregations(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } else {
            $db->exec("CREATE TABLE IF NOT EXISTS event_allowed_members (id INTEGER PRIMARY KEY AUTOINCREMENT, event_id INTEGER NOT NULL, member_id INTEGER NOT NULL)");
            $db->exec("CREATE UNIQUE INDEX IF NOT EXISTS uniq_member ON event_allowed_members(event_id, member_id)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_event_member ON event_allowed_members(event_id, member_id)");
            $db->exec("CREATE TABLE IF NOT EXISTS event_allowed_congregations (id INTEGER PRIMARY KEY AUTOINCREMENT, event_id INTEGER NOT NULL, congregation_id INTEGER NOT NULL)");
            $db->exec("CREATE UNIQUE INDEX IF NOT EXISTS uniq_congregation ON event_allowed_congregations(event_id, congregation_id)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_event_congregation ON event_allowed_congregations(event_id, congregation_id)");
        }
    }
    public function down(PDO $db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $db->exec("DROP TABLE IF EXISTS event_allowed_members");
            $db->exec("DROP TABLE IF EXISTS event_allowed_congregations");
        } else {
            $db->exec("DROP TABLE IF EXISTS event_allowed_members");
            $db->exec("DROP TABLE IF EXISTS event_allowed_congregations");
        }
    }
}
