<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();

echo "Starting Service Reports Seeding...\n";

function randomDate($start_date, $end_date) {
    $min = strtotime($start_date);
    $max = strtotime($end_date);
    return date('Y-m-d H:i:s', rand($min, $max));
}

// 2. SERVICE REPORTS (RELATÓRIOS DE CULTO)
echo "Creating service reports...\n";

// Get all congregations
$stmt = $pdo->query("SELECT id FROM congregations");
$congregations = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get some users to be "created_by"
$stmt = $pdo->query("SELECT id FROM users LIMIT 1");
$user = $stmt->fetch();
$user_id = $user ? $user['id'] : 1;

$leaders = ['Pr. José', 'Ev. Marcos', 'Pb. Lucas', 'Pr. Paulo'];
$preachers = ['Pr. Antônio', 'Mis. Carlos', 'Pr. João', 'Pr. Rafael'];
$times = ['19:00', '19:30', '20:00', '09:00'];

foreach ($congregations as $cid) {
    // create 5 reports for each congregation
    for ($i = 0; $i < 5; $i++) {
        $men = rand(10, 40);
        $women = rand(15, 50);
        $youth = rand(10, 30);
        $children = rand(5, 20);
        $visitors = rand(0, 10);
        $total = $men + $women + $youth + $children + $visitors;
        
        $stmt = $pdo->prepare("
            INSERT INTO service_reports 
            (congregation_id, date, time, leader_name, preacher_name, attendance_men, attendance_women, attendance_youth, attendance_children, attendance_visitors, total_attendance, notes, created_by, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $cid,
            randomDate('2023-01-01', date('Y-m-d H:i:s')),
            $times[array_rand($times)],
            $leaders[array_rand($leaders)],
            $preachers[array_rand($preachers)],
            $men, $women, $youth, $children, $visitors, $total,
            "Culto abençoado, com grande mover do Espírito Santo.",
            $user_id
        ]);
        
        $report_id = $pdo->lastInsertId();
        
        // Add service people actions (decisions, reconciliations, baptisms)
        $actions = ['Conversão', 'Reconciliação', 'Apresentação de Criança', 'Batismo no Espírito Santo'];
        
        // random 0 to 3 actions per service
        $num_actions = rand(0, 3);
        for ($j = 0; $j < $num_actions; $j++) {
            $stmt = $pdo->prepare("INSERT INTO service_people_actions (service_report_id, name, action_type, observation) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $report_id,
                "Irmão " . uniqid(), // dummy name
                $actions[array_rand($actions)],
                "Ocorreu durante o apelo"
            ]);
        }
    }
}

echo "Seeding completed successfully!\n";
