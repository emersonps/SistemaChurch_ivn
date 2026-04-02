<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();

echo "Gerando histórico de Aulas e Chamadas para a EBD...\n";

// Pegar todas as classes ativas
$stmt = $pdo->query("SELECT id, name FROM ebd_classes WHERE status = 'active'");
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$temas_aulas = [
    'O Princípio da Criação',
    'A Queda do Homem',
    'A Promessa da Salvação',
    'A Vida de Abraão',
    'O Êxodo e a Libertação',
    'Os Dez Mandamentos',
    'Os Juízes e os Reis',
    'O Reino de Davi',
    'Os Profetas Maiores',
    'O Nascimento de Jesus',
    'Os Milagres de Cristo',
    'As Parábolas do Reino',
    'A Crucificação e Ressurreição',
    'O Derramamento do Espírito Santo',
    'A Igreja Primitiva',
    'As Viagens de Paulo',
    'As Cartas Paulinas',
    'O Apocalipse e a Esperança',
    'A Vida de Oração',
    'O Fruto do Espírito'
];

// Vamos gerar 4 aulas (uma para cada domingo do mês atual) para cada classe
$domingos = [];
$start = new DateTime('first day of this month');
$end = new DateTime('last day of this month');
$interval = DateInterval::createFromDateString('1 day');
$period = new DatePeriod($start, $interval, $end);

foreach ($period as $dt) {
    if ($dt->format('N') == 7) { // 7 = Domingo
        $domingos[] = $dt->format('Y-m-d');
    }
}

$aulas_criadas = 0;
$presencas_criadas = 0;

foreach ($classes as $classe) {
    $class_id = $classe['id'];
    
    // Pegar alunos matriculados nesta classe
    $stmtS = $pdo->prepare("SELECT member_id FROM ebd_students WHERE class_id = ? AND status = 'active'");
    $stmtS->execute([$class_id]);
    $alunos = $stmtS->fetchAll(PDO::FETCH_COLUMN);
    
    // Se a classe não tiver alunos, a gente pula (ou adicionamos depois, mas o seeder de membros já deve ter posto alguns)
    if (empty($alunos)) {
        continue;
    }

    foreach ($domingos as $idx => $data_aula) {
        $tema = $temas_aulas[array_rand($temas_aulas)] . " (Classe " . $classe['name'] . ")";
        
        $visitantes = rand(0, 3);
        $oferta = rand(1500, 8000) / 100;
        
        // Inserir a Aula
        $stmtIns = $pdo->prepare("INSERT INTO ebd_lessons (class_id, lesson_date, topic, visitors_count, bibles_count, magazines_count, offerings, created_at) VALUES (?, ?, ?, ?, 0, 0, ?, NOW())");
        $stmtIns->execute([$class_id, $data_aula, $tema, $visitantes, $oferta]);
        $lesson_id = $pdo->lastInsertId();
        $aulas_criadas++;
        
        $presentes_count = 0;
        $biblias_count = 0;
        $revistas_count = 0;
        
        // Fazer a chamada para cada aluno
        foreach ($alunos as $aluno_id) {
            // 80% de chance do aluno estar presente
            $presente = (rand(1, 100) <= 80) ? 1 : 0;
            
            $trouxe_biblia = 0;
            $trouxe_revista = 0;
            
            if ($presente) {
                $presentes_count++;
                $trouxe_biblia = (rand(1, 100) <= 90) ? 1 : 0; // 90% dos presentes trazem bíblia
                $trouxe_revista = (rand(1, 100) <= 60) ? 1 : 0; // 60% trazem revista
                
                if ($trouxe_biblia) $biblias_count++;
                if ($trouxe_revista) $revistas_count++;
            }
            
            $stmtAtt = $pdo->prepare("INSERT INTO ebd_attendance (lesson_id, student_id, present, brought_bible, brought_magazine) VALUES (?, ?, ?, ?, ?)");
            $stmtAtt->execute([$lesson_id, $aluno_id, $presente, $trouxe_biblia, $trouxe_revista]);
            $presencas_criadas++;
        }
        
        // Atualizar o total de bíblias e revistas da aula (alunos + visitantes que talvez trouxeram)
        $biblias_visitantes = floor($visitantes * 0.5);
        $revistas_visitantes = 0;
        
        $total_biblias = $biblias_count + $biblias_visitantes;
        $total_revistas = $revistas_count + $revistas_visitantes;
        
        $pdo->query("UPDATE ebd_lessons SET bibles_count = $total_biblias, magazines_count = $total_revistas WHERE id = $lesson_id");
    }
}

echo "Foram geradas $aulas_criadas novas aulas e $presencas_criadas registros de chamada (presenças/faltas) para os domingos deste mês!\n";
