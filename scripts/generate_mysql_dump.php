<?php
// scripts/generate_mysql_dump.php

$dbFile = __DIR__ . '/../database/SistemaChurch.db';
$dumpFile = __DIR__ . '/../public/backup_mysql.sql';

if (!file_exists($dbFile)) {
    die("Database file not found.\n");
}

try {
    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

$handle = fopen($dumpFile, 'w');
if (!$handle) die("Cannot open output file.\n");

// Header
fwrite($handle, "-- MySQL Dump generated from SQLite\n");
fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n\n");
fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
fwrite($handle, "START TRANSACTION;\n");
fwrite($handle, "SET time_zone = \"+00:00\";\n");
fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n\n");

// Get tables
$tables = [];
$stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $tables[] = $row['name'];
}

foreach ($tables as $table) {
    echo "Processing table: $table...\n";
    
    // 1. Structure
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
        $notnull = $col['notnull'] ? 'NOT NULL' : 'NULL'; // MySQL uses NULL for nullable
        $default = '';
        
        // Handle Default Value
        if ($col['dflt_value'] !== null) {
            $dVal = $col['dflt_value'];
            // Remove wrapping quotes if present
            if (preg_match("/^'(.*)'$/", $dVal, $m) || preg_match('/^"(.*)"$/', $dVal, $m)) {
                $dVal = $m[1];
                $default = "DEFAULT '$dVal'";
            } elseif (strcasecmp($dVal, 'CURRENT_TIMESTAMP') === 0) {
                 $default = "DEFAULT CURRENT_TIMESTAMP";
             } elseif (strcasecmp($dVal, 'NULL') === 0) {
                 $default = "DEFAULT NULL";
             } elseif (is_numeric($dVal)) {
                 $default = "DEFAULT $dVal";
             } else {
                 // Fallback for unquoted strings that might be keywords or literals
                 $default = "DEFAULT '$dVal'";
             }
         } elseif (!$col['notnull']) {
             $default = "DEFAULT NULL";
         }
         
         // Map types
         $mysqlType = 'VARCHAR(255)'; // Default fallback
         if (strpos($type, 'INT') !== false) $mysqlType = 'INT(11)';
         elseif (strpos($type, 'CHAR') !== false) $mysqlType = 'VARCHAR(255)';
         elseif (strpos($type, 'TEXT') !== false) {
             // Heuristic: Use VARCHAR for short fields, TEXT for long
             if (in_array($name, ['username', 'password', 'email', 'phone', 'role', 'cpf', 'rg', 'zip_code', 'state', 'city', 'type', 'status', 'gender', 'marital_status'])) {
                 $mysqlType = 'VARCHAR(255)';
             } else {
                 $mysqlType = 'TEXT';
             }
         }
        elseif (strpos($type, 'REAL') !== false || strpos($type, 'FLOA') !== false || strpos($type, 'DOUB') !== false) $mysqlType = 'DOUBLE';
        elseif (strpos($type, 'BLOB') !== false) $mysqlType = 'LONGBLOB';
        elseif (strpos($type, 'DATE') !== false) $mysqlType = 'DATETIME';
        
        // Handle PK AutoIncrement
        $extra = '';
        if ($col['pk'] == 1) {
            $pk = $name;
            if ($type === 'INTEGER') { 
                $extra = 'AUTO_INCREMENT';
                $notnull = 'NOT NULL';
                $default = ''; // No default for AI
            }
        }
        
        // Specific fix for 'id' columns just in case
        if ($name === 'id') {
            $mysqlType = 'INT(11)';
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
    
    // 2. Data
    fwrite($handle, "-- Dumping data for `$table`\n");
    $stmtData = $pdo->query("SELECT * FROM \"$table\"");
    
    $rowsBuffer = [];
    $bufferSize = 50; // Reduced buffer size for safety
    
    while ($row = $stmtData->fetch(PDO::FETCH_ASSOC)) {
        $vals = [];
        foreach ($row as $k => $v) {
            if ($v === null) {
                $vals[] = "NULL";
            } else {
                // Better MySQL escaping
                $v = addslashes($v); 
                // Fix newlines for SQL dump
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

fwrite($handle, "SET FOREIGN_KEY_CHECKS = 1;\n");
fwrite($handle, "COMMIT;\n");
fclose($handle);

echo "MySQL dump created at: $dumpFile\n";
