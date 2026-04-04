<?php

class DatabaseBackupManager {
    private $db;
    private $backupDir;
    private $lockFile;

    public function __construct() {
        $this->db = (new Database())->connect();
        $this->backupDir = dirname(__DIR__, 2) . '/storage/backups/database';
        $this->lockFile = $this->backupDir . '/backup.lock';
    }

    public function getBackupDirectory() {
        $this->ensureBackupDirectory();
        return $this->backupDir;
    }

    public function listBackups() {
        $dir = $this->getBackupDirectory();
        $files = glob($dir . '/*.sql') ?: [];
        $backups = [];

        foreach ($files as $file) {
            $filename = basename($file);
            $mode = str_starts_with($filename, 'auto_') ? 'automatico' : 'manual';
            $parts = explode('_', pathinfo($filename, PATHINFO_FILENAME));
            $driver = end($parts);

            $backups[] = [
                'filename' => $filename,
                'mode' => $mode,
                'driver' => $driver ?: 'db',
                'size' => filesize($file),
                'created_at' => filemtime($file),
                'path' => $file
            ];
        }

        usort($backups, function ($a, $b) {
            return $b['created_at'] <=> $a['created_at'];
        });

        return $backups;
    }

    public function ensureWeeklyBackup() {
        $latestAuto = $this->getLatestBackupTimestamp('automatico');
        if ($latestAuto && (time() - $latestAuto) < 7 * 24 * 60 * 60) {
            return null;
        }

        $backup = $this->createBackup('auto');
        $this->pruneAutomaticBackups(12);
        return $backup;
    }

    public function createBackup($mode = 'manual') {
        $this->ensureBackupDirectory();
        $handle = fopen($this->lockFile, 'c+');
        if (!$handle) {
            throw new RuntimeException('Não foi possível preparar o bloqueio do backup.');
        }

        if (!flock($handle, LOCK_EX)) {
            fclose($handle);
            throw new RuntimeException('Não foi possível obter bloqueio para gerar o backup.');
        }

        try {
            $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
            $prefix = $mode === 'auto' ? 'auto' : 'manual';
            $filename = sprintf('%s_backup_%s_%s.sql', $prefix, date('Ymd_His'), $driver);
            $filePath = $this->backupDir . '/' . $filename;
            $dump = $driver === 'mysql' ? $this->buildMySqlDump() : $this->buildSqliteDump();
            file_put_contents($filePath, $dump);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }

        return [
            'filename' => $filename,
            'path' => $filePath
        ];
    }

    public function getBackupPath($filename) {
        $safe = basename((string)$filename);
        if ($safe === '' || preg_match('/^[a-zA-Z0-9_\-\.]+$/', $safe) !== 1) {
            return null;
        }

        $path = $this->getBackupDirectory() . '/' . $safe;
        if (!is_file($path)) {
            return null;
        }

        return $path;
    }

    private function ensureBackupDirectory() {
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0777, true);
        }
    }

    private function getLatestBackupTimestamp($mode) {
        $backups = $this->listBackups();
        foreach ($backups as $backup) {
            if ($backup['mode'] === $mode) {
                return $backup['created_at'];
            }
        }
        return null;
    }

    private function pruneAutomaticBackups($keep = 12) {
        $autos = array_values(array_filter($this->listBackups(), function ($backup) {
            return $backup['mode'] === 'automatico';
        }));

        if (count($autos) <= $keep) {
            return;
        }

        $toDelete = array_slice($autos, $keep);
        foreach ($toDelete as $backup) {
            if (is_file($backup['path'])) {
                @unlink($backup['path']);
            }
        }
    }

    private function buildMySqlDump() {
        $dump = "-- SistemaChurch Database Backup\n";
        $dump .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n";
        $dump .= "-- Driver: mysql\n\n";
        $dump .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $dump .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
        $dump .= "SET NAMES utf8mb4;\n\n";

        $tables = $this->db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_NUM);
        foreach ($tables as $tableRow) {
            $table = $tableRow[0];
            $createRow = $this->db->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
            $createSql = $createRow['Create Table'] ?? array_values($createRow)[1] ?? null;
            if (!$createSql) {
                continue;
            }

            $dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $dump .= $createSql . ";\n\n";
            $dump .= $this->buildTableDataDump($table);
        }

        $dump .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        return $dump;
    }

    private function buildSqliteDump() {
        $dump = "-- SistemaChurch Database Backup\n";
        $dump .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n";
        $dump .= "-- Driver: sqlite\n\n";
        $dump .= "PRAGMA foreign_keys = OFF;\n";
        $dump .= "BEGIN TRANSACTION;\n\n";

        $schemaStmt = $this->db->query("
            SELECT type, name, sql
            FROM sqlite_master
            WHERE sql IS NOT NULL AND name NOT LIKE 'sqlite_%'
            ORDER BY CASE type
                WHEN 'table' THEN 1
                WHEN 'index' THEN 2
                WHEN 'trigger' THEN 3
                ELSE 9
            END, name ASC
        ");
        $schemaRows = $schemaStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($schemaRows as $row) {
            if (($row['type'] ?? '') === 'table') {
                $dump .= "DROP TABLE IF EXISTS \"{$row['name']}\";\n";
                $dump .= $row['sql'] . ";\n\n";
                $dump .= $this->buildTableDataDump($row['name']);
            } else {
                $dump .= $row['sql'] . ";\n\n";
            }
        }

        $dump .= "COMMIT;\n";
        $dump .= "PRAGMA foreign_keys = ON;\n";
        return $dump;
    }

    private function buildTableDataDump($table) {
        $columns = $this->getTableColumns($table);
        if (empty($columns)) {
            return '';
        }

        $quotedColumns = array_map(function ($column) {
            return "`{$column}`";
        }, $columns);

        $stmt = $this->db->query("SELECT * FROM " . $this->quoteIdentifier($table));
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            return "\n";
        }

        $dump = '';
        foreach ($rows as $row) {
            $values = [];
            foreach ($columns as $column) {
                $values[] = $this->sqlValue($row[$column] ?? null);
            }
            $dump .= "INSERT INTO " . $this->quoteIdentifier($table) . " (" . implode(', ', $quotedColumns) . ") VALUES (" . implode(', ', $values) . ");\n";
        }

        return $dump . "\n";
    }

    private function getTableColumns($table) {
        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $stmt = $this->db->query("SHOW COLUMNS FROM " . $this->quoteIdentifier($table));
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map(function ($row) {
                return $row['Field'];
            }, $rows);
        }

        $stmt = $this->db->query("PRAGMA table_info(" . $this->quoteIdentifier($table) . ")");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(function ($row) {
            return $row['name'];
        }, $rows);
    }

    private function quoteIdentifier($identifier) {
        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            return '`' . str_replace('`', '``', $identifier) . '`';
        }
        return '"' . str_replace('"', '""', $identifier) . '"';
    }

    private function sqlValue($value) {
        if ($value === null) {
            return 'NULL';
        }
        return $this->db->quote((string)$value);
    }
}
