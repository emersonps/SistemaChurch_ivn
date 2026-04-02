<?php
// scripts/sync_system_payments.php

require_once __DIR__ . '/../config/database.php';

try {
    echo "Iniciando sincronização de pagamentos do sistema para despesas...\n";
    $db = (new Database())->connect();
    
    // 1. Find Headquarters ID
    $hqId = 1; 
    $stmtHq = $db->query("SELECT id FROM congregations WHERE type = 'headquarters' OR name LIKE '%Sede%' OR name LIKE '%Matriz%' LIMIT 1");
    if ($row = $stmtHq->fetch()) {
        $hqId = $row['id'];
        echo "Sede identificada: ID $hqId\n";
    } else {
        echo "Sede não identificada, usando ID 1 padrão.\n";
    }

    // 2. Get all PAID system payments
    $payments = $db->query("SELECT * FROM system_payments WHERE status = 'paid'")->fetchAll();
    
    $count = 0;
    
    foreach ($payments as $p) {
        $month = $p['reference_month'];
        $amount = $p['amount'];
        // Use payment_date if available, otherwise current date (fallback, though payment_date should be there)
        $date = !empty($p['payment_date']) ? date('Y-m-d', strtotime($p['payment_date'])) : date('Y-m-d');
        
        $description = "Pagamento Sistema - Mensalidade $month";
        
        // Check if expense exists
        $stmtCheck = $db->prepare("SELECT id FROM expenses WHERE description = ? AND amount = ?");
        $stmtCheck->execute([$description, $amount]);
        
        if (!$stmtCheck->fetch()) {
            // Not found, insert it
            $stmtInsert = $db->prepare("INSERT INTO expenses (description, amount, expense_date, category, congregation_id, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtInsert->execute([
                $description,
                $amount,
                $date,
                'Contas Fixas',
                $hqId,
                'Sincronização automática de pagamentos passados'
            ]);
            echo "Criada despesa para: $description ($date)\n";
            $count++;
        } else {
            echo "Despesa já existe para: $description\n";
        }
    }
    
    echo "Sincronização concluída. $count registros criados.\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
