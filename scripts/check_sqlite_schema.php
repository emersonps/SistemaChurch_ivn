<?php
try {
    $dbPath = __DIR__ . '/../database/SistemaChurch.db';
    $pdo = new PDO("sqlite:$dbPath");
    $stmt = $pdo->query("SELECT sql FROM sqlite_master WHERE name='events'");
    echo $stmt->fetchColumn() . "\n";
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
