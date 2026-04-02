<?php
// scripts/fix_dump.php

$dumpFile = __DIR__ . '/../database/dump_mysql.sql';

if (!file_exists($dumpFile)) {
    die("Dump file not found.\n");
}

$content = file_get_contents($dumpFile);

// 1. Remove CREATE DATABASE and USE statements
$content = preg_replace('/CREATE DATABASE IF NOT EXISTS.*;/i', '', $content);
$content = preg_replace('/USE `.*;/i', '', $content);

// 2. Fix Collation (utf8mb4_0900_ai_ci -> utf8mb4_general_ci)
// Use regex to be more flexible
$content = preg_replace('/utf8mb4_0900_ai_ci/', 'utf8mb4_general_ci', $content, -1, $count);
echo "Replaced $count occurrences of collation.\n";

// 3. Ensure Foreign Key Checks are disabled at the start
if (strpos($content, 'SET FOREIGN_KEY_CHECKS = 0') === false) {
    // Add it after the header comments block
    // Look for the last comment block
    if (preg_match('/^-- .*$/m', $content)) {
         $content = "SET FOREIGN_KEY_CHECKS = 0;\n" . $content;
    } else {
         $content = "SET FOREIGN_KEY_CHECKS = 0;\n" . $content;
    }
}

// 4. Add SET FOREIGN_KEY_CHECKS = 1 at the end
if (strpos($content, 'SET FOREIGN_KEY_CHECKS = 1') === false) {
    $content .= "\nSET FOREIGN_KEY_CHECKS = 1;";
}

file_put_contents($dumpFile, $content);

echo "Dump file fixed! \n";
echo "- Removed CREATE DATABASE/USE statements.\n";
echo "- Changed collation to utf8mb4_general_ci.\n";
echo "- Added explicit SET FOREIGN_KEY_CHECKS.\n";
