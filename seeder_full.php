<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();

echo "Starting Full Database Seeding...\n";

function randomDate($start_date, $end_date) {
    $min = strtotime($start_date);
    $max = strtotime($end_date);
    return date('Y-m-d H:i:s', rand($min, $max));
}

$names_m = ['João', 'José', 'Pedro', 'Paulo', 'Marcos', 'Lucas', 'Mateus', 'Tiago', 'Felipe', 'André', 'Bruno', 'Carlos', 'Daniel', 'Eduardo', 'Fernando', 'Gabriel', 'Gustavo', 'Henrique', 'Igor', 'Julio', 'Leonardo', 'Marcelo', 'Rafael', 'Rodrigo', 'Thiago'];
$names_f = ['Maria', 'Ana', 'Fernanda', 'Juliana', 'Camila', 'Letícia', 'Amanda', 'Bruna', 'Carolina', 'Daniela', 'Eduarda', 'Flávia', 'Gabriela', 'Helena', 'Isabela', 'Jessica', 'Larissa', 'Mariana', 'Natália', 'Patrícia', 'Renata', 'Tatiane', 'Vanessa'];
$surnames = ['Silva', 'Santos', 'Pereira', 'Costa', 'Alves', 'Rodrigues', 'Lima', 'Gomes', 'Martins', 'Ribeiro', 'Almeida', 'Carvalho', 'Oliveira', 'Souza', 'Araújo', 'Melo', 'Barbosa', 'Cardoso', 'Dias', 'Castro'];

function generateName($gender) {
    global $names_m, $names_f, $surnames;
    $first = $gender == 'M' ? $names_m[array_rand($names_m)] : $names_f[array_rand($names_f)];
    $last = $surnames[array_rand($surnames)] . ' ' . $surnames[array_rand($surnames)];
    return $first . ' ' . $last;
}

