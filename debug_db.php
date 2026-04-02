<?php
echo "Start\n";
require_once __DIR__ . '/config/database.php';
echo "Loaded config\n";
try {
    $db = (new Database())->connect();
    echo "Connected\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "End\n";
