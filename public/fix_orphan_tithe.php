<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../config/database.php';

try {
    $db = (new Database())->connect();
    echo "--- Cleaning up Orphaned Tithe (Member ID 2) ---\n";
    
    // First, verify again just to be safe
    $count = $db->query("SELECT count(*) FROM members WHERE id = 2")->fetchColumn();
    
    if ($count == 0) {
        // Delete the tithe record for this non-existent member
        $stmt = $db->prepare("DELETE FROM tithes WHERE member_id = 2 AND amount = 250");
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo "✅ Successfully deleted {$stmt->rowCount()} orphaned tithe record(s) for missing member ID 2.\n";
        } else {
            echo "⚠️ No records deleted. Check if it still exists.\n";
        }
    } else {
        echo "❌ Aborting: Member ID 2 actually exists! Do not delete.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
