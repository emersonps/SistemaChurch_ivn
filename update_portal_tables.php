<?php
// update_portal_tables.php
require 'config/database.php';

echo "Updating tables for Member Portal...\n";

$db = (new Database())->connect();

try {
    // Add 'password' column to members
    $db->exec("ALTER TABLE members ADD COLUMN password TEXT");
    echo "Column 'password' added to members successfully.\n";
} catch (PDOException $e) {
    echo "Column 'password' likely already exists in members: " . $e->getMessage() . "\n";
}

try {
    // Add 'congregation_id' column to events
    $db->exec("ALTER TABLE events ADD COLUMN congregation_id INTEGER DEFAULT NULL");
    echo "Column 'congregation_id' added to events successfully.\n";
} catch (PDOException $e) {
    echo "Column 'congregation_id' likely already exists in events: " . $e->getMessage() . "\n";
}

echo "Update complete.\n";
