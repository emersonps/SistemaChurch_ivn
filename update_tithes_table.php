<?php
// update_tithes_table.php
require 'config/database.php';

echo "Updating tithes table...\n";

$db = (new Database())->connect();

try {
    // Add 'type' column
    $db->exec("ALTER TABLE tithes ADD COLUMN type TEXT DEFAULT 'dizimo'");
    echo "Column 'type' added successfully.\n";
} catch (PDOException $e) {
    echo "Column 'type' likely already exists: " . $e->getMessage() . "\n";
}

echo "Update complete.\n";
