<?php
$line = '07/01 	 DÍZIMO 	 EMERSON PINHEIRO DE SOUZA 	 PIX 	 330,00';
$line = trim($line);

if (preg_match('/^(\d{2}\/[a-zA-Z0-9]{2,3})\s+(OFERTA|DÍZIMO|DIZIMO|EBD)?\s*(.*?)\s*(ESPÉCIE|PIX|DINHEIRO|CARTÃO|TRANSFERÊNCIA)\s+((?:R\$)?\s*[\d\.,]+)$/ui', $line, $matches)) {
    echo "REGEX MATCH:\n";
    print_r($matches);
    $parts = [
        $matches[1], // Data
        $matches[2], // Tipo
        $matches[3], // Descrição
        $matches[4], // Método
        $matches[5]  // Valor
    ];
} else {
    echo "REGEX FAIL, USING FALLBACK:\n";
    $parts = preg_split('/\t+/', $line);
    if (count($parts) < 4) {
        $parts = preg_split('/\s{2,}/', $line);
    }
    print_r($parts);
}

$type = 'Oferta';
$desc = '';
$met = '';
$valStr = '';

if (count($parts) >= 5) {
    echo "\nTEM 5 COLUNAS:\n";
    $typePart = strtolower(trim($parts[1]));
    if (strpos($typePart, 'dízimo') !== false || strpos($typePart, 'dizimo') !== false) {
        $type = 'Dízimo';
    } else if (strpos($typePart, 'ebd') !== false) {
        $type = 'Oferta'; // EBD entra como Oferta
        $desc = trim($parts[2]);
        if (empty($desc)) {
            $desc = 'EBD';
        } else {
            $desc = 'EBD ' . $desc;
        }
    } else {
        $type = 'Oferta';
    }
    
    if (empty($desc) && strpos($typePart, 'ebd') === false) {
        $desc = trim($parts[2]);
    }
    
    $met = strtoupper(trim($parts[3]));
    $valStr = trim($parts[4]);
} else {
    echo "\nTEM 4 COLUNAS:\n";
    $desc = trim($parts[1]);
    $met = strtoupper(trim($parts[2]));
    $valStr = trim($parts[3]);
}

echo "RESULTADO FINAL:\n";
echo "Type: $type\n";
echo "Desc: $desc\n";
echo "Met: $met\n";
echo "Val: $valStr\n";
