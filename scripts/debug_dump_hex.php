<?php
// scripts/debug_dump_hex.php

$dumpFile = __DIR__ . '/../database/dump_mysql.sql';
$content = file_get_contents($dumpFile);

$pos = strpos($content, 'congregations');
if ($pos !== false) {
    echo "Found 'congregations' at $pos\n";
    // Look ahead for COLLATE
    $chunk = substr($content, $pos, 1000);
    $colPos = strpos($chunk, 'COLLATE');
    if ($colPos !== false) {
        echo "Found 'COLLATE' at offset $colPos in chunk\n";
        $subChunk = substr($chunk, $colPos, 50);
        echo "String: " . $subChunk . "\n";
        echo "Hex: " . bin2hex($subChunk) . "\n";
    } else {
        echo "COLLATE not found in chunk.\n";
    }
} else {
    echo "'congregations' not found.\n";
}
