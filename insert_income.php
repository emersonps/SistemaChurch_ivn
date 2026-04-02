<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    
    // Dados da entrada
    $member_id = null;
    $giver_name = 'Banca (kikão)';
    $amount = 80.00;
    $payment_date = '2024-02-03';
    $payment_method = 'PIX';
    $type = 'Oferta';
    $congregation_id = 6; // ID da Congregação 2
    $notes = 'Banca (kikão)';

    $stmt = $db->prepare("INSERT INTO tithes (member_id, amount, payment_date, payment_method, type, notes, congregation_id, giver_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$member_id, $amount, $payment_date, $payment_method, $type, $notes, $congregation_id, $giver_name]);
    
    echo "Linhas afetadas: " . $stmt->rowCount() . "\n";
    echo "Entrada registrada com sucesso: $giver_name - R$ $amount em $payment_date\n";

} catch (Exception $e) {
    echo "Erro ao registrar entrada: " . $e->getMessage() . "\n";
}
