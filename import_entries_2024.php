<?php
require_once __DIR__ . '/config/database.php';

// Mapeamento de Meses
$months = [
    'jan' => '01', 'fev' => '02', 'mar' => '03', 'abr' => '04', 'mai' => '05', 'jun' => '06',
    'jul' => '07', 'ago' => '08', 'set' => '09', 'out' => '10', 'nov' => '11', 'dez' => '12'
];

// Mapeamento de Métodos
$methods = [
    'PIX' => 'PIX',
    'ESPÉCIE' => 'Dinheiro'
];

// Dados Fornecidos
$data = [
    ['03/fev', 'Banca (kikão)', 'PIX', 'R$ 80,00'],
    ['01/mar', 'Rifa dos 5 prêmios', 'PIX', 'R$ 1.052,79'],
    ['05/mar', 'Rifa do Bolo e Pudim', 'PIX', 'R$ 242,11'],
    ['09/mar', 'Oferta especial (Pr. Hélio)', 'ESPÉCIE', 'R$ 80,00'],
    ['10/mar', 'Banca (kikão e pudim)', 'PIX', 'R$ 290,00'],
    ['17/mar', 'Banca (pizza) - 28 pizzas', 'PIX', 'R$ 228,00'],
    ['26/mai', 'Banca Kikão', 'PIX', 'R$ 56,00'],
    ['26/mai', 'Banca Kikão', 'PIX', 'R$ 64,00'],
    ['09/jun', 'Oferta especial (Pr. Hélio)', 'ESPÉCIE', 'R$ 50,00'],
    ['20/jun', 'Banca Kikão', 'ESPÉCIE', 'R$ 73,00'],
    ['23/jun', 'Banca Kikão', 'ESPÉCIE', 'R$ 123,84'],
    ['20/jul', 'Rifas (82)', 'PIX', 'R$ 220,00'],
    ['20/jul', 'Pudim', 'PIX', 'R$ 75,00'],
    ['21/jul', 'Banca Pizzas', 'PIX', 'R$ 360,00'],
    ['29/set', 'Banca Igreja', 'PIX', 'R$ 135,00'],
    ['14/nov', 'Banca Igreja', 'ESPÉCIE', 'R$ 141,00'],
    ['17/nov', 'Banca Igreja', 'ESPÉCIE', 'R$ 170,00'],
    ['28/nov', 'Banca Igreja', 'PIX', 'R$ 95,00'],
    ['08/dez', 'Banca Igreja', 'PIX', 'R$ 144,00'],
    ['09/dez', 'Banca Igreja', 'PIX', 'R$ 36,00'],
    ['15/dez', 'Banca Igreja', 'PIX', 'R$ 78,00'],
    ['16/dez', 'Banca Igreja (venda na rua)', 'PIX', 'R$ 12,00'],
    ['16/dez', 'Banca Igreja (venda na rua)', 'ESPÉCIE', 'R$ 74,00']
];

try {
    $db = (new Database())->connect();
    // Inserindo com member_id = NULL e type = 'Oferta' e congregation_id = 6 (Sede)
    $stmt = $db->prepare("INSERT INTO tithes (member_id, amount, payment_date, payment_method, type, notes, congregation_id, giver_name) VALUES (NULL, ?, ?, ?, 'Oferta', ?, 6, ?)");

    $count = 0;
    foreach ($data as $row) {
        // Parse Data
        list($day, $monthName) = explode('/', trim($row[0]));
        $month = $months[strtolower(trim($monthName))];
        $date = "2024-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
        
        // Parse Valor
        $val = trim($row[3]);
        $val = str_replace(['R$', ' ', '.'], '', $val);
        $val = str_replace(',', '.', $val);
        $amount = (float)$val;
        
        // Parse Método
        $met = trim($row[2]);
        $method = ($met === 'ESPÉCIE') ? 'Dinheiro' : $met;
        
        // Descrição e Nome do Doador
        $desc = trim($row[1]);
        
        // Executar
        $stmt->execute([$amount, $date, $method, $desc, $desc]);
        $count++;
        echo "Inserido: $date - $desc - R$ $amount ($method)\n";
    }
    
    echo "\nTotal de $count registros inseridos com sucesso!\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
