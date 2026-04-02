<?php
// scripts/full_backup.php

// Define paths
$dbFile = __DIR__ . '/../database/SistemaChurch.db';
$backupFile = __DIR__ . '/../public/backup_full.sql';

if (!file_exists($dbFile)) {
    die("Database file not found at: $dbFile\n");
}

try {
    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

$handle = fopen($backupFile, 'w');
if (!$handle) {
    die("Cannot open file for writing: $backupFile\n");
}

fwrite($handle, "-- Database Backup: SistemaChurch.db\n");
fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n\n");
fwrite($handle, "PRAGMA foreign_keys=OFF;\n");
fwrite($handle, "BEGIN TRANSACTION;\n\n");

// Get all tables
$tables = [];
$stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $tables[] = $row['name'];
}

foreach ($tables as $table) {
    // Get CREATE statement
    $stmt = $pdo->prepare("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?");
    $stmt->execute([$table]);
    $createSql = $stmt->fetchColumn();
    
    fwrite($handle, "-- Table: $table\n");
    fwrite($handle, "DROP TABLE IF EXISTS \"$table\";\n");
    fwrite($handle, "$createSql;\n\n");
    
    // Get Data
    $stmt = $pdo->query("SELECT * FROM \"$table\"");
    
    // Check if table has data
    $first = true;
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        if ($first) {
            fwrite($handle, "INSERT INTO \"$table\" VALUES");
            $first = false;
        } else {
            fwrite($handle, ",");
        }
        
        $rowValues = [];
        foreach ($row as $val) {
            if ($val === null) {
                $rowValues[] = "NULL";
            } else {
                $rowValues[] = $pdo->quote($val);
            }
        }
        fwrite($handle, "\n(" . implode(", ", $rowValues) . ")");
    }
    
    if (!$first) {
        fwrite($handle, ";\n\n");
    }
}

fwrite($handle, "COMMIT;\n");
fclose($handle);

echo "Backup created successfully at:\n$backupFile\n";
echo "Download URL: /backup_full.sql\n";
