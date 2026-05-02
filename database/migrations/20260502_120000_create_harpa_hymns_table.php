<?php

class CreateHarpaHymnsTable20260502 {
    public function up($db) {
        $driver = (string)$db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            $db->exec("
                CREATE TABLE IF NOT EXISTS harpa_hymns (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    hymn_number INT(11) NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    file_name VARCHAR(255) NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY uq_harpa_hymns_number (hymn_number)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        } else {
            $db->exec("
                CREATE TABLE IF NOT EXISTS harpa_hymns (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    hymn_number INTEGER NOT NULL,
                    title TEXT NOT NULL,
                    file_name TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ");
            $db->exec("CREATE UNIQUE INDEX IF NOT EXISTS uq_harpa_hymns_number ON harpa_hymns (hymn_number);");
        }

        $projectRoot = dirname(__DIR__, 2);
        $harpaDir = $projectRoot . DIRECTORY_SEPARATOR . 'harpa_crista';
        if (!is_dir($harpaDir)) {
            return;
        }

        $files = scandir($harpaDir);
        if (!is_array($files)) {
            return;
        }

        $selectStmt = $db->prepare("SELECT id FROM harpa_hymns WHERE hymn_number = ? LIMIT 1");
        $insertStmt = $db->prepare("INSERT INTO harpa_hymns (hymn_number, title, file_name) VALUES (?, ?, ?)");
        $updateStmt = $db->prepare("UPDATE harpa_hymns SET title = ?, file_name = ?, updated_at = CURRENT_TIMESTAMP WHERE hymn_number = ?");

        foreach ($files as $file) {
            if (!is_string($file) || $file === '.' || $file === '..') {
                continue;
            }
            if (strpos($file, '~$') === 0) {
                continue;
            }
            if (!preg_match('/\.(pptx?)$/i', $file)) {
                continue;
            }

            if (!preg_match('/^(\d+)\s*-\s*(.*?)\.(pptx?)$/i', $file, $m)) {
                continue;
            }

            $num = (int)($m[1] ?? 0);
            if ($num <= 0) {
                continue;
            }

            $title = trim((string)($m[2] ?? ''));
            if ($title === '' || $title === '-') {
                $title = 'Hino sem título';
            }

            $selectStmt->execute([$num]);
            $existingId = $selectStmt->fetchColumn();
            if ($existingId) {
                $updateStmt->execute([$title, $file, $num]);
            } else {
                $insertStmt->execute([$num, $title, $file]);
            }
        }
    }
}

