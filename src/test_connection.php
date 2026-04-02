<?php
require_once __DIR__ . '/../config/database.php';

echo "--- INICIANDO TESTE DE CONEXÃO E REGRAS DE NEGÓCIO ---\n";

try {
    // 1. Conexão
    $db = (new Database())->connect();
    echo "✅ [SUCESSO] Conexão com SQLite estabelecida (database/SistemaChurch.db).\n";

    // 2. Verificar Tabelas Existentes
    $tables = ['users', 'congregations', 'members', 'tithes', 'events'];
    echo "\n--- Verificando Estrutura do Banco ---\n";
    foreach ($tables as $table) {
        $stmt = $db->query("SELECT count(*) FROM sqlite_master WHERE type='table' AND name='$table'");
        if ($stmt->fetchColumn() > 0) {
            echo "✅ Tabela '$table' existe.\n";
        } else {
            echo "❌ Tabela '$table' NÃO encontrada!\n";
        }
    }

    // 3. Teste de Inserção (Regra de Negócio: Hierarquia Igreja -> Membro -> Dízimo)
    echo "\n--- Testando Inserção de Dados (Hierarquia) ---\n";
    
    // Inserir Congregação
    $congName = "Congregação de Teste " . date('H:i:s');
    $db->prepare("INSERT INTO congregations (name, type) VALUES (?, 'congregation')")->execute([$congName]);
    $congId = $db->lastInsertId();
    echo "✅ Congregação criada: ID $congId - $congName\n";

    // Inserir Membro na Congregação
    $membName = "Membro Teste";
    $membEmail = "teste" . time() . "@email.com";
    $db->prepare("INSERT INTO members (congregation_id, name, email, status, is_baptized) VALUES (?, ?, ?, 'active', 1)")->execute([$congId, $membName, $membEmail]);
    $membId = $db->lastInsertId();
    echo "✅ Membro criado: ID $membId - $membName (Vinculado à Congregação $congId)\n";

    // Inserir Dízimo para o Membro
    $amount = 250.00;
    $db->prepare("INSERT INTO tithes (member_id, amount, payment_date, payment_method, notes) VALUES (?, ?, date('now'), 'Pix', 'Teste de conexão')")->execute([$membId, $amount]);
    $titheId = $db->lastInsertId();
    echo "✅ Dízimo lançado: ID $titheId - Valor R$ $amount (Vinculado ao Membro $membId)\n";

    // 4. Teste de Leitura (Relatório)
    echo "\n--- Testando Leitura Relacional ---\n";
    $sql = "SELECT t.amount, t.payment_date, m.name as member_name, c.name as cong_name 
            FROM tithes t
            JOIN members m ON t.member_id = m.id
            JOIN congregations c ON m.congregation_id = c.id
            WHERE t.id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$titheId]);
    $result = $stmt->fetch();

    if ($result) {
        echo "✅ Leitura bem sucedida:\n";
        echo "   - Membro: " . $result['member_name'] . "\n";
        echo "   - Congregação: " . $result['cong_name'] . "\n";
        echo "   - Dízimo: R$ " . number_format($result['amount'], 2, ',', '.') . "\n";
        echo "   - Data: " . $result['payment_date'] . "\n";
    } else {
        echo "❌ Falha ao ler dados relacionados.\n";
    }

} catch (Exception $e) {
    echo "❌ ERRO CRÍTICO: " . $e->getMessage() . "\n";
}

echo "\n--- FIM DO TESTE ---\n";
