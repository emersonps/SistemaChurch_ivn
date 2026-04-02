<?php
class AddLeaderMemberIdToCongregations {
    public function up(PDO $db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $db->exec("ALTER TABLE congregations ADD COLUMN leader_member_id INT NULL AFTER leader_name");
            // Add FK if members table exists
            try {
                $db->exec("ALTER TABLE congregations ADD CONSTRAINT fk_congregations_leader_member FOREIGN KEY (leader_member_id) REFERENCES members(id) ON DELETE SET NULL");
            } catch (Exception $e) {
                // ignore if constraint add fails
            }
        } else {
            // SQLite: add column only (no easy FK alter)
            $db->exec("ALTER TABLE congregations ADD COLUMN leader_member_id INTEGER");
        }
    }
    public function down(PDO $db) {
        // Dropping a column in SQLite is non-trivial; skip
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            try {
                $db->exec("ALTER TABLE congregations DROP FOREIGN KEY fk_congregations_leader_member");
            } catch (Exception $e) {}
            $db->exec("ALTER TABLE congregations DROP COLUMN leader_member_id");
        }
    }
}
