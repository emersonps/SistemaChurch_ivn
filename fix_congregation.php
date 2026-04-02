<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    
    // Mover despesas
    $stmt1 = $db->prepare("UPDATE expenses SET congregation_id = 1 WHERE congregation_id = 6 AND (notes = 'Importado via Painel Dev' OR notes = 'Registro manual solicitado pelo usuário' OR expense_date LIKE '2024-%')");
    $stmt1->execute();
    echo "Despesas movidas para a Congregação 1: " . $stmt1->rowCount() . "\n";
    
    // Mover entradas/ofertas (antes tinha type='Oferta' mas o update pegou 0 linhas, vamos tentar geral do ano)
    $stmt2 = $db->prepare("UPDATE tithes SET congregation_id = 1 WHERE payment_date LIKE '2024-%'");
    $stmt2->execute();
    echo "Entradas/Ofertas movidas para a Congregação 1: " . $stmt2->rowCount() . "\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}