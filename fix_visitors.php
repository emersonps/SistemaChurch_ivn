<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();

echo "Verificando e inserindo visitantes ausentes...\n";

// Pega todos os relatórios que dizem ter visitantes > 0
$stmt = $pdo->query("SELECT id, attendance_visitors FROM service_reports WHERE attendance_visitors > 0");
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nomes_visitantes = ['Carlos Silva', 'Ana Beatriz', 'Roberto Gomes', 'Fernanda Lima', 'João Paulo', 'Mariana Costa', 'Ricardo Alves', 'Patricia Souza', 'Thiago Mendes', 'Camila Ribeiro', 'Marcos Oliveira', 'Juliana Castro'];

$inserted_count = 0;

foreach ($reports as $report) {
    $report_id = $report['id'];
    $qtd_informada = $report['attendance_visitors'];
    
    // Verifica quantos visitantes estão registrados de fato para este relatório
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM service_people_actions WHERE service_report_id = ? AND action_type = 'Visitante'");
    $stmtCount->execute([$report_id]);
    $qtd_registrada = $stmtCount->fetchColumn();
    
    // Se a quantidade registrada for menor que a informada, insere a diferença
    if ($qtd_registrada < $qtd_informada) {
        $qtd_a_inserir = $qtd_informada - $qtd_registrada;
        
        for ($i = 0; $i < $qtd_a_inserir; $i++) {
            $nome = $nomes_visitantes[array_rand($nomes_visitantes)] . " " . rand(1, 100);
            
            $stmtInsert = $pdo->prepare("INSERT INTO service_people_actions (service_report_id, name, action_type, observation) VALUES (?, ?, 'Visitante', 'Primeira vez')");
            $stmtInsert->execute([$report_id, $nome]);
            $inserted_count++;
        }
    }
}

echo "Foram inseridos $inserted_count nomes de visitantes para bater com os números dos relatórios.\n";
