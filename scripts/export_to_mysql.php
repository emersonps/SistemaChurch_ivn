<?php
// scripts/export_to_mysql.php

$dbPath = __DIR__ . '/../database/SistemaChurch.db';
$dumpFile = __DIR__ . '/../database/dump_mysql.sql';

if (!file_exists($dbPath)) {
    die("Database file not found at: " . $dbPath);
}

try {
    $sqlite = new PDO("sqlite:$dbPath");
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sqlite->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$output = "";

$output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$output .= "START TRANSACTION;\n";
$output .= "SET time_zone = \"+00:00\";\n";
$output .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

// --- Users ---
$output .= "-- Table structure for table `users` --\n";
$output .= "DROP TABLE IF EXISTS `users`;\n";
$output .= "CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'admin',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

$output .= dumpTableData($sqlite, 'users');

// --- Congregations ---
$output .= "-- Table structure for table `congregations` --\n";
$output .= "DROP TABLE IF EXISTS `congregations`;\n";
$output .= "CREATE TABLE `congregations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` text,
  `leader_name` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'congregation',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `opening_date` date DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `service_schedule` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

$output .= dumpTableData($sqlite, 'congregations');

// --- Members ---
$output .= "-- Table structure for table `members` --\n";
$output .= "DROP TABLE IF EXISTS `members`;\n";
$output .= "CREATE TABLE `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `congregation_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `baptism_date` date DEFAULT NULL,
  `is_baptized` tinyint(1) DEFAULT '0',
  `status` varchar(50) DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `gender` varchar(20) DEFAULT NULL,
  `cpf` varchar(20) DEFAULT NULL,
  `rg` varchar(20) DEFAULT NULL,
  `marital_status` varchar(50) DEFAULT NULL,
  `address` text,
  `address_number` varchar(20) DEFAULT NULL,
  `neighborhood` varchar(100) DEFAULT NULL,
  `complement` varchar(255) DEFAULT NULL,
  `reference_point` text,
  `zip_code` varchar(20) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `birthplace` varchar(100) DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `children_count` int(11) DEFAULT '0',
  `profession` varchar(255) DEFAULT NULL,
  `church_origin` varchar(255) DEFAULT NULL,
  `admission_method` varchar(255) DEFAULT NULL,
  `admission_date` date DEFAULT NULL,
  `exit_date` date DEFAULT NULL,
  `is_tither` tinyint(1) DEFAULT '0',
  `photo` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `congregation_id` (`congregation_id`),
  CONSTRAINT `members_ibfk_1` FOREIGN KEY (`congregation_id`) REFERENCES `congregations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

$output .= dumpTableData($sqlite, 'members');

// --- Tithes ---
$output .= "-- Table structure for table `tithes` --\n";
$output .= "DROP TABLE IF EXISTS `tithes`;\n";
$output .= "CREATE TABLE `tithes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `type` varchar(50) DEFAULT 'dizimo',
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  CONSTRAINT `tithes_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

$output .= dumpTableData($sqlite, 'tithes');

// --- Events ---
$output .= "-- Table structure for table `events` --\n";
$output .= "DROP TABLE IF EXISTS `events`;\n";
$output .= "CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `event_date` datetime DEFAULT NULL,
  `location` text,
  `type` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) DEFAULT 'active',
  `recurring_days` text,
  `end_time` time DEFAULT NULL,
  `congregation_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `congregation_id` (`congregation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

$output .= dumpTableData($sqlite, 'events');

// --- Photo Albums ---
$output .= "-- Table structure for table `photo_albums` --\n";
$output .= "DROP TABLE IF EXISTS `photo_albums`;\n";
$output .= "CREATE TABLE `photo_albums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `event_date` date DEFAULT NULL,
  `location` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

$output .= dumpTableData($sqlite, 'photo_albums');

// --- Photos ---
$output .= "-- Table structure for table `photos` --\n";
$output .= "DROP TABLE IF EXISTS `photos`;\n";
$output .= "CREATE TABLE `photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `album_id` (`album_id`),
  CONSTRAINT `photos_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `photo_albums` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

$output .= dumpTableData($sqlite, 'photos');


$output .= "SET FOREIGN_KEY_CHECKS = 1;\n";
$output .= "COMMIT;\n";

file_put_contents($dumpFile, $output);
echo "Dump created successfully at: $dumpFile\n";

function dumpTableData($pdo, $tableName) {
    $stmt = $pdo->query("SELECT * FROM `$tableName`");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($rows)) {
        return "";
    }

    $sql = "INSERT INTO `$tableName` (";
    $firstRow = $rows[0];
    $columns = array_keys($firstRow);
    $sql .= implode(", ", array_map(function($col) { return "`$col`"; }, $columns));
    $sql .= ") VALUES \n";

    $valuesList = [];
    foreach ($rows as $row) {
        $rowValues = [];
        foreach ($row as $val) {
            if ($val === null) {
                $rowValues[] = "NULL";
            } else {
                // Manual escaping for SQL
                $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
                $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
                $val = str_replace($search, $replace, $val);
                $rowValues[] = "'$val'";
            }
        }
        $valuesList[] = "(" . implode(", ", $rowValues) . ")";
    }

    $sql .= implode(",\n", $valuesList);
    $sql .= ";\n\n";
    
    return $sql;
}
