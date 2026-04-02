<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!file_exists('../config/database.php')) {
    echo "config/database.php NOT FOUND\n";
    exit(1);
}

require '../config/database.php';
try {
    $db = (new Database())->connect();
    
    echo "--- Checking for Tithe of 250 ---\n";
    $stmt = $db->query("SELECT * FROM tithes WHERE amount = 250");
    if (!$stmt) {
        echo "Query failed.\n";
        exit;
    }
    
    $tithe = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($tithe) {
        print_r($tithe);
        
        if (!empty($tithe['member_id'])) {
            $mem = $db->query("SELECT * FROM members WHERE id = " . $tithe['member_id'])->fetch(PDO::FETCH_ASSOC);
            if ($mem) {
                echo "Member found: " . $mem['name'] . "\n";
            } else {
                echo "Member ID " . $tithe['member_id'] . " NOT FOUND\n";
            }
        }
    } else {
        echo "No tithe with amount 250 found.\n";
        $all = $db->query("SELECT id, amount, member_id, giver_name FROM tithes")->fetchAll(PDO::FETCH_ASSOC);
        print_r($all);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
