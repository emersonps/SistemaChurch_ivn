<?php
// fix_payment_expense.php
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/config/database.php';

echo "<h1>Corretor de Despesa de Pagamento do Sistema</h1>";

try {
    $db = (new Database())->connect();
    
    // 1. Achar uma congregação válida
    $hqId = null;
    // Tenta Sede
    $stmt = $db->query("SELECT id FROM congregations WHERE type = 'headquarters' OR name LIKE '%Sede%' OR name LIKE '%Matriz%' LIMIT 1");
    if ($row = $stmt->fetch()) {
        $hqId = $row['id'];
    } else {
        // Tenta qualquer uma
        $stmt = $db->query("SELECT id FROM congregations LIMIT 1");
        if ($row = $stmt->fetch()) {
            $hqId = $row['id'];
        }
    }
    
    echo "<p>Congregação vinculada ID: " . ($hqId ? $hqId : 'NULL') . "</p>";

    // 2. Dados da Despesa
    $month = date('Y-m'); // Mês atual
    $description = "Pagamento Sistema - Mensalidade $month (Correção)";
    
    // Verificar se já existe para não duplicar
    $stmtCheck = $db->prepare("SELECT id FROM expenses WHERE description = ?");
    $stmtCheck->execute([$description]);
    if ($stmtCheck->fetch()) {
        die("<h3 style='color:orange'>⚠️ Essa despesa já consta no banco de dados! Verifique o relatório de Saídas.</h3>");
    }

    // 3. Inserir
    $sql = "INSERT INTO expenses (description, amount, expense_date, category, congregation_id, notes) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        $description,
        59.99,
        date('Y-m-d'),
        'Contas Fixas',
        $hqId,
        'Inserção manual via script de correção'
    ]);

    echo "<h2 style='color:green'>✅ Sucesso! Despesa registrada no relatório.</h2>";
    echo "<p>Pode apagar este arquivo agora.</p>";

} catch (Exception $e) {
    echo "<h2 style='color:red'>❌ Erro: " . $e->getMessage() . "</h2>";
}
?>