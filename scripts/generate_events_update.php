<?php
// scripts/generate_events_update.php

$outputFile = __DIR__ . '/../public/update_events_banner.sql';

$handle = fopen($outputFile, 'w');
if (!$handle) die("Cannot open output file.\n");

fwrite($handle, "-- Update Script for MySQL/phpMyAdmin - Events Banner\n");
fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n\n");

// ALTER TABLE events (Add banner_path if missing)
fwrite($handle, "DELIMITER //\n");
fwrite($handle, "CREATE PROCEDURE AddBannerColumn()\n");
fwrite($handle, "BEGIN\n");
fwrite($handle, "    IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'events' AND COLUMN_NAME = 'banner_path') THEN\n");
fwrite($handle, "        ALTER TABLE `events` ADD COLUMN `banner_path` TEXT DEFAULT NULL AFTER `end_time`;\n");
fwrite($handle, "    END IF;\n");
fwrite($handle, "END//\n");
fwrite($handle, "DELIMITER ;\n");
fwrite($handle, "CALL AddBannerColumn();\n");
fwrite($handle, "DROP PROCEDURE AddBannerColumn;\n\n");

fclose($handle);

echo "Update SQL created at: $outputFile\n";
