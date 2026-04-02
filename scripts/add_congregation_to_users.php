<?php
require_once __DIR__ . '/../config/database.php';

echo "Adding 'congregation_id' column to 'users' table...\n";

$db = (new Database())->connect();
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

try {
    if ($driver === 'sqlite') {
        $sql = "ALTER TABLE users ADD COLUMN congregation_id INTEGER DEFAULT NULL";
    } else {
        // MySQL
        $sql = "ALTER TABLE users ADD COLUMN congregation_id INT DEFAULT NULL";
    }
    
    $db->exec($sql);
    echo "Column 'congregation_id' added successfully.\n";
} catch (PDOException $e) {
    // If column already exists, it might fail, which is fine
    echo "Note: " . $e->getMessage() . "\n";
}
