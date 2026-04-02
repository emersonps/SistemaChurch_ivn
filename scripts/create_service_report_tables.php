<?php
require_once __DIR__ . '/../config/database.php';

echo "Creating 'service_reports' and related tables...\n";

$db = (new Database())->connect();
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

// Service Reports Table
if ($driver === 'sqlite') {
    $sqlReports = "CREATE TABLE IF NOT EXISTS service_reports (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        congregation_id INTEGER NOT NULL,
        date DATE NOT NULL,
        time TIME NOT NULL,
        leader_name TEXT,
        preacher_name TEXT,
        attendance_men INTEGER DEFAULT 0,
        attendance_women INTEGER DEFAULT 0,
        attendance_youth INTEGER DEFAULT 0,
        attendance_children INTEGER DEFAULT 0,
        attendance_visitors INTEGER DEFAULT 0,
        total_attendance INTEGER DEFAULT 0,
        notes TEXT,
        created_by INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (congregation_id) REFERENCES congregations(id),
        FOREIGN KEY (created_by) REFERENCES users(id)
    )";
} else {
    $sqlReports = "CREATE TABLE IF NOT EXISTS service_reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        congregation_id INT NOT NULL,
        date DATE NOT NULL,
        time TIME NOT NULL,
        leader_name VARCHAR(255),
        preacher_name VARCHAR(255),
        attendance_men INT DEFAULT 0,
        attendance_women INT DEFAULT 0,
        attendance_youth INT DEFAULT 0,
        attendance_children INT DEFAULT 0,
        attendance_visitors INT DEFAULT 0,
        total_attendance INT DEFAULT 0,
        notes TEXT,
        created_by INT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (congregation_id) REFERENCES congregations(id),
        FOREIGN KEY (created_by) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
}

try {
    $db->exec($sqlReports);
    echo "Table 'service_reports' created.\n";
} catch (PDOException $e) {
    echo "Error creating 'service_reports': " . $e->getMessage() . "\n";
}

// Service People Actions Table
if ($driver === 'sqlite') {
    $sqlPeople = "CREATE TABLE IF NOT EXISTS service_people_actions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        service_report_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        action_type TEXT NOT NULL, -- visitor, reconciled, accepted_jesus, disciplined, dismissed
        observation TEXT,
        FOREIGN KEY (service_report_id) REFERENCES service_reports(id) ON DELETE CASCADE
    )";
} else {
    $sqlPeople = "CREATE TABLE IF NOT EXISTS service_people_actions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        service_report_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        action_type VARCHAR(50) NOT NULL,
        observation TEXT,
        FOREIGN KEY (service_report_id) REFERENCES service_reports(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
}

try {
    $db->exec($sqlPeople);
    echo "Table 'service_people_actions' created.\n";
} catch (PDOException $e) {
    echo "Error creating 'service_people_actions': " . $e->getMessage() . "\n";
}

// Modify Tithes Table
try {
    if ($driver === 'sqlite') {
        // SQLite doesn't support modifying column constraints easily.
        // We will just add new columns.
        // Handling NULL member_id in SQLite: existing columns are usually nullable unless defined NOT NULL.
        // Let's check schema. Assuming member_id might be INTEGER.
        
        $db->exec("ALTER TABLE tithes ADD COLUMN service_report_id INTEGER DEFAULT NULL REFERENCES service_reports(id) ON DELETE SET NULL");
        $db->exec("ALTER TABLE tithes ADD COLUMN giver_name TEXT DEFAULT NULL");
        
        // Note: SQLite columns are nullable by default unless specified. 
        // If member_id was created as "INTEGER NOT NULL", we can't change it without recreating table.
        // But let's assume standard creation or try to work with it.
        // If it fails, we might need a more complex migration script.
    } else {
        // MySQL
        $db->exec("ALTER TABLE tithes ADD COLUMN service_report_id INT DEFAULT NULL");
        $db->exec("ALTER TABLE tithes ADD COLUMN giver_name VARCHAR(255) DEFAULT NULL");
        $db->exec("ALTER TABLE tithes MODIFY COLUMN member_id INT NULL"); // Make member_id nullable
        $db->exec("ALTER TABLE tithes ADD FOREIGN KEY (service_report_id) REFERENCES service_reports(id) ON DELETE SET NULL");
    }
    echo "Table 'tithes' modified.\n";
} catch (PDOException $e) {
    echo "Error modifying 'tithes': " . $e->getMessage() . "\n";
}
