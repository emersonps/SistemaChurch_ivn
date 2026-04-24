<?php

class WhiteLabelService {
    public function getCurrentBranding() {
        $name = getSystemSetting('church_name', '');
        $alias = getSystemSetting('church_alias', '');

        if ($name !== '' && $alias !== '') {
            return [
                'name' => $name,
                'alias' => $alias
            ];
        }

        $headerPath = __DIR__ . '/../views/layout/header.php';
        $currentAlias = 'IVN';
        $currentName = 'Igreja Vida Nova';

        if (file_exists($headerPath)) {
            $content = file_get_contents($headerPath);
            if (preg_match('/<title>(.*?)<\/title>/', $content, $matches)) {
                $title = strip_tags($matches[1]);
                $parts = explode(' - ', $title);
                if (count($parts) >= 2) {
                    $currentAlias = trim($parts[0]);
                    $currentName = trim($parts[1]);
                } elseif (trim($title) !== '') {
                    $currentName = trim($title);
                }
            }
        }

        return [
            'name' => $currentName,
            'alias' => $currentAlias
        ];
    }

    public function saveBrandingSettings(PDO $db, $alias, $name, $logoUrl = null) {
        $this->saveSystemSetting($db, 'church_alias', $alias);
        $this->saveSystemSetting($db, 'church_name', $name);
        if ($logoUrl !== null) {
            $this->saveSystemSetting($db, 'church_logo_url', $logoUrl);
        }
    }

    public function applyBranding($newAlias, $newName) {
        $current = $this->getCurrentBranding();
        $currentAlias = $current['alias'];
        $currentName = $current['name'];

        if ($currentAlias === $newAlias && $currentName === $newName) {
            return;
        }

        $directories = [
            __DIR__ . '/../',
            __DIR__ . '/../../public',
        ];
        $extensions = ['php', 'json', 'html'];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if (!$file->isFile() || !in_array($file->getExtension(), $extensions, true)) {
                    continue;
                }

                $path = $file->getPathname();
                $content = file_get_contents($path);
                $originalContent = $content;

                if ($currentName !== $newName) {
                    $content = str_replace($currentName, $newName, $content);
                }

                if ($currentAlias !== $newAlias) {
                    $content = preg_replace('/\b' . preg_quote($currentAlias, '/') . '\b(?!(?:_MEMBER|_logo))/', $newAlias, $content);
                    $content = preg_replace('/\b' . preg_quote(strtolower($currentAlias), '/') . '\b(?!(?:_member))/', strtolower($newAlias), $content);
                    $content = str_ireplace('contato@' . $currentAlias, 'contato@' . strtolower($newAlias), $content);
                }

                if ($content !== $originalContent) {
                    file_put_contents($path, $content);
                }
            }
        }
    }

    public function saveSystemSetting(PDO $db, $key, $value) {
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        try {
            $stmt->execute([$key, $value]);
            return;
        } catch (PDOException $e) {
        }

        $checkStmt = $db->prepare("SELECT id FROM settings WHERE setting_key = ?");
        $checkStmt->execute([$key]);
        $exists = $checkStmt->fetchColumn();

        if ($exists) {
            $updateStmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $updateStmt->execute([$value, $key]);
            return;
        }

        $insertStmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $insertStmt->execute([$key, $value]);
    }
}
