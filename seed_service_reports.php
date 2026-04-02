<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    
    echo "--- Inserindo Relatórios de Culto de Teste ---\n";
    
    // Buscar uma congregação existente para vincular
    $congId = $db->query("SELECT id FROM congregations LIMIT 1")->fetchColumn();
    if (!$congId) {
        $db->query("INSERT INTO congregations (name, type) VALUES ('IMPVC Sede', 'Sede')");
        $congId = $db->lastInsertId();
    }
    
    // Verificar se existe um usuário para created_by
    $userId = $db->query("SELECT id FROM users LIMIT 1")->fetchColumn();
    if (!$userId) {
        $userId = 1; // Fallback
    }

    $stmt = $db->prepare("INSERT INTO service_reports (
        congregation_id, date, time, leader_name, preacher_name,
        attendance_men, attendance_women, attendance_youth, attendance_children, attendance_visitors,
        total_attendance, notes, created_by
    ) VALUES (
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, ?, ?
    )");

    // Gerar relatórios para os últimos 10 domingos e terças
    $startDate = strtotime('-10 weeks');

    for ($i = 0; $i < 20; $i++) {
        // Alternar entre Domingo e Terça
        $dayOffset = ($i % 2 == 0) ? 'Sunday' : 'Tuesday';
        $currentDate = strtotime("+$i weeks $dayOffset", $startDate);
        
        // Se data futura, parar
        if ($currentDate > time()) break;
        
        $date = date('Y-m-d', $currentDate);
        $time = ($dayOffset == 'Sunday') ? '18:00' : '19:30';
        
        $leader = ($i % 2 == 0) ? 'Pr. João' : 'Ev. Marcos';
        $preacher = ($i % 2 == 0) ? 'Pr. João' : 'Miss. Ana';
        
        $men = rand(10, 50);
        $women = rand(15, 60);
        $youth = rand(5, 30);
        $children = rand(5, 20);
        $visitors = rand(0, 10);
        
        $total = $men + $women + $youth + $children + $visitors;
        
        $obs = "Culto abençoado de " . ($dayOffset == 'Sunday' ? 'Família' : 'Doutrina');

        $stmt->execute([
            $congId,
            $date,
            $time,
            $leader,
            $preacher,
            $men,
            $women,
            $youth,
            $children,
            $visitors,
            $total,
            $obs,
            $userId
        ]);
        
        echo "✅ Relatório inserido: $date ($time) - Total: $total pessoas\n";
    }
    
    echo "\n--- Concluído! Relatórios de culto adicionados. ---\n";

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
