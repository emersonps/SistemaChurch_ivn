<?php

class HarpaController {
    private function requireDeveloper() {
        if (!isset($_SESSION['user_id'])) {
            redirect('/admin/login');
        }

        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            return;
        }

        if (function_exists('hasPermission') && hasPermission('developer.access')) {
            return;
        }

        redirect('/admin/dashboard');
    }

    private function findSofficePath() {
        $candidates = [];
        $pf = getenv('ProgramFiles');
        $pfx86 = getenv('ProgramFiles(x86)');

        if (is_string($pf) && $pf !== '') {
            $candidates[] = $pf . '\\LibreOffice\\program\\soffice.exe';
        }
        if (is_string($pfx86) && $pfx86 !== '') {
            $candidates[] = $pfx86 . '\\LibreOffice\\program\\soffice.exe';
        }
        $candidates[] = 'C:\\Program Files\\LibreOffice\\program\\soffice.exe';
        $candidates[] = 'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe';

        foreach ($candidates as $p) {
            if (is_string($p) && $p !== '' && file_exists($p)) {
                return $p;
            }
        }

        $out = @shell_exec('where soffice 2>NUL');
        if (is_string($out)) {
            $lines = preg_split("/\\r\\n|\\n|\\r/", trim($out));
            foreach ($lines as $l) {
                $l = trim((string)$l);
                if ($l !== '' && file_exists($l)) {
                    return $l;
                }
            }
        }

        return null;
    }

    private function escapePowerShellSingleQuotedString($value) {
        $value = (string)$value;
        return str_replace("'", "''", $value);
    }

    private function canCreatePowerPointCom() {
        $cmd = "powershell -NoProfile -ExecutionPolicy Bypass -Command \"try { \$ppt = New-Object -ComObject PowerPoint.Application; \$ppt.Quit(); Write-Output 'ok'; exit 0 } catch { Write-Output \$_.Exception.Message; exit 2 }\"";
        $out = @shell_exec($cmd);
        if (!is_string($out)) {
            return false;
        }
        return trim($out) === 'ok';
    }

    private function runPowerPointConvert($inputFile, $outputFile) {
        $in = $this->escapePowerShellSingleQuotedString($inputFile);
        $out = $this->escapePowerShellSingleQuotedString($outputFile);

        $ps = "\$ErrorActionPreference = 'Stop'; " .
            "\$in = '$in'; " .
            "\$out = '$out'; " .
            "\$ppt = New-Object -ComObject PowerPoint.Application; " .
            "\$pres = \$ppt.Presentations.Open(\$in, \$true, \$true, 0); " .
            "\$pres.SaveAs(\$out, 24); " .
            "\$pres.Close(); " .
            "\$ppt.Quit(); " .
            "[System.Runtime.InteropServices.Marshal]::ReleaseComObject(\$pres) | Out-Null; " .
            "[System.Runtime.InteropServices.Marshal]::ReleaseComObject(\$ppt) | Out-Null; " .
            "exit 0";

        $cmd = 'powershell -NoProfile -ExecutionPolicy Bypass -Command "' . str_replace('"', '\"', $ps) . '"';
        $desc = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $proc = @proc_open($cmd, $desc, $pipes, null, null);
        if (!is_resource($proc)) {
            return ['ok' => false, 'code' => 999, 'out' => '', 'err' => 'Falha ao iniciar PowerPoint.'];
        }

        @fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        @fclose($pipes[1]);
        @fclose($pipes[2]);
        $code = proc_close($proc);

        return ['ok' => $code === 0, 'code' => $code, 'out' => (string)$stdout, 'err' => (string)$stderr];
    }

    private function runSofficeConvert($sofficePath, $inputFile, $outDir) {
        $cmd = '"' . $sofficePath . '" --headless --nologo --nolockcheck --norestore --convert-to pptx --outdir "' . $outDir . '" "' . $inputFile . '"';
        $desc = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $proc = @proc_open($cmd, $desc, $pipes, null, null);
        if (!is_resource($proc)) {
            return ['ok' => false, 'code' => 999, 'out' => '', 'err' => 'Falha ao iniciar LibreOffice.'];
        }

        @fclose($pipes[0]);
        $out = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        @fclose($pipes[1]);
        @fclose($pipes[2]);

        $code = proc_close($proc);
        return ['ok' => $code === 0, 'code' => $code, 'out' => (string)$out, 'err' => (string)$err];
    }

    private function extractTextFromPptx($pptxPath) {
        $zip = new ZipArchive();
        if ($zip->open($pptxPath) !== true) {
            throw new Exception('Falha ao abrir PPTX.');
        }

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

    public function sync() {
        $this->requireDeveloper();

        header('Content-Type: text/html; charset=utf-8');

        $db = (new Database())->connect();
        $projectRoot = dirname(__DIR__, 2);
        $harpaDir = $projectRoot . DIRECTORY_SEPARATOR . 'harpa_crista';
        $harpaDirReal = realpath($harpaDir);

        echo "<h1>Harpa Cristã - Conversão e Extração</h1>";

        if (!$harpaDirReal || !is_dir($harpaDirReal)) {
            echo "<p style='color:red'>Diretório harpa_crista não encontrado.</p>";
            return;
        }

        $soffice = $this->findSofficePath();
        echo "<p><strong>LibreOffice:</strong> " . ($soffice ? htmlspecialchars($soffice) : "não encontrado") . "</p>";
        $powerPointOk = $this->canCreatePowerPointCom();
        echo "<p><strong>PowerPoint:</strong> " . ($powerPointOk ? "ok" : "não disponível") . "</p>";

        $rows = $db->query("SELECT hymn_number, file_name, pptx_file_name, lyrics FROM harpa_hymns ORDER BY hymn_number ASC")->fetchAll(PDO::FETCH_ASSOC);
        if (!is_array($rows) || count($rows) === 0) {
            echo "<p>Nenhum hino encontrado na tabela.</p>";
            return;
        }

        $update = $db->prepare("UPDATE harpa_hymns SET pptx_file_name = ?, lyrics = ?, extract_status = ?, extract_error = ?, extracted_at = CURRENT_TIMESTAMP WHERE hymn_number = ?");

        $converted = 0;
        $extracted = 0;
        $failed = 0;

        echo "<ul>";
        foreach ($rows as $r) {
            $num = (int)($r['hymn_number'] ?? 0);
            $fileName = (string)($r['file_name'] ?? '');
            $pptxName = (string)($r['pptx_file_name'] ?? '');
            $lyrics = (string)($r['lyrics'] ?? '');

            if ($num <= 0 || $fileName === '') {
                continue;
            }

            $effectiveName = $pptxName !== '' ? $pptxName : $fileName;
            $ext = strtolower((string)pathinfo($effectiveName, PATHINFO_EXTENSION));

            $pptxToUse = null;
            $pptxFileNameToUse = $pptxName;

            if ($ext === 'pptx') {
                $pptxToUse = $harpaDirReal . DIRECTORY_SEPARATOR . $effectiveName;
                $pptxFileNameToUse = $effectiveName;
            } elseif ($ext === 'ppt') {
                $inputPath = $harpaDirReal . DIRECTORY_SEPARATOR . $fileName;
                $inputReal = realpath($inputPath);
                if (!$inputReal || strpos($inputReal, $harpaDirReal) !== 0 || !is_file($inputReal)) {
                    $update->execute([$pptxName ?: null, $lyrics ?: null, 'missing_file', 'Arquivo .ppt não encontrado.', $num]);
                    echo "<li style='color:red'>Hino {$num}: arquivo .ppt não encontrado.</li>";
                    $failed++;
                    continue;
                }

                $expected = preg_replace('/\\.ppt$/i', '.pptx', $fileName);
                $expectedPath = $harpaDirReal . DIRECTORY_SEPARATOR . $expected;

                if ($soffice) {
                    $res = $this->runSofficeConvert($soffice, $inputReal, $harpaDirReal);
                    if (!$res['ok'] || !file_exists($expectedPath)) {
                        $msg = trim(($res['err'] ?: $res['out']) ?: 'Falha na conversão.');
                        $update->execute([$pptxName ?: null, $lyrics ?: null, 'convert_error', $msg, $num]);
                        echo "<li style='color:red'>Hino {$num}: erro ao converter. " . htmlspecialchars($msg) . "</li>";
                        $failed++;
                        continue;
                    }
                } else {
                    if (!$powerPointOk) {
                        $update->execute([$pptxName ?: null, $lyrics ?: null, 'pending_conversion', 'PowerPoint não disponível para converter .ppt → .pptx.', $num]);
                        echo "<li>Hino {$num}: aguardando conversão (PowerPoint não disponível).</li>";
                        continue;
                    }

                    $res = $this->runPowerPointConvert($inputReal, $expectedPath);
                    if (!$res['ok'] || !file_exists($expectedPath)) {
                        $msg = trim(($res['err'] ?: $res['out']) ?: 'Falha na conversão.');
                        $update->execute([$pptxName ?: null, $lyrics ?: null, 'convert_error', $msg, $num]);
                        echo "<li style='color:red'>Hino {$num}: erro ao converter. " . htmlspecialchars($msg) . "</li>";
                        $failed++;
                        continue;
                    }
                }

                $pptxToUse = $expectedPath;
                $pptxFileNameToUse = $expected;
                $converted++;
            } else {
                $update->execute([$pptxName ?: null, $lyrics ?: null, 'unsupported', 'Extensão não suportada.', $num]);
                echo "<li style='color:red'>Hino {$num}: extensão não suportada.</li>";
                $failed++;
                continue;
            }

            $pptxReal = realpath($pptxToUse);
            if (!$pptxReal || strpos($pptxReal, $harpaDirReal) !== 0 || !is_file($pptxReal)) {
                $update->execute([$pptxFileNameToUse ?: null, $lyrics ?: null, 'missing_file', 'Arquivo PPTX não encontrado.', $num]);
                echo "<li style='color:red'>Hino {$num}: PPTX não encontrado.</li>";
                $failed++;
                continue;
            }

            if (trim($lyrics) !== '') {
                $update->execute([$pptxFileNameToUse ?: null, $lyrics, 'ok', null, $num]);
                echo "<li>Hino {$num}: já tinha letra.</li>";
                continue;
            }

            try {
                $text = $this->extractTextFromPptx($pptxReal);
                if (trim($text) === '') {
                    $update->execute([$pptxFileNameToUse ?: null, null, 'empty', 'Nenhum texto encontrado no PPTX.', $num]);
                    echo "<li>Hino {$num}: sem texto no PPTX.</li>";
                    continue;
                }
                $update->execute([$pptxFileNameToUse ?: null, $text, 'ok', null, $num]);
                echo "<li>Hino {$num}: letra extraída.</li>";
                $extracted++;
            } catch (Throwable $e) {
                $update->execute([$pptxFileNameToUse ?: null, null, 'extract_error', $e->getMessage(), $num]);
                echo "<li style='color:red'>Hino {$num}: erro ao extrair. " . htmlspecialchars($e->getMessage()) . "</li>";
                $failed++;
            }
        }
        echo "</ul>";

        echo "<p><strong>Convertidos:</strong> {$converted} | <strong>Extraídos:</strong> {$extracted} | <strong>Falhas:</strong> {$failed}</p>";
        echo "<p><a href=\"/harpa\" style=\"display:inline-block;margin-top:10px\">Voltar para Harpa</a></p>";
    }
}
