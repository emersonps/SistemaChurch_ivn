<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();

echo "Ajustando as congregações dos grupos...\n";

// Pegar todos os grupos atuais
$stmt = $pdo->query("SELECT id, name FROM `groups`");
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// No script original de seed de grupos eu criei os nomes no formato "Célula Vida - Cong. 4"
// Vamos extrair o ID da congregação do nome e atualizar o campo congregation_id
$count = 0;
foreach ($groups as $g) {
    if (preg_match('/Cong\. (\d+)/', $g['name'], $matches)) {
        $cong_id = $matches[1];
        
        // Também vamos setar um dia da semana para não dar mais o erro de nulo
        $days = ['Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado', 'Domingo'];
        $meeting_day = $days[array_rand($days)];
        
        $updateStmt = $pdo->prepare("UPDATE `groups` SET congregation_id = ?, meeting_day = ?, meeting_time = '19:30:00' WHERE id = ?");
        $updateStmt->execute([$cong_id, $meeting_day, $g['id']]);
        $count++;
    }
}

echo "Foram ajustados $count grupos com suas congregações, dias e horários corretos.\n";
