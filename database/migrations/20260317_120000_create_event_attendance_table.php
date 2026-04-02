<?php

class CreateEventAttendanceTable {
    public function up($db) {
        $sql = "CREATE TABLE IF NOT EXISTS event_attendance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_id INT NOT NULL,
            member_id INT NOT NULL,
            scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
            FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
            UNIQUE KEY unique_attendance (event_id, member_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $db->exec($sql);
    }

    public function down($db) {
        $db->exec("DROP TABLE IF EXISTS event_attendance");
    }
}
return new CreateEventAttendanceTable();
