<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    
    // Dados da despesa
    $description = 'Instalação do condicionador';
    $amount = 450.00;
    $expense_date = '2024-03-10';
    $category = 'Manutenção';
    $congregation_id = 6; // ID da Congregação 2 (única encontrada)
    $notes = 'Registro manual solicitado pelo usuário';

    $stmt = $db->prepare("INSERT INTO expenses (description, amount, expense_date, category, congregation_id, notes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$description, $amount, $expense_date, $category, $congregation_id, $notes]);
    
    echo "Linhas afetadas: " . $stmt->rowCount() . "\n";
    echo "Despesa registrada com sucesso: $description - R$ $amount em $expense_date\n";
} catch (Exception $e) {
    echo "Erro ao registrar despesa: " . $e->getMessage() . "\n";
}
