<?php

class SeedHarpaHymnsFromSourceDb20260502 {
    private function isSafeDbName($name) {
        return is_string($name) && preg_match('/^[a-zA-Z0-9_]+$/', $name);
    }

    private function getDestDatabaseName($db) {
        try {
            $name = $db->query('SELECT DATABASE()')->fetchColumn();
            return is_string($name) && $name !== '' ? $name : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    private function hasHarpaTable($db, $schema) {
        if (!$this->isSafeDbName($schema)) {
            return false;
        }

        $stmt = $db->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = 'harpa_hymns'");
        $stmt->execute([$schema]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function countHarpaLyrics($db, $schemaOrNull) {
        try {
            if ($schemaOrNull === null) {
                return (int)$db->query("SELECT COUNT(*) FROM harpa_hymns WHERE lyrics IS NOT NULL AND TRIM(lyrics) <> ''")->fetchColumn();
            }

            if (!$this->isSafeDbName($schemaOrNull)) {
                return 0;
            }

            $sql = "SELECT COUNT(*) FROM `{$schemaOrNull}`.harpa_hymns WHERE lyrics IS NOT NULL AND TRIM(lyrics) <> ''";
            return (int)$db->query($sql)->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function resolveSourceDb($db, $destDb) {
        $env = getenv('HARPA_SEED_SOURCE_DB');
        if (is_string($env)) {
            $env = trim($env);
            if ($env !== '' && $this->isSafeDbName($env) && $env !== $destDb && $this->hasHarpaTable($db, $env)) {
                if ($this->countHarpaLyrics($db, $env) > 0) {
                    return $env;
                }
            }
        }

        $candidates = [
            'sistemaieadsena',
            'sistemachurch_seed',
            'sistemadefault',
        ];

        foreach ($candidates as $c) {
            if ($c === $destDb) {
                continue;
            }
            if (!$this->hasHarpaTable($db, $c)) {
                continue;
            }
            if ($this->countHarpaLyrics($db, $c) > 0) {
                return $c;
            }
        }

        return null;
    }

    public function up($db) {
        $driver = (string)$db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver !== 'mysql') {
            return;
        }

        $destDb = $this->getDestDatabaseName($db);
        $sourceDb = $this->resolveSourceDb($db, $destDb);
        if (!$sourceDb) {
            return;
        }

        if (!$this->hasHarpaTable($db, $sourceDb)) {
            return;
        }

        if ($this->countHarpaLyrics($db, null) >= 100) {
            return;
        }

        $insertSql = "
            INSERT INTO harpa_hymns (
                hymn_number,
                title,
                file_name,
                pptx_file_name,
                lyrics,
                extract_status,
                extract_error,
                extracted_at,
                created_at,
                updated_at
            )
            SELECT
                src.hymn_number,
                src.title,
                src.file_name,
                src.pptx_file_name,
                src.lyrics,
                COALESCE(NULLIF(src.extract_status, ''), 'ok') AS extract_status,
                src.extract_error,
                src.extracted_at,
                src.created_at,
                src.updated_at
            FROM `{$sourceDb}`.harpa_hymns AS src
            ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                file_name = VALUES(file_name),
                pptx_file_name = COALESCE(VALUES(pptx_file_name), harpa_hymns.pptx_file_name),
                lyrics = IF(harpa_hymns.lyrics IS NULL OR TRIM(harpa_hymns.lyrics) = '', VALUES(lyrics), harpa_hymns.lyrics),
                extract_status = IF(harpa_hymns.extract_status IS NULL OR harpa_hymns.extract_status = 'pending', VALUES(extract_status), harpa_hymns.extract_status),
                extract_error = IF(harpa_hymns.extract_error IS NULL OR harpa_hymns.extract_error = '', VALUES(extract_error), harpa_hymns.extract_error),
                extracted_at = COALESCE(harpa_hymns.extracted_at, VALUES(extracted_at)),
                updated_at = CURRENT_TIMESTAMP
        ";

        $db->exec($insertSql);
    }
}

