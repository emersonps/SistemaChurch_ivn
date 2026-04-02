<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    
    echo "--- INICIANDO LIMPEZA DE REGISTROS ÓRFÃOS DA EBD ---\n\n";
    
    // 1. Identificar alunos órfãos (matrículas onde o membro não existe)
    $sqlOrphans = "SELECT s.id, s.class_id, s.member_id 
                   FROM ebd_students s 
                   LEFT JOIN members m ON s.member_id = m.id 
                   WHERE m.id IS NULL";
                   
    $stmt = $db->query($sqlOrphans);
    $orphans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($orphans)) {
        echo "Nenhum aluno órfão encontrado.\n";
    } else {
        echo "Encontrados " . count($orphans) . " registros de alunos órfãos:\n";
        foreach ($orphans as $o) {
            echo "- Matrícula ID: {$o['id']} (Membro ID: {$o['member_id']} - Inexistente)\n";
        }
        
        // Deletar (Abordagem compatível com MySQL - DELETE JOIN)
        // O MySQL não permite deletar da mesma tabela que está no subselect do WHERE IN de forma direta
        
        $sqlDelete = "DELETE s FROM ebd_students s 
                      LEFT JOIN members m ON s.member_id = m.id 
                      WHERE m.id IS NULL";
                      
        $count = $db->exec($sqlDelete);
        echo "\n>>> Removidos $count registros de alunos órfãos.\n";
    }
    
    echo "\n--------------------------------------------------\n";
    
    // 2. Identificar professores órfãos (opcional, mas bom garantir)
    $sqlTeachers = "SELECT t.id, t.class_id, t.member_id 
                    FROM ebd_teachers t 
                    LEFT JOIN members m ON t.member_id = m.id 
                    WHERE m.id IS NULL";
                    
    $stmtT = $db->query($sqlTeachers);
    $orphansT = $stmtT->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($orphansT)) {
        echo "Nenhum professor órfão encontrado.\n";
    } else {
        echo "Encontrados " . count($orphansT) . " registros de professores órfãos:\n";
        // Deletar
        $sqlDeleteT = "DELETE t FROM ebd_teachers t 
                       LEFT JOIN members m ON t.member_id = m.id 
                       WHERE m.id IS NULL";
                       
        $countT = $db->exec($sqlDeleteT);
        echo "\n>>> Removidos $countT registros de professores órfãos.\n";
    }

    echo "\n--- LIMPEZA CONCLUÍDA ---\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
