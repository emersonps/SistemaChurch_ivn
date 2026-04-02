<?php
// scripts/debug_dump_hex_events.php

$dumpFile = __DIR__ . '/../database/dump_mysql.sql';
$content = file_get_contents($dumpFile);

$pos = strpos($content, 'events');
if ($pos !== false) {
    echo "Found 'events' at $pos\n";
    // Look ahead for COLLATE
    $chunk = substr($content, $pos, 2000);
    $colPos = strpos($chunk, 'COLLATE=');
    if ($colPos !== false) {
        $subChunk = substr($chunk, $colPos, 50);
        echo "String: " . $subChunk . "\n";
    }
}
