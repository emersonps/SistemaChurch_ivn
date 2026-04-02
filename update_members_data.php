<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();

echo "Starting Member Data Update...\n";

// Helper functions for fake data
function generateCPF() {
    $n = [];
    for($i=0; $i<9; $i++) $n[] = rand(0,9);
    $d1 = 0;
    for($i=0; $i<9; $i++) $d1 += $n[$i] * (10-$i);
    $d1 = 11 - ($d1 % 11);
    if($d1 >= 10) $d1 = 0;
    $n[] = $d1;
    $d2 = 0;
    for($i=0; $i<10; $i++) $d2 += $n[$i] * (11-$i);
    $d2 = 11 - ($d2 % 11);
    if($d2 >= 10) $d2 = 0;
    $n[] = $d2;
    return implode('', array_slice($n, 0, 3)) . '.' . implode('', array_slice($n, 3, 3)) . '.' . implode('', array_slice($n, 6, 3)) . '-' . implode('', array_slice($n, 9, 2));
}

function generateRG() {
    return rand(10, 99) . '.' . rand(100, 999) . '.' . rand(100, 999) . '-' . rand(0, 9);
}

function generateCEP() {
    return rand(10000, 99999) . '-' . rand(100, 999);
}

$marital_statuses = ['Solteiro(a)', 'Casado(a)', 'Divorciado(a)', 'Viúvo(a)'];
$streets = ['Rua das Flores', 'Av. Brasil', 'Rua São Paulo', 'Rua XV de Novembro', 'Av. Paulista', 'Rua Amazonas', 'Av. das Nações'];
$neighborhoods = ['Centro', 'Jardim América', 'Vila Nova', 'Boa Vista', 'São João', 'Bela Vista', 'Santo Antônio'];
$professions = ['Professor', 'Engenheiro', 'Comerciante', 'Estudante', 'Médico', 'Advogado', 'Administrador', 'Autônomo', 'Motorista'];
$cities = ['São Paulo', 'Rio de Janeiro', 'Belo Horizonte', 'Curitiba', 'Porto Alegre', 'Brasília', 'Salvador'];

$names_m = ['João', 'José', 'Pedro', 'Paulo', 'Marcos', 'Lucas', 'Mateus', 'Tiago', 'Felipe', 'André'];
$names_f = ['Maria', 'Ana', 'Fernanda', 'Juliana', 'Camila', 'Letícia', 'Amanda', 'Bruna', 'Carolina', 'Daniela'];
$surnames = ['Silva', 'Santos', 'Pereira', 'Costa', 'Alves', 'Rodrigues', 'Lima', 'Gomes', 'Martins', 'Ribeiro'];

function generateParentName($gender) {
    global $names_m, $names_f, $surnames;
    $first = $gender == 'M' ? $names_m[array_rand($names_m)] : $names_f[array_rand($names_f)];
    $last = $surnames[array_rand($surnames)] . ' ' . $surnames[array_rand($surnames)];
    return $first . ' ' . $last;
}

// Fetch all members
$stmt = $pdo->query("SELECT id FROM members");
$members = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "Updating " . count($members) . " members...\n";

$update_stmt = $pdo->prepare("
    UPDATE members SET 
        cpf = ?, 
        rg = ?, 
        marital_status = ?, 
        address = ?, 
        address_number = ?, 
        neighborhood = ?, 
        complement = ?, 
        reference_point = ?, 
        zip_code = ?, 
        state = ?, 
        city = ?, 
        nationality = 'Brasileiro(a)', 
        birthplace = ?, 
        father_name = ?, 
        mother_name = ?, 
        children_count = ?, 
        profession = ?, 
        admission_method = ?, 
        admission_date = ?, 
        is_tither = ?,
        is_baptized = ?,
        baptism_date = ?,
        status = 'active'
    WHERE id = ?
");

foreach ($members as $id) {
    $is_baptized = rand(0, 1);
    $update_stmt->execute([
        generateCPF(),
        generateRG(),
        $marital_statuses[array_rand($marital_statuses)],
        $streets[array_rand($streets)],
        rand(1, 9999),
        $neighborhoods[array_rand($neighborhoods)],
        rand(0, 1) ? 'Apto ' . rand(1, 100) : '',
        'Próximo ao supermercado',
        generateCEP(),
        'SP', // Fixed state for simplicity
        $cities[array_rand($cities)],
        $cities[array_rand($cities)],
        generateParentName('M'),
        generateParentName('F'),
        rand(0, 4), // children_count
        $professions[array_rand($professions)],
        rand(0, 1) ? 'Batismo' : 'Aclamação',
        date('Y-m-d H:i:s', rand(strtotime('2010-01-01'), time())),
        rand(0, 1), // is_tither
        $is_baptized,
        $is_baptized ? date('Y-m-d H:i:s', rand(strtotime('2010-01-01'), time())) : null,
        $id
    ]);
}

echo "All members updated successfully with mandatory/additional data!\n";
