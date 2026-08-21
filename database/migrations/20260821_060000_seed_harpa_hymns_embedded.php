<?php

// Seed autocontido dos 640 hinos da Harpa Cristã (número, título, arquivo, letra).
// Diferente de 20260502_130000 (que copia de um banco irmão no mesmo servidor MySQL,
// algo que só existe quando as instâncias compartilham a mesma conta de hospedagem),
// esta migração lê o JSON embutido em database/seeds/harpa_hymns.json, então funciona
// em qualquer instância nova, mesmo hospedada isoladamente.

class SeedHarpaHymnsEmbedded20260821 {
    public function up($db) {
        $driver = (string)$db->getAttribute(PDO::ATTR_DRIVER_NAME);

        $lyricsCount = (int)$db->query("SELECT COUNT(*) FROM harpa_hymns WHERE lyrics IS NOT NULL AND TRIM(lyrics) <> ''")->fetchColumn();
        if ($lyricsCount >= 600) {
            return;
        }

        $seedPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'seeds' . DIRECTORY_SEPARATOR . 'harpa_hymns.json';
        if (!is_file($seedPath)) {
            return;
        }

        $json = file_get_contents($seedPath);
        $rows = json_decode((string)$json, true);
        if (!is_array($rows) || count($rows) === 0) {
            return;
        }

        $selectStmt = $db->prepare("SELECT lyrics FROM harpa_hymns WHERE hymn_number = ? LIMIT 1");
        $insertStmt = $db->prepare("INSERT INTO harpa_hymns (hymn_number, title, file_name, pptx_file_name, lyrics, extract_status, extract_error, extracted_at) VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
        $updateStmt = $db->prepare("UPDATE harpa_hymns SET title = ?, file_name = ?, pptx_file_name = ?, lyrics = ?, extract_status = ?, extract_error = ?, extracted_at = CURRENT_TIMESTAMP WHERE hymn_number = ?");

        foreach ($rows as $r) {
            $num = (int)($r['hymn_number'] ?? 0);
            if ($num <= 0) {
                continue;
            }

            $title = (string)($r['title'] ?? '');
            $fileName = (string)($r['file_name'] ?? '');
            $pptxFileName = $r['pptx_file_name'] ?? null;
            $lyrics = (string)($r['lyrics'] ?? '');
            $status = (string)($r['extract_status'] ?? 'ok');
            $error = $r['extract_error'] ?? null;

            $selectStmt->execute([$num]);
            $existingLyrics = $selectStmt->fetchColumn();

            if ($existingLyrics === false) {
                $insertStmt->execute([$num, $title, $fileName, $pptxFileName, $lyrics ?: null, $status, $error]);
                continue;
            }

            if (trim((string)$existingLyrics) !== '') {
                continue;
            }

            $updateStmt->execute([$title, $fileName, $pptxFileName, $lyrics ?: null, $status, $error, $num]);
        }
    }
}
