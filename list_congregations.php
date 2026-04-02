<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    $stmt = $db->query("SELECT id, name FROM congregations ORDER BY id ASC");
    $congregations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($congregations as $c) {
        echo "ID: " . $c['id'] . " - Nome: " . $c['name'] . "\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
