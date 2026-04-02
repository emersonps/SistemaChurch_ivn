<?php
// teste_drivers.php
echo "<h1>Diagnóstico de Banco de Dados</h1>";

echo "<h2>Drivers PDO Disponíveis:</h2>";
$drivers = PDO::getAvailableDrivers();
if (empty($drivers)) {
    echo "<p style='color:red'>Nenhum driver PDO encontrado!</p>";
} else {
    echo "<ul>";
    foreach ($drivers as $driver) {
        echo "<li>$driver</li>";
    }
    echo "</ul>";
}

echo "<h2>Teste de Conexão SQLite:</h2>";
try {
    $db_path = __DIR__ . '/database/SistemaChurch.db';
    echo "Caminho do banco: $db_path<br>";
    
    if (!file_exists($db_path)) {
        echo "<p style='color:red'>Arquivo do banco não encontrado!</p>";
    } else {
        echo "Arquivo do banco existe.<br>";
    }

    $pdo = new PDO("sqlite:" . $db_path);
    echo "<p style='color:green'>Conexão SQLite realizada com sucesso!</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>Erro SQLite: " . $e->getMessage() . "</p>";
}
