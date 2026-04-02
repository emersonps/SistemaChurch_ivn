<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    
    // 1. Find the class
    $stmt = $db->query("SELECT id, name, congregation_id FROM ebd_classes");
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($classes)) {
        echo "Nenhuma classe encontrada.\n";
        exit;
    }
    
    foreach ($classes as $class) {
        echo "Classe Encontrada: [ID: {$class['id']}] {$class['name']} (Congregação ID: {$class['congregation_id']})\n";
        
        // 2. Count students in ebd_students table
        $stmtCount = $db->prepare("SELECT COUNT(*) FROM ebd_students WHERE class_id = ?");
        $stmtCount->execute([$class['id']]);
        $total = $stmtCount->fetchColumn();
        echo "-> Total de registros na tabela 'ebd_students': $total\n";
        
        // 3. List students with details
        $sql = "SELECT s.id as student_record_id, s.member_id, s.status as enrollment_status, 
                       m.name as member_name, m.status as member_status
                FROM ebd_students s
                LEFT JOIN members m ON s.member_id = m.id
                WHERE s.class_id = ?";
                
        $stmtStudents = $db->prepare($sql);
        $stmtStudents->execute([$class['id']]);
        $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($students)) {
            echo "-> Nenhum aluno listado na query detalhada.\n";
        } else {
            echo "-> Detalhes dos Alunos:\n";
            foreach ($students as $s) {
                $mName = $s['member_name'] ?? 'Membro Excluído (NULL)';
                $mStatus = $s['member_status'] ?? 'N/A';
                echo "   - ID Matrícula: {$s['student_record_id']} | Membro ID: {$s['member_id']} | Nome: $mName | Status Membro: $mStatus | Status Matrícula: {$s['enrollment_status']}\n";
            }
        }
        echo "--------------------------------------------------\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
