<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'config/database.php';
$db = (new Database())->connect();

echo "--- Searching for Tithe of 250.00 ---\n";
// Note: Amount might be float, try range or exact
$stmt = $db->query("SELECT * FROM tithes WHERE amount = 250");
$tithe = $stmt->fetch(PDO::FETCH_ASSOC);

if ($tithe) {
    print_r($tithe);
    
    if (!empty($tithe['member_id'])) {
        echo "Checking Member ID {$tithe['member_id']}...\n";
        $stmtM = $db->query("SELECT * FROM members WHERE id = {$tithe['member_id']}");
        $member = $stmtM->fetch(PDO::FETCH_ASSOC);
        if ($member) {
            echo "Member Found:\n";
            print_r($member);
        } else {
            echo "❌ Member ID {$tithe['member_id']} NOT FOUND in members table!\n";
        }
    } else {
        echo "Member ID is NULL.\n";
    }
} else {
    echo "Tithe not found. Listing all tithes:\n";
    $all = $db->query("SELECT id, amount, member_id, giver_name FROM tithes")->fetchAll(PDO::FETCH_ASSOC);
    print_r($all);
}
