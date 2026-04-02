<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();

echo "Updating service reports dates to current month...\n";

// Get all service reports
$stmt = $pdo->query("SELECT id FROM service_reports");
$reports = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($reports as $id) {
    // Generate a random date in the current month
    $start_of_month = strtotime(date('Y-m-01'));
    $today = time();
    $random_timestamp = rand($start_of_month, $today);
    
    $new_date = date('Y-m-d H:i:s', $random_timestamp);
    
    $pdo->query("UPDATE service_reports SET date = '$new_date' WHERE id = $id");
}

echo "Dates updated successfully! They will now show up in the General Report.\n";
