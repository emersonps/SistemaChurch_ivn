<?php

class AddHasAttendanceListToEvents {
    public function up($db) {
        try {
            // Check if column exists first (MySQL specific)
            $stmt = $db->query("SHOW COLUMNS FROM events LIKE 'has_attendance_list'");
            if (!$stmt->fetch()) {
                $db->exec("ALTER TABLE events ADD COLUMN has_attendance_list TINYINT(1) DEFAULT 0 AFTER recurring_days");
            }
        } catch (Exception $e) {
            // SQLite fallback or ignore if exists
            try {
                $db->exec("ALTER TABLE events ADD COLUMN has_attendance_list INTEGER DEFAULT 0");
            } catch (Exception $e2) {}
        }
    }

    public function down($db) {
        // MySQL only supports DROP COLUMN
        try {
            $db->exec("ALTER TABLE events DROP COLUMN has_attendance_list");
        } catch (Exception $e) {}
    }
}
return new AddHasAttendanceListToEvents();
