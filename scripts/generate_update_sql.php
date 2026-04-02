<?php
// scripts/generate_update_sql.php

$dbFile = __DIR__ . '/../database/SistemaChurch.db';
$outputFile = __DIR__ . '/../public/update_database.sql';

if (!file_exists($dbFile)) {
    die("Database file not found.\n");
}

try {
    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

$handle = fopen($outputFile, 'w');
if (!$handle) die("Cannot open output file.\n");

fwrite($handle, "-- Update Script for MySQL/phpMyAdmin\n");
fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n\n");
fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
fwrite($handle, "START TRANSACTION;\n");
fwrite($handle, "SET time_zone = \"+00:00\";\n\n");

// List of new tables created recently
$targetTables = ['expenses', 'financial_closures'];

// Also include tithes update if structure changed (giver_name column)
// We'll check if tithes has giver_name in SQLite and generate ALTER if needed, 
// but for simplicity, let's just dump the full structure of new tables
// and the ALTER statement for tithes.

// 1. ALTER TABLE tithes (Add giver_name if missing in MySQL)
fwrite($handle, "-- Update 'tithes' table structure if needed\n");
fwrite($handle, "DELIMITER //\n");
fwrite($handle, "CREATE PROCEDURE AddColumnIfNotExists()\n");
fwrite($handle, "BEGIN\n");
fwrite($handle, "    IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'tithes' AND COLUMN_NAME = 'giver_name') THEN\n");
fwrite($handle, "        ALTER TABLE `tithes` ADD COLUMN `giver_name` VARCHAR(255) DEFAULT NULL AFTER `congregation_id`;\n");
fwrite($handle, "    END IF;\n");
fwrite($handle, "END//\n");
fwrite($handle, "DELIMITER ;\n");
fwrite($handle, "CALL AddColumnIfNotExists();\n");
fwrite($handle, "DROP PROCEDURE AddColumnIfNotExists;\n\n");


// 2. Dump New Tables (Structure + Data)
foreach ($targetTables as $table) {
    echo "Processing table: $table...\n";
    
    // Structure
    fwrite($handle, "-- Table structure for `$table`\n");
    fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
    
    $cols = [];
    $pk = null;
    $stmtCols = $pdo->query("PRAGMA table_info(\"$table\")");
    $columnsInfo = $stmtCols->fetchAll(PDO::FETCH_ASSOC);
    
    $createLines = [];
    foreach ($columnsInfo as $col) {
        $name = $col['name'];
        $type = strtoupper($col['type']);
        $notnull = $col['notnull'] ? 'NOT NULL' : 'NULL';
        $default = '';
        
        // Handle Default Value
        if ($col['dflt_value'] !== null) {
            $dVal = $col['dflt_value'];
            if (strcasecmp($dVal, 'CURRENT_TIMESTAMP') === 0) {
                $default = "DEFAULT CURRENT_TIMESTAMP";
            } elseif (is_numeric($dVal)) {
                $default = "DEFAULT $dVal";
            } else {
                $default = "DEFAULT '" . trim($dVal, "'\"") . "'";
            }
        } elseif (!$col['notnull']) {
            $default = "DEFAULT NULL";
        }
        
        // Map types
        $mysqlType = 'VARCHAR(255)';
        if (strpos($type, 'INT') !== false) $mysqlType = 'INT(11)';
        elseif (strpos($type, 'CHAR') !== false) $mysqlType = 'VARCHAR(255)';
        elseif (strpos($type, 'TEXT') !== false) $mysqlType = 'TEXT';
        elseif (strpos($type, 'REAL') !== false || strpos($type, 'FLOA') !== false || strpos($type, 'DOUB') !== false) $mysqlType = 'DECIMAL(10,2)'; // Money
        elseif (strpos($type, 'DATE') !== false) $mysqlType = 'DATE';
        elseif (strpos($type, 'DATETIME') !== false) $mysqlType = 'DATETIME';
        
        // Handle PK AutoIncrement
        $extra = '';
        if ($col['pk'] == 1) {
            $pk = $name;
            if ($type === 'INTEGER') { 
                $extra = 'AUTO_INCREMENT';
                $notnull = 'NOT NULL';
                $default = ''; 
            }
        }
        
        $line = "`$name` $mysqlType $notnull $default $extra";
        $createLines[] = trim($line);
    }
    
    $sql = "CREATE TABLE `$table` (\n  " . implode(",\n  ", $createLines);
    if ($pk) {
        $sql .= ",\n  PRIMARY KEY (`$pk`)";
    }
    $sql .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";
    
    fwrite($handle, $sql);
    
    // Data
    fwrite($handle, "-- Dumping data for `$table`\n");
    $stmtData = $pdo->query("SELECT * FROM \"$table\"");
    
    $rowsBuffer = [];
    $bufferSize = 50;
    
    while ($row = $stmtData->fetch(PDO::FETCH_ASSOC)) {
        $vals = [];
        foreach ($row as $k => $v) {
            if ($v === null) {
                $vals[] = "NULL";
            } else {
                $v = addslashes($v); 
                $v = str_replace(["\n", "\r"], ["\\n", "\\r"], $v);
                $vals[] = "'$v'";
            }
        }
        $rowsBuffer[] = "(" . implode(", ", $vals) . ")";
        
        if (count($rowsBuffer) >= $bufferSize) {
            fwrite($handle, "INSERT INTO `$table` VALUES " . implode(", ", $rowsBuffer) . ";\n");
            $rowsBuffer = [];
        }
    }
    
    if (!empty($rowsBuffer)) {
        fwrite($handle, "INSERT INTO `$table` VALUES " . implode(", ", $rowsBuffer) . ";\n");
    }
    
    fwrite($handle, "\n");
}

fwrite($handle, "COMMIT;\n");
fclose($handle);

echo "Update SQL created at: $outputFile\n";
