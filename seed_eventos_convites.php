<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();

echo "Cadastrando novos Eventos e Convites Especiais...\n";

// Pega id da Sede
$stmt = $pdo->query("SELECT id, name FROM congregations ORDER BY id ASC LIMIT 1");
$sede = $stmt->fetch(PDO::FETCH_ASSOC);
$sede_id = $sede ? $sede['id'] : 1;
$sede_name = $sede ? $sede['name'] : 'Sede';

$novos_eventos = [
    // === EVENTOS ===
    [
        'title' => 'Chá de Mulheres',
        'desc' => 'Um momento de comunhão e palavra para as mulheres da igreja.',
        'date' => date('Y-m-d 16:00:00', strtotime('+2 Saturdays')),
        'end_time' => '19:00:00',
        'location' => $sede_name,
        'address' => '',
        'type' => 'evento',
        'cong_id' => $sede_id
    ],
    [
        'title' => 'Acampadentro dos Adolescentes',
        'desc' => 'Uma noite inteira de louvor, gincanas e diversão na igreja.',
        'date' => date('Y-m-d 20:00:00', strtotime('+3 Fridays')),
        'end_time' => '08:00:00',
        'location' => $sede_name,
        'address' => '',
        'type' => 'evento',
        'cong_id' => $sede_id
    ],
    [
        'title' => 'Batismo nas Águas 2024',
        'desc' => 'Grande celebração de batismo para os novos convertidos.',
        'date' => date('Y-m-d 09:00:00', strtotime('+4 Sundays')),
        'end_time' => '12:00:00',
        'location' => 'Clube de Campo das Águas',
        'address' => 'Estrada do Sol, Km 12',
        'type' => 'evento',
        'cong_id' => null
    ],
    [
        'title' => 'Escola Bíblica de Férias (EBF)',
        'desc' => 'Semana especial para as crianças durante as férias escolares.',
        'date' => date('Y-m-d 14:00:00', strtotime('+1 month')),
        'end_time' => '17:00:00',
        'location' => $sede_name,
        'address' => '',
        'type' => 'evento',
        'cong_id' => $sede_id
    ],
    
    // === CONVITES ESPECIAIS ===
    [
        'title' => 'Culto de Ações de Graças - AD Central',
        'desc' => 'Fomos convidados para o culto de aniversário da igreja co-irmã.',
        'date' => date('Y-m-d 19:30:00', strtotime('+10 days')),
        'end_time' => '22:00:00',
        'location' => 'Assembleia de Deus Central',
        'address' => 'Rua Principal, 500 - Centro',
        'type' => 'convite',
        'cong_id' => null
    ],
    [
        'title' => 'Congresso de Missões Regional',
        'desc' => 'Participação do nosso coral no congresso regional de missões.',
        'date' => date('Y-m-d 18:00:00', strtotime('+20 days')),
        'end_time' => '22:30:00',
        'location' => 'Ginásio Municipal',
        'address' => 'Praça dos Esportes, s/n',
        'type' => 'convite',
        'cong_id' => null
    ],
    [
        'title' => 'Casamento: João e Maria',
        'desc' => 'Cerimônia de casamento dos nossos irmãos.',
        'date' => date('Y-m-d 17:00:00', strtotime('+2 months')),
        'end_time' => '20:00:00',
        'location' => $sede_name,
        'address' => '',
        'type' => 'convite',
        'cong_id' => $sede_id
    ]
];

$event_ids = [];

foreach ($novos_eventos as $ev) {
    $stmt = $pdo->prepare("
        INSERT INTO events 
        (title, description, event_date, end_time, location, address, type, status, has_attendance_list, congregation_id, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'active', 0, ?, NOW())
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
    
    $event_ids[] = $pdo->lastInsertId();
}

// Associar alguns banners aletórios
$banners_dir = __DIR__ . '/public/assets/uploads/banners/';
$dummy_image_path = $banners_dir . 'dummy_banner.jpg';

if (file_exists($dummy_image_path)) {
    foreach ($event_ids as $id) {
        if(rand(0, 100) > 30) { // 70% chance de ter banner
            $filename = 'event_' . uniqid() . '.jpg';
            copy($dummy_image_path, $banners_dir . $filename);
            $pdo->query("UPDATE events SET banner_path = '$filename' WHERE id = $id");
        }
    }
}

echo "Foram cadastrados " . count($novos_eventos) . " novos itens com sucesso!\n";
