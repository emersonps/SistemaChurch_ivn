<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();

echo "Atualizando ofertas da EBD...\n";

// Colocar um valor de oferta aleatório (entre R$ 20 e R$ 150) para todas as aulas
$stmt = $pdo->query("SELECT id FROM ebd_lessons WHERE offerings IS NULL OR offerings = 0");
$lessons = $stmt->fetchAll(PDO::FETCH_COLUMN);

$count = 0;
foreach ($lessons as $id) {
    $random_offering = rand(2000, 15000) / 100; // Valor entre 20.00 e 150.00
    
    // Além da oferta, vamos adicionar visitantes (0 a 4), bíblias trazidas (maioria) e revistas (alguns)
    $visitors = rand(0, 4);
    
    // Pegar quantos alunos estavam presentes para não colocar mais bíblias que alunos
    $stmtAtt = $pdo->prepare("SELECT COUNT(*) FROM ebd_attendance WHERE lesson_id = ? AND present = 1");
    $stmtAtt->execute([$id]);
    $present_count = $stmtAtt->fetchColumn() + $visitors;
    
    // Quantidade de bíblias (60% a 100% dos presentes)
    $bibles = floor($present_count * (rand(60, 100) / 100));
    
    // Quantidade de revistas (40% a 90% dos presentes)
    $magazines = floor($present_count * (rand(40, 90) / 100));
    
    $updateStmt = $pdo->prepare("UPDATE ebd_lessons SET offerings = ?, visitors_count = ?, bibles_count = ?, magazines_count = ? WHERE id = ?");
    $updateStmt->execute([$random_offering, $visitors, $bibles, $magazines, $id]);
    $count++;
}

echo "Foram atualizadas $count aulas da Escola Bíblica com ofertas, visitantes, bíblias e revistas.\n";
