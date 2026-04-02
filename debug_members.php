<?php
require_once __DIR__ . '/config/database.php';
$db = (new Database())->connect();

echo "Check Members Table:\n";
try {
    $cols = $db->query("DESCRIBE members")->fetchAll(PDO::FETCH_COLUMN);
    print_r($cols);
    
    echo "\nCount Active Members:\n";
    $count = $db->query("SELECT COUNT(*) FROM members WHERE status = 'active'")->fetchColumn();
    echo "Active: $count\n";
    
    echo "\nSample Member:\n";
    $sample = $db->query("SELECT * FROM members LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    print_r($sample);
    
    echo "\nTest Controller Query:\n";
    $sql = "SELECT m.id, m.name, c.name as congregation_name FROM members m LEFT JOIN congregations c ON m.congregation_id = c.id WHERE m.status = 'active' ORDER BY m.name";
    $res = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    echo "Result Count: " . count($res) . "\n";
    if (count($res) > 0) print_r($res[0]);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
