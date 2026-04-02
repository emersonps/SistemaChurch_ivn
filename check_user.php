<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    
    // Ver qual congregação a secretaria_n2 pertence
    $stmt = $db->prepare("SELECT id, username, congregation_id FROM users WHERE username = 'secretaria_n2' OR username LIKE '%n2%'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Usuário encontrado:\n";
    print_r($user);
    
    // Ver em qual congregação eu importei as despesas de 2024 (ID 6)
    $stmt = $db->prepare("SELECT COUNT(*) FROM expenses WHERE notes = 'Importado via Painel Dev' OR notes = 'Registro manual solicitado pelo usuário'");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    echo "\nTotal de despesas importadas recentemente (em qualquer congregação): $count\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}