<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    $stmt = $db->query("DESCRIBE events");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
