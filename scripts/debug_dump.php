<?php
// scripts/debug_dump.php

$dumpFile = __DIR__ . '/../database/dump_mysql.sql';

if (!file_exists($dumpFile)) {
    die("Dump file not found.\n");
}

$content = file_get_contents($dumpFile);

echo "File length: " . strlen($content) . "\n";
echo "First 100 bytes (Hex):\n";
echo bin2hex(substr($content, 0, 100)) . "\n";

// Check if utf8mb4_0900_ai_ci exists
if (strpos($content, 'utf8mb4_0900_ai_ci') !== false) {
    echo "String 'utf8mb4_0900_ai_ci' FOUND.\n";
} else {
    echo "String 'utf8mb4_0900_ai_ci' NOT FOUND.\n";
}

// Try regex find
if (preg_match('/utf8mb4_0900_ai_ci/', $content)) {
    echo "Regex match FOUND.\n";
} else {
    echo "Regex match NOT FOUND.\n";
}
