<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
$pdo = $db->connect();

echo "Atualizando os métodos de pagamento das entradas...\n";

// Métodos de pagamento possíveis (com base no que o sistema aceita)
$methods = ['Dinheiro', 'PIX', 'Cartão de Crédito', 'Cartão de Débito', 'Transferência'];

// Buscar todas as entradas (dízimos e ofertas)
$stmt = $pdo->query("SELECT id FROM tithes WHERE payment_method IS NULL OR payment_method = ''");
$tithes = $stmt->fetchAll(PDO::FETCH_COLUMN);

$count = 0;
foreach ($tithes as $id) {
    $random_method = $methods[array_rand($methods)];
    $updateStmt = $pdo->prepare("UPDATE tithes SET payment_method = ? WHERE id = ?");
    $updateStmt->execute([$random_method, $id]);
    $count++;
}

echo "Foram atualizadas $count entradas com métodos de pagamento variados.\n";
