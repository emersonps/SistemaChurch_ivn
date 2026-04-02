<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();

echo "Starting Database Seeding...\n";

// Helper functions
function randomDate($start_date, $end_date) {
    $min = strtotime($start_date);
    $max = strtotime($end_date);
    $val = rand($min, $max);
    return date('Y-m-d H:i:s', $val);
}

// 1. Get a valid congregation_id
$stmt = $pdo->query("SELECT id FROM congregations LIMIT 1");
$congregation = $stmt->fetch();
$cong_id = $congregation ? $congregation['id'] : 1;
if (!$congregation) {
    $pdo->exec("INSERT INTO congregations (name) VALUES ('Sede')");
    $cong_id = $pdo->lastInsertId();
}
echo "Using Congregation ID: $cong_id\n";

// 2. Seed Members
$names = ['João Silva', 'Maria Santos', 'José Pereira', 'Ana Costa', 'Pedro Alves', 'Paulo Rodrigues', 'Marcos Lima', 'Lucas Gomes', 'Mateus Martins', 'Fernanda Ribeiro'];
$member_ids = [];
echo "Seeding members...\n";
for ($i = 0; $i < 30; $i++) {
    $name = $names[array_rand($names)] . " " . rand(1, 1000);
    $stmt = $pdo->prepare("INSERT INTO members (name, email, phone, birth_date, congregation_id, status, role, unique_id) VALUES (?, ?, ?, ?, ?, 'Ativo', 'Membro', ?)");
    $unique_id = substr(md5(uniqid()), 0, 7);
    $stmt->execute([
        $name, 
        strtolower(str_replace(' ', '.', $name)) . '@example.com', 
        '(11) 9' . rand(1000, 9999) . '-' . rand(1000, 9999),
        randomDate('1970-01-01', '2005-12-31'),
        $cong_id,
        $unique_id
    ]);
    $member_ids[] = $pdo->lastInsertId();
}
echo "Created 30 members.\n";

// 3. Seed Tithes & Offerings
echo "Seeding tithes and offerings...\n";
foreach ($member_ids as $m_id) {
    // 3 tithes per member
    for ($j = 0; $j < 3; $j++) {
        $stmt = $pdo->prepare("INSERT INTO tithes (member_id, amount, payment_date, type, congregation_id) VALUES (?, ?, ?, 'dizimo', ?)");
        $stmt->execute([$m_id, rand(50, 500), randomDate('2023-01-01', date('Y-m-d')), $cong_id]);
    }
    // some offerings
    if (rand(0, 1)) {
        $stmt = $pdo->prepare("INSERT INTO tithes (member_id, amount, payment_date, type, congregation_id) VALUES (?, ?, ?, 'oferta', ?)");
        $stmt->execute([$m_id, rand(10, 100), randomDate('2023-01-01', date('Y-m-d')), $cong_id]);
    }
}
echo "Created tithes.\n";

// 4. Seed EBD
echo "Seeding EBD...\n";
$classes = ['Jovens', 'Adultos', 'Crianças', 'Adolescentes'];
$class_ids = [];
foreach ($classes as $c) {
    $stmt = $pdo->prepare("INSERT INTO ebd_classes (name, status, congregation_id) VALUES (?, 'active', ?)");
    $stmt->execute([$c, $cong_id]);
    $class_ids[] = $pdo->lastInsertId();
}

$student_ids = [];
foreach ($member_ids as $m_id) {
    $c_id = $class_ids[array_rand($class_ids)];
    $stmt = $pdo->prepare("INSERT INTO ebd_students (class_id, member_id, status, enrolled_at) VALUES (?, ?, 'active', NOW())");
    $stmt->execute([$c_id, $m_id]);
    $student_ids[$c_id][] = $pdo->lastInsertId();
}

foreach ($class_ids as $c_id) {
    // create 2 lessons
    for ($l = 0; $l < 2; $l++) {
        $stmt = $pdo->prepare("INSERT INTO ebd_lessons (class_id, lesson_date, topic) VALUES (?, ?, ?)");
        $stmt->execute([$c_id, randomDate('2023-01-01', date('Y-m-d')), "Lição " . rand(1, 10)]);
        $l_id = $pdo->lastInsertId();
        
        // attendance
        if (isset($student_ids[$c_id])) {
            foreach ($student_ids[$c_id] as $s_id) {
                if (rand(0, 10) > 2) { // 80% presence
                    $stmt = $pdo->prepare("INSERT INTO ebd_attendance (lesson_id, student_id, present, brought_bible, brought_magazine) VALUES (?, ?, 1, ?, ?)");
                    $stmt->execute([$l_id, $s_id, rand(0,1), rand(0,1)]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO ebd_attendance (lesson_id, student_id, present) VALUES (?, ?, 0)");
                    $stmt->execute([$l_id, $s_id]);
                }
            }
        }
    }
}
echo "Created EBD data.\n";

// 5. Seed Events & Attendance
echo "Seeding events...\n";
$event_titles = ['Culto de Domingo', 'Culto de Jovens', 'Campanha de Oração', 'Vigília'];
$event_ids = [];
for ($i = 0; $i < 5; $i++) {
    $stmt = $pdo->prepare("INSERT INTO events (title, event_date, type, congregation_id, has_attendance_list) VALUES (?, ?, 'Culto', ?, 1)");
    $stmt->execute([$event_titles[array_rand($event_titles)], randomDate('2023-01-01', date('Y-m-d')), $cong_id]);
    $event_ids[] = $pdo->lastInsertId();
}

foreach ($event_ids as $e_id) {
    // 10 random members attended
    $attended = (array)array_rand($member_ids, min(10, count($member_ids)));
    foreach ($attended as $idx) {
        $m_id = $member_ids[$idx];
        $stmt = $pdo->prepare("INSERT INTO event_attendance (event_id, member_id) VALUES (?, ?)");
        $stmt->execute([$e_id, $m_id]);
    }
}
echo "Created events data.\n";

// 6. Seed Studies
echo "Seeding studies...\n";
for ($i = 0; $i < 5; $i++) {
    $stmt = $pdo->prepare("INSERT INTO studies (title, description, file_path, congregation_id) VALUES (?, ?, 'mock_study.pdf', ?)");
    $stmt->execute(["Estudo Bíblico " . ($i+1), "Descrição do estudo " . ($i+1), $cong_id]);
}
echo "Created studies data.\n";

echo "Seeding finished successfully!\n";
