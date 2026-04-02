<?php
// debug_expense_insert.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/config/database.php';

echo "<h1>Diagnóstico de Inserção de Despesas</h1>";

try {
    // 1. Conectar ao Banco
    echo "<h2>1. Conexão</h2>";
    $db = (new Database())->connect();
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "<p>✅ Conectado via driver: <strong>$driver</strong></p>";

    // 2. Verificar Tabela Expenses
    echo "<h2>2. Estrutura da Tabela 'expenses'</h2>";
    if ($driver === 'sqlite') {
        $stmt = $db->query("PRAGMA table_info(expenses)");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $db->query("DESCRIBE expenses");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo "<pre>";
    print_r($columns);
    echo "</pre>";

    // 3. Verificar Congregação Sede (Headquarters)
    echo "<h2>3. Resolução da Sede (Headquarters)</h2>";
    $hqId = null;
    
    // Priority 1: Explicit type
    $stmtHqType = $db->query("SELECT id, name FROM congregations WHERE type = 'headquarters' LIMIT 1");
    if ($row = $stmtHqType->fetch()) {
        $hqId = $row['id'];
        echo "<p>✅ Encontrado por tipo 'headquarters': ID {$row['id']} - {$row['name']}</p>";
    } else {
        echo "<p>⚠️ Não encontrado por tipo 'headquarters'</p>";
    }
    
    // Priority 2: Name match
    if (!$hqId) {
        $stmtHqName = $db->query("SELECT id, name FROM congregations WHERE name LIKE '%Sede%' OR name LIKE '%Matriz%' LIMIT 1");
        if ($row = $stmtHqName->fetch()) {
            $hqId = $row['id'];
            echo "<p>✅ Encontrado por nome (Sede/Matriz): ID {$row['id']} - {$row['name']}</p>";
        } else {
            echo "<p>⚠️ Não encontrado por nome (Sede/Matriz)</p>";
        }
    }
    
    // Priority 3: Fallback ID 1
    if (!$hqId) {
        $hqId = 1;
        echo "<p>⚠️ Usando Fallback ID 1</p>";
    }
    
    // Verificar se o ID realmente existe
    $stmtCheck = $db->prepare("SELECT id, name FROM congregations WHERE id = ?");
    $stmtCheck->execute([$hqId]);
    if ($row = $stmtCheck->fetch()) {
         echo "<p>✅ ID $hqId VÁLIDO na tabela congregations: {$row['name']}</p>";
    } else {
         echo "<p style='color:red'>❌ ID $hqId NÃO EXISTE na tabela congregations! Isso causará erro de Foreign Key.</p>";
         // Tentar pegar QUALQUER ID válido
         $stmtAny = $db->query("SELECT id, name FROM congregations LIMIT 1");
         if ($rowAny = $stmtAny->fetch()) {
             $hqId = $rowAny['id'];
             echo "<p>🔄 Usando ID alternativo existente: $hqId - {$rowAny['name']}</p>";
         } else {
             echo "<p style='color:red'>❌ NÃO EXISTEM CONGREGAÇÕES NO BANCO!</p>";
             $hqId = null;
         }
    }

    // 4. Teste de Inserção
    echo "<h2>4. Teste de Inserção</h2>";
    
    if ($hqId) {
        $expenseDate = date('Y-m-d');
        $description = "TESTE DE DEBUG - " . date('H:i:s');
        $notes = 'Registro de teste gerado pelo script de diagnóstico';
        $category = 'Contas Fixas';
        $amount = 1.00;

        $sql = "INSERT INTO expenses (description, amount, expense_date, category, congregation_id, notes) VALUES (?, ?, ?, ?, ?, ?)";
        echo "<p>SQL: $sql</p>";
        echo "<p>Dados: " . json_encode([$description, $amount, $expenseDate, $category, $hqId, $notes]) . "</p>";

        $stmtExpense = $db->prepare($sql);
        try {
            $stmtExpense->execute([
                $description,
                $amount,
                $expenseDate,
                $category,
                $hqId,
                $notes
            ]);
            echo "<p style='color:green'>✅ Inserção realizada com SUCESSO! Verifique o relatório.</p>";
        } catch (PDOException $e) {
            echo "<p style='color:red'>❌ Erro na inserção: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Inserção abortada: Sem ID de congregação válido.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erro Geral: " . $e->getMessage() . "</p>";
}
?>