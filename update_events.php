<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();

echo "Updating events and services...\n";

// Clear old fake events to avoid mess
$pdo->query("DELETE FROM event_attendance");
$pdo->query("DELETE FROM events");

// Get all congregations
$stmt = $pdo->query("SELECT id, name FROM congregations");
$congregations = $stmt->fetchAll(PDO::FETCH_ASSOC);

function randomDate($start_date, $end_date) {
    $min = strtotime($start_date);
    $max = strtotime($end_date);
    return date('Y-m-d H:i:s', rand($min, $max));
}

// 1. CULTOS ROTINEIROS (Recurring services per congregation)
echo "Creating routine services...\n";
foreach ($congregations as $cong) {
    $services = [
        ['title' => 'Culto de Ensino', 'days' => '["3"]', 'desc' => 'Culto de ensino da palavra de Deus.'], // Wednesday
        ['title' => 'Culto da Família', 'days' => '["0"]', 'desc' => 'Culto de domingo dedicado à família.'], // Sunday
        ['title' => 'Círculo de Oração', 'days' => '["5"]', 'desc' => 'Reunião de oração e intercessão.'] // Friday
    ];
    
    foreach ($services as $svc) {
        $stmt = $pdo->prepare("
            INSERT INTO events 
            (title, description, event_date, end_time, location, type, status, recurring_days, has_attendance_list, congregation_id, created_at) 
            VALUES (?, ?, ?, ?, ?, 'Culto', 'Agendado', ?, 0, ?, NOW())
        ");
        
        $base_date = date('Y-m-d', strtotime('next Sunday'));
        $stmt->execute([
            $svc['title'] . ' - ' . $cong['name'],
            $svc['desc'],
            $base_date . ' 19:30:00', // start time
            '21:30:00',               // end time
            $cong['name'],            // location
            $svc['days'],
            $cong['id']
        ]);
    }
}

// 2. EVENTOS ESPECIAIS E EXTERNOS
echo "Creating special and external events...\n";

$special_events = [
    [
        'title' => 'Conferência Jovem 2024',
        'desc' => 'Grande conferência para os jovens da região.',
        'date' => date('Y-m-d 19:00:00', strtotime('+15 days')),
        'end_time' => '22:00:00',
        'location' => 'Ginásio Poliesportivo Municipal',
        'address' => 'Rua do Esporte, 1000 - Centro',
        'type' => 'Conferência',
        'cong_id' => null // External event
    ],
    [
        'title' => 'Retiro de Casais',
        'desc' => 'Fim de semana abençoado para os casais da igreja.',
        'date' => date('Y-m-d 08:00:00', strtotime('+1 month')),
        'end_time' => '17:00:00',
        'location' => 'Chácara Recanto Feliz',
        'address' => 'Rodovia BR-101, Km 45',
        'type' => 'Retiro',
        'cong_id' => null // External event
    ],
    [
        'title' => 'Seminário de Liderança',
        'desc' => 'Treinamento para todos os líderes e obreiros.',
        'date' => date('Y-m-d 14:00:00', strtotime('+10 days')),
        'end_time' => '18:00:00',
        'location' => 'Sede', // This happens at the main church
        'address' => '',
        'type' => 'Seminário',
        'cong_id' => 1 // Assuming 1 is Sede
    ],
    [
        'title' => 'Vigília Geral',
        'desc' => 'Vigília com todas as congregações.',
        'date' => date('Y-m-d 23:00:00', strtotime('next Saturday')),
        'end_time' => '05:00:00',
        'location' => 'Sede',
        'address' => '',
        'type' => 'Vigília',
        'cong_id' => 1
    ]
];

foreach ($special_events as $ev) {
    $stmt = $pdo->prepare("
        INSERT INTO events 
        (title, description, event_date, end_time, location, address, type, status, has_attendance_list, congregation_id, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Agendado', 1, ?, NOW())
    ");
    
    $stmt->execute([
        $ev['title'],
        $ev['desc'],
        $ev['date'],
        $ev['end_time'],
        $ev['location'],
        $ev['address'],
        $ev['type'],
        $ev['cong_id']
    ]);
}

// Add some dummy banners to the new events
$stmt = $pdo->query("SELECT id FROM events");
$new_events = $stmt->fetchAll(PDO::FETCH_COLUMN);
$banners_dir = __DIR__ . '/public/assets/uploads/banners/';
$dummy_image_path = $banners_dir . 'dummy_banner.jpg';

if (file_exists($dummy_image_path)) {
    foreach ($new_events as $id) {
        if(rand(0,1)) { // 50% chance to have a banner
            $filename = 'event_' . uniqid() . '.jpg';
            copy($dummy_image_path, $banners_dir . $filename);
            $pdo->query("UPDATE events SET banner_path = '$filename' WHERE id = $id");
        }
    }
}

echo "Events updated successfully!\n";
