<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../config/database.php';

try {
    $db = (new Database())->connect();
    echo "--- Checking Member ID 2 ---\n";
    $stmt = $db->query("SELECT * FROM members WHERE id = 2");
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($member) {
        echo "Member FOUND:\n";
        print_r($member);
    } else {
        echo "❌ Member ID 2 NOT FOUND in members table.\n";
        
        echo "\n--- Listing all members ---\n";
        $all = $db->query("SELECT id, name FROM members")->fetchAll(PDO::FETCH_ASSOC);
        print_r($all);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
