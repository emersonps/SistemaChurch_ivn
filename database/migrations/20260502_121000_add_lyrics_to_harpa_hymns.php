<?php

class AddLyricsToHarpaHymns20260502 {
    private function columnExists($db, $table, $column) {
        $driver = (string)$db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $stmt = $db->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
            $stmt->execute([$column]);
            return (bool)$stmt->fetch();
        }

        $stmt = $db->query("PRAGMA table_info($table)");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
            if (($col['name'] ?? '') === $column) {
                return true;
            }
        }
        return false;
    }

    private function extractTextFromPptx($pptxPath) {
        $zip = new ZipArchive();
        if ($zip->open($pptxPath) !== true) {
            throw new Exception('Falha ao abrir PPTX.');
        }

        $texts = [];
        $slidePaths = [];
        $notesPaths = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!is_string($name)) {
                continue;
            }
            if (preg_match('#^ppt/slides/slide\\d+\\.xml$#i', $name)) {
                $slidePaths[] = $name;
            } elseif (preg_match('#^ppt/notesSlides/notesSlide\\d+\\.xml$#i', $name)) {
                $notesPaths[] = $name;
            }
        }

        $sortByNumber = function ($a, $b) {
            preg_match('/(\\d+)/', $a, $ma);
            preg_match('/(\\d+)/', $b, $mb);
            return ((int)($ma[1] ?? 0)) <=> ((int)($mb[1] ?? 0));
        };
        usort($slidePaths, $sortByNumber);
        usort($notesPaths, $sortByNumber);

        $extractFromXml = function ($xml) {
            $xml = (string)$xml;
            if ($xml === '') {
                return [];
            }

            $doc = new DOMDocument();
            $prev = libxml_use_internal_errors(true);
            $ok = $doc->loadXML($xml);
            libxml_clear_errors();
            libxml_use_internal_errors($prev);
            if (!$ok) {
                return [];
            }

            $xpath = new DOMXPath($doc);
            $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
            $nodes = $xpath->query('//a:t');
            $lines = [];
            if ($nodes) {
                foreach ($nodes as $node) {
                    $t = trim((string)$node->textContent);
                    if ($t !== '') {
                        $lines[] = $t;
                    }
                }
            }
            return $lines;
        };

        $blocks = [];
        foreach (array_merge($slidePaths, $notesPaths) as $path) {
            $xml = $zip->getFromName($path);
            if ($xml === false) {
                continue;
            }
            $lines = $extractFromXml($xml);
            if (count($lines) === 0) {
                continue;
            }
            $block = trim(implode("\n", $lines));
            if ($block !== '') {
                $blocks[] = $block;
            }
        }

        $zip->close();

        $seen = [];
        $uniqueBlocks = [];
        foreach ($blocks as $b) {
            $k = strtolower(preg_replace('/\\s+/', ' ', $b));
            if (!isset($seen[$k])) {
                $seen[$k] = true;
                $uniqueBlocks[] = $b;
            }
        }

        $lyrics = trim(implode("\n\n", $uniqueBlocks));
        $lyrics = preg_replace("/[ \\t]+/u", " ", $lyrics);
        $lyrics = preg_replace("/\\n{3,}/u", "\n\n", $lyrics);
        return trim($lyrics);
    }

    public function up($db) {
        $driver = (string)$db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if (!$this->columnExists($db, 'harpa_hymns', 'pptx_file_name')) {
            $db->exec("ALTER TABLE harpa_hymns ADD COLUMN pptx_file_name " . ($driver === 'mysql' ? 'VARCHAR(255) NULL' : 'TEXT'));
        }
        if (!$this->columnExists($db, 'harpa_hymns', 'lyrics')) {
            $db->exec("ALTER TABLE harpa_hymns ADD COLUMN lyrics " . ($driver === 'mysql' ? 'LONGTEXT NULL' : 'TEXT'));
        }
        if (!$this->columnExists($db, 'harpa_hymns', 'extract_status')) {
            $db->exec("ALTER TABLE harpa_hymns ADD COLUMN extract_status " . ($driver === 'mysql' ? "VARCHAR(30) NOT NULL DEFAULT 'pending'" : "TEXT NOT NULL DEFAULT 'pending'"));
        }
        if (!$this->columnExists($db, 'harpa_hymns', 'extract_error')) {
            $db->exec("ALTER TABLE harpa_hymns ADD COLUMN extract_error " . ($driver === 'mysql' ? 'TEXT NULL' : 'TEXT'));
        }
        if (!$this->columnExists($db, 'harpa_hymns', 'extracted_at')) {
            $db->exec("ALTER TABLE harpa_hymns ADD COLUMN extracted_at " . ($driver === 'mysql' ? 'DATETIME NULL' : 'DATETIME'));
        }

        $projectRoot = dirname(__DIR__, 2);
        $harpaDir = $projectRoot . DIRECTORY_SEPARATOR . 'harpa_crista';
        $harpaDirReal = realpath($harpaDir);
        if (!$harpaDirReal || !is_dir($harpaDirReal)) {
            return;
        }

        $rows = $db->query("SELECT hymn_number, file_name, pptx_file_name, lyrics FROM harpa_hymns ORDER BY hymn_number ASC")->fetchAll(PDO::FETCH_ASSOC);
        if (!is_array($rows) || count($rows) === 0) {
            return;
        }

        $update = $db->prepare("UPDATE harpa_hymns SET pptx_file_name = ?, lyrics = ?, extract_status = ?, extract_error = NULL, extracted_at = CURRENT_TIMESTAMP WHERE hymn_number = ?");
        $markPending = $db->prepare("UPDATE harpa_hymns SET extract_status = ?, extract_error = ?, extracted_at = NULL WHERE hymn_number = ?");

        foreach ($rows as $r) {
            $num = (int)($r['hymn_number'] ?? 0);
            if ($num <= 0) {
                continue;
            }

            $fileName = (string)($r['file_name'] ?? '');
            $pptxName = (string)($r['pptx_file_name'] ?? '');
            $lyrics = (string)($r['lyrics'] ?? '');

            $effectiveName = $pptxName !== '' ? $pptxName : $fileName;
            $ext = strtolower((string)pathinfo($effectiveName, PATHINFO_EXTENSION));

            if ($lyrics !== '') {
                continue;
            }

            if ($ext !== 'pptx') {
                $markPending->execute(['pending_conversion', 'Arquivo .ppt precisa de conversão para extrair letra.', $num]);
                continue;
            }

            $pptxPath = $harpaDirReal . DIRECTORY_SEPARATOR . $effectiveName;
            $pptxReal = realpath($pptxPath);
            if (!$pptxReal || strpos($pptxReal, $harpaDirReal) !== 0 || !is_file($pptxReal)) {
                $markPending->execute(['missing_file', 'Arquivo PPTX não encontrado.', $num]);
                continue;
            }

            try {
                $text = $this->extractTextFromPptx($pptxReal);
                if ($text === '') {
                    $markPending->execute(['empty', 'Nenhum texto encontrado no PPTX.', $num]);
                    continue;
                }
                $update->execute([$pptxName !== '' ? $pptxName : $fileName, $text, 'ok', $num]);
            } catch (Throwable $e) {
                $markPending->execute(['error', $e->getMessage(), $num]);
            }
        }
    }
}
