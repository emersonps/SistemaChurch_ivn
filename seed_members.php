<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    
    echo "--- Inserindo 20 Membros de Teste ---\n";
    
    // Lista de Nomes e Sobrenomes para gerar aleatoriamente
    $firstNames = ['Ana', 'Bruno', 'Carlos', 'Daniela', 'Eduardo', 'Fernanda', 'Gabriel', 'Helena', 'Igor', 'Julia', 'Lucas', 'Mariana', 'Nicolas', 'Olivia', 'Pedro', 'Quintino', 'Rafael', 'Sofia', 'Tiago', 'Ursula'];
    $lastNames = ['Silva', 'Santos', 'Oliveira', 'Souza', 'Rodrigues', 'Ferreira', 'Alves', 'Pereira', 'Lima', 'Gomes', 'Costa', 'Ribeiro', 'Martins', 'Carvalho', 'Almeida', 'Lopes', 'Soares', 'Fernandes', 'Vieira', 'Barbosa'];
    
    $roles = ['Membro', 'Auxiliar', 'Diácono', 'Cooperador'];
    $statuses = ['Congregando', 'Congregando', 'Congregando', 'Desligado']; // Mais chance de ser ativo
    
    // Buscar uma congregação existente para vincular
    $congId = $db->query("SELECT id FROM congregations LIMIT 1")->fetchColumn();
    if (!$congId) {
        // Se não tiver, cria uma Sede
        $db->query("INSERT INTO congregations (name, type) VALUES ('IMPVC Sede', 'Sede')");
        $congId = $db->lastInsertId();
    }

    $stmt = $db->prepare("INSERT INTO members (
        name, email, phone, cpf, birth_date, 
        address, address_number, neighborhood, city, state, zip_code,
        congregation_id, role, status, admission_method, baptism_date
    ) VALUES (
        ?, ?, ?, ?, ?, 
        ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?
    )");

    for ($i = 0; $i < 20; $i++) {
        $name = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
        // Garante e-mail único
        $email = strtolower(str_replace(' ', '.', $name)) . rand(100, 999) . '@email.com';
        
        // Gerar CPF válido (apenas formato, não validado na Receita)
        $cpf = sprintf('%03d.%03d.%03d-%02d', rand(0, 999), rand(0, 999), rand(0, 999), rand(0, 99));
        
        $phone = sprintf('(11) 9%04d-%04d', rand(0, 9999), rand(0, 9999));
        $birthDate = date('Y-m-d', strtotime('-' . rand(18, 70) . ' years'));
        
        $role = $roles[array_rand($roles)];
        $status = $statuses[array_rand($statuses)];
        
        $stmt->execute([
            $name, 
            $email, 
            $phone, 
            $cpf, 
            $birthDate,
            'Rua Exemplo', 
            rand(10, 5000), 
            'Centro', 
            'São Paulo', 
            'SP', 
            '01000-000',
            $congId,
            $role,
            $status,
            'Aclamação',
            date('Y-m-d', strtotime('-' . rand(1, 10) . ' years'))
        ]);
        
        echo "✅ Membro inserido: $name ($role)\n";
    }
    
    echo "\n--- Concluído! 20 membros adicionados. ---\n";

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
