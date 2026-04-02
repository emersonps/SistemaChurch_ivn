<?php
// update_tithes_structure.php
require '../config/database.php';

echo "Updating 'tithes' table structure...\n";

$db = (new Database())->connect();
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

try {
    // 1. Add congregation_id
    echo "Adding 'congregation_id'...\n";
    if ($driver === 'sqlite') {
        // SQLite doesn't support IF NOT EXISTS in ALTER TABLE
        try {
            $db->exec("ALTER TABLE tithes ADD COLUMN congregation_id INTEGER REFERENCES congregations(id)");
            echo "Column 'congregation_id' added.\n";
        } catch (PDOException $e) {
            echo "Column 'congregation_id' might already exist or error: " . $e->getMessage() . "\n";
        }
    } else {
        // MySQL
        try {
            $db->exec("ALTER TABLE tithes ADD COLUMN congregation_id INT, ADD FOREIGN KEY (congregation_id) REFERENCES congregations(id)");
             echo "Column 'congregation_id' added.\n";
        } catch (PDOException $e) {
             echo "Column 'congregation_id' error: " . $e->getMessage() . "\n";
        }
    }

    // 2. Add service_report_id
    echo "Adding 'service_report_id'...\n";
    if ($driver === 'sqlite') {
        try {
            $db->exec("ALTER TABLE tithes ADD COLUMN service_report_id INTEGER REFERENCES service_reports(id) ON DELETE CASCADE");
            echo "Column 'service_report_id' added.\n";
        } catch (PDOException $e) {
             echo "Column 'service_report_id' might already exist or error: " . $e->getMessage() . "\n";
        }
    } else {
        try {
            $db->exec("ALTER TABLE tithes ADD COLUMN service_report_id INT, ADD FOREIGN KEY (service_report_id) REFERENCES service_reports(id) ON DELETE CASCADE");
             echo "Column 'service_report_id' added.\n";
        } catch (PDOException $e) {
             echo "Column 'service_report_id' error: " . $e->getMessage() . "\n";
        }
    }

    // 3. Add giver_name
    echo "Adding 'giver_name'...\n";
    if ($driver === 'sqlite') {
        try {
            $db->exec("ALTER TABLE tithes ADD COLUMN giver_name TEXT");
            echo "Column 'giver_name' added.\n";
        } catch (PDOException $e) {
             echo "Column 'giver_name' might already exist or error: " . $e->getMessage() . "\n";
        }
    } else {
        try {
            $db->exec("ALTER TABLE tithes ADD COLUMN giver_name VARCHAR(255)");
             echo "Column 'giver_name' added.\n";
        } catch (PDOException $e) {
             echo "Column 'giver_name' error: " . $e->getMessage() . "\n";
        }
    }

    // 4. Backfill congregation_id from members
    echo "Backfilling 'congregation_id' from 'members' table...\n";
    $sql = "UPDATE tithes SET congregation_id = (SELECT congregation_id FROM members WHERE members.id = tithes.member_id) WHERE member_id IS NOT NULL";
    $count = $db->exec($sql);
    echo "Updated $count rows with congregation_id from members.\n";

} catch (Exception $e) {
    echo "Critical Error: " . $e->getMessage() . "\n";
}

echo "Done.\n";
