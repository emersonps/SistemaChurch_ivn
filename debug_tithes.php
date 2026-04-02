<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'config/database.php';
$db = (new Database())->connect();

echo "--- Checking Tithes with NULL congregation_id ---\n";
$nulls = $db->query("SELECT count(*) FROM tithes WHERE congregation_id IS NULL")->fetchColumn();
echo "Tithes with NULL congregation_id: $nulls\n";

echo "\n--- Sample of NULL congregation_id rows ---\n";
$rows = $db->query("SELECT id, member_id, service_report_id, amount, type FROM tithes WHERE congregation_id IS NULL LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    print_r($row);
    if (!empty($row['service_report_id'])) {
        $stmt = $db->prepare("SELECT id, congregation_id, date FROM service_reports WHERE id = ?");
        $stmt->execute([$row['service_report_id']]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Tithe ID {$row['id']} linked to Report ID {$row['service_report_id']} -> Congregation ID: " . ($report['congregation_id'] ?? 'NOT FOUND') . "\n";
    } else {
        echo "Tithe ID {$row['id']} has NO service_report_id\n";
    }
    echo "------------------\n";
}
