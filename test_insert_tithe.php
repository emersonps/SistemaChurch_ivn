<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    
    echo "Testing Insertion...\n";
    
    // Simulate insertion for 2024
    $member_id = null; // Visitor
    $amount = 100.00;
    $payment_date = '2024-06-15';
    $payment_method = 'Dinheiro';
    $type = 'Dízimo';
    $notes = 'Teste Script 2024';
    $congregation_id = 2; // IMPVC Sede
    $giver_name = 'Visitante Teste 2024';
    
    $stmt = $db->prepare("INSERT INTO tithes (member_id, amount, payment_date, payment_method, type, notes, congregation_id, giver_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$member_id, $amount, $payment_date, $payment_method, $type, $notes, $congregation_id, $giver_name]);
    
    echo "Inserted record for 2024-06-15 with CongID 2.\n";
    
    // Verify
    $sql = "SELECT id, payment_date, congregation_id FROM tithes WHERE payment_date = '2024-06-15' AND congregation_id = 2";
    $stmt = $db->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "✅ Record FOUND: ID " . $result['id'] . "\n";
    } else {
        echo "❌ Record NOT FOUND!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