// 1. CONGREGATIONS
echo "Creating congregations...\n";
$congregations_data = [
    ['name' => 'Congregação Betel', 'address' => 'Rua das Flores, 123', 'type' => 'Congregação'],
    ['name' => 'Congregação Peniel', 'address' => 'Av. Brasil, 456', 'type' => 'Congregação'],
    ['name' => 'Congregação Monte Sinai', 'address' => 'Rua do Sol, 789', 'type' => 'Congregação']
];
$cong_ids = [];
foreach ($congregations_data as $c) {
    $stmt = $pdo->prepare("INSERT INTO congregations (name, address, type, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$c['name'], $c['address'], $c['type']]);
    $cong_ids[] = $pdo->lastInsertId();
}

// Get existing congregation (Sede)
$stmt = $pdo->query("SELECT id FROM congregations");
$all_congs = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 2. MEMBERS
echo "Creating members...\n";
$members_by_cong = [];
foreach ($all_congs as $cid) {
    $members_by_cong[$cid] = [];
    $num_members = rand(15, 25);
    for ($i = 0; $i < $num_members; $i++) {
        $gender = rand(0, 1) ? 'M' : 'F';
        $name = generateName($gender);
        $email = strtolower(str_replace(' ', '.', $name)) . rand(10, 99) . '@example.com';
        $phone = '(11) 9' . rand(1000, 9999) . '-' . rand(1000, 9999);
        $unique_id = substr(md5(uniqid()), 0, 7);
        
        $stmt = $pdo->prepare("INSERT INTO members (name, email, phone, birth_date, congregation_id, status, role, unique_id, gender) VALUES (?, ?, ?, ?, ?, 'Ativo', 'Membro', ?, ?)");
        $stmt->execute([$name, $email, $phone, randomDate('1960-01-01', '2010-12-31'), $cid, $unique_id, $gender]);
        $members_by_cong[$cid][] = $pdo->lastInsertId();
    }
}

// 3. GROUPS / CÉLULAS AND LEADERS
echo "Creating groups and leaders...\n";
foreach ($all_congs as $cid) {
    $group_names = ["Célula Vida", "Célula Esperança", "Célula Fé"];
    foreach ($group_names as $gn) {
        $stmt = $pdo->prepare("INSERT INTO `groups` (name, description) VALUES (?, ?)");
        $stmt->execute([$gn . " - Cong. " . $cid, "Célula da congregação $cid"]);
        $g_id = $pdo->lastInsertId();
        
        // Pick members for this group
        if(count($members_by_cong[$cid]) > 5) {
            $m_keys = array_rand($members_by_cong[$cid], 6);
            
            // 1 Leader
            $leader_id = $members_by_cong[$cid][$m_keys[0]];
            $stmt = $pdo->prepare("INSERT INTO group_members (group_id, member_id, role, joined_at) VALUES (?, ?, 'leader', NOW())");
            $stmt->execute([$g_id, $leader_id]);
            
            // 1 Assistant
            $asst_id = $members_by_cong[$cid][$m_keys[1]];
            $stmt = $pdo->prepare("INSERT INTO group_members (group_id, member_id, role, joined_at) VALUES (?, ?, 'assistant', NOW())");
            $stmt->execute([$g_id, $asst_id]);
            
            // 1 Host
            $host_id = $members_by_cong[$cid][$m_keys[2]];
            $stmt = $pdo->prepare("INSERT INTO group_members (group_id, member_id, role, joined_at) VALUES (?, ?, 'host', NOW())");
            $stmt->execute([$g_id, $host_id]);
            
            // Members
            for($j=3; $j<6; $j++) {
                $mem_id = $members_by_cong[$cid][$m_keys[$j]];
                $stmt = $pdo->prepare("INSERT INTO group_members (group_id, member_id, role, joined_at) VALUES (?, ?, 'member', NOW())");
                $stmt->execute([$g_id, $mem_id]);
            }
        }
    }
}

// 4. EBD CLASSES, TEACHERS, STUDENTS
echo "Creating EBD classes...\n";
foreach ($all_congs as $cid) {
    $classes = [
        ['name' => 'Classe de Jovens', 'min' => 15, 'max' => 25],
        ['name' => 'Classe de Adultos', 'min' => 26, 'max' => 99]
    ];
    
    foreach ($classes as $c) {
        $stmt = $pdo->prepare("INSERT INTO ebd_classes (name, min_age, max_age, congregation_id, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
        $stmt->execute([$c['name'], $c['min'], $c['max'], $cid]);
        $class_id = $pdo->lastInsertId();
        
        if(count($members_by_cong[$cid]) > 5) {
            $m_keys = array_rand($members_by_cong[$cid], 5);
            
            // 1 Teacher
            $teacher_id = $members_by_cong[$cid][$m_keys[0]];
            $stmt = $pdo->prepare("INSERT INTO ebd_teachers (class_id, member_id, assigned_at, status) VALUES (?, ?, NOW(), 'active')");
            $stmt->execute([$class_id, $teacher_id]);
            // update member
            $pdo->query("UPDATE members SET is_ebd_teacher=1 WHERE id=$teacher_id");
            
            // Students
            for($j=1; $j<5; $j++) {
                $mem_id = $members_by_cong[$cid][$m_keys[$j]];
                $stmt = $pdo->prepare("INSERT INTO ebd_students (class_id, member_id, status, enrolled_at) VALUES (?, ?, 'active', NOW())");
                $stmt->execute([$class_id, $mem_id]);
            }
        }
    }
}

// 5. FINANCIAL: EXPENSES & TITHES
echo "Creating finances...\n";
$categories = ['Água', 'Luz', 'Aluguel', 'Manutenção', 'Missões'];
for ($i=0; $i<20; $i++) {
    // Expense
    $stmt = $pdo->prepare("INSERT INTO expenses (description, amount, expense_date, category, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute(["Pagamento de " . $categories[array_rand($categories)], rand(50, 1500), randomDate('2023-01-01', date('Y-m-d')), $categories[array_rand($categories)]]);
}

foreach ($all_congs as $cid) {
    foreach ($members_by_cong[$cid] as $mid) {
        // 2 dízimos, 2 ofertas per member
        for($j=0; $j<2; $j++) {
            $stmt = $pdo->prepare("INSERT INTO tithes (member_id, amount, payment_date, type, congregation_id) VALUES (?, ?, ?, 'dizimo', ?)");
            $stmt->execute([$mid, rand(100, 1000), randomDate('2023-01-01', date('Y-m-d')), $cid]);
            
            $stmt = $pdo->prepare("INSERT INTO tithes (member_id, amount, payment_date, type, congregation_id) VALUES (?, ?, ?, 'oferta', ?)");
            $stmt->execute([$mid, rand(20, 200), randomDate('2023-01-01', date('Y-m-d')), $cid]);
        }
    }
}

echo "Seeding completed successfully!\n";
