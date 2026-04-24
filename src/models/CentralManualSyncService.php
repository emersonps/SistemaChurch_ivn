<?php

class CentralManualSyncService {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getConfig() {
        return [
            'enabled' => $this->getSetting('central_manual_sync_enabled', '0') === '1',
            'central_url' => rtrim($this->getSetting('central_manual_sync_url', ''), '/'),
            'instance_code' => $this->getSetting('central_manual_sync_instance_code', ''),
            'token' => $this->getSetting('central_manual_sync_token', ''),
            'last_sync_at' => $this->getSetting('central_manual_sync_last_at', ''),
            'last_sync_version' => $this->getSetting('central_manual_sync_last_version', ''),
            'last_sync_checksum' => $this->getSetting('central_manual_sync_last_checksum', ''),
        ];
    }

    public function saveConfig(array $data) {
        $enabled = !empty($data['enabled']) ? '1' : '0';
        $centralUrl = rtrim(trim((string)($data['central_url'] ?? '')), '/');
        $instanceCode = trim((string)($data['instance_code'] ?? ''));
        $token = trim((string)($data['token'] ?? ''));

        $this->saveSetting('central_manual_sync_enabled', $enabled);
        $this->saveSetting('central_manual_sync_url', $centralUrl);
        $this->saveSetting('central_manual_sync_instance_code', $instanceCode);
        $this->saveSetting('central_manual_sync_token', $token);

        return $this->getConfig();
    }

    public function isEnabled() {
        return $this->getConfig()['enabled'];
    }

    public function hasRemoteConfig() {
        $config = $this->getConfig();
        return $config['central_url'] !== '' && $config['instance_code'] !== '' && $config['token'] !== '';
    }

    public function getConnectionConfig() {
        $config = $this->getConfig();
        return [
            'central_url' => $config['central_url'],
            'instance_code' => $config['instance_code'],
            'token' => $config['token']
        ];
    }

    public function fetchPing() {
        $config = $this->getConfig();
        $this->validateConfig($config);
        return $this->requestJson($config['central_url'] . '/api/v1/ping', $config);
    }

    public function fetchModuleVersion($routeSegment) {
        $config = $this->getConfig();
        $this->validateConfig($config);
        return $this->requestJson($config['central_url'] . '/api/v1/' . trim($routeSegment, '/') . '/version', $config);
    }

    public function fetchModuleExport($routeSegment) {
        $config = $this->getConfig();
        $this->validateConfig($config);
        return $this->requestJson($config['central_url'] . '/api/v1/' . trim($routeSegment, '/') . '/export', $config);
    }

    public function fetchRemoteStatus() {
        $ping = $this->fetchPing();
        $version = $this->fetchModuleVersion('manuals');

        return [
            'ping' => $ping,
            'version' => $version,
        ];
    }

    public function syncManuals() {
        $config = $this->getConfig();
        $this->validateConfig($config);

        $version = $this->fetchModuleVersion('manuals');
        $export = $this->fetchModuleExport('manuals');

        $remoteVersion = (string)($version['version'] ?? '');
        $remoteChecksum = (string)($version['checksum'] ?? '');
        if ($remoteVersion === '') {
            throw new RuntimeException('A central não retornou uma versão válida para o módulo de manuais.');
        }

        if (($config['last_sync_version'] ?? '') === $remoteVersion && ($config['last_sync_checksum'] ?? '') === $remoteChecksum) {
            return [
                'message' => 'Os manuais já estão atualizados com a última versão publicada.',
                'updated' => false,
                'version' => $remoteVersion,
                'checksum' => $remoteChecksum
            ];
        }

        $payload = $export['payload'] ?? null;
        if (!is_array($payload)) {
            throw new RuntimeException('A central retornou um payload inválido para o módulo de manuais.');
        }

        $this->replaceLocalManuals($payload);

        $now = date('Y-m-d H:i:s');
        $this->saveSetting('central_manual_sync_last_at', $now);
        $this->saveSetting('central_manual_sync_last_version', $remoteVersion);
        $this->saveSetting('central_manual_sync_last_checksum', $remoteChecksum);

        return [
            'message' => 'Manuais sincronizados com sucesso.',
            'updated' => true,
            'version' => $remoteVersion,
            'checksum' => $remoteChecksum,
            'videos' => count($payload['manual_videos'] ?? []),
            'targets' => count($payload['manual_video_targets'] ?? [])
        ];
    }

    private function replaceLocalManuals(array $payload) {
        $videos = $payload['manual_videos'] ?? [];
        $targets = $payload['manual_video_targets'] ?? [];

        $this->db->beginTransaction();
        try {
            $this->db->exec("DELETE FROM manual_video_targets");
            $this->db->exec("DELETE FROM manual_videos");

            $stmtVideo = $this->db->prepare("
                INSERT INTO manual_videos (id, title, theme, description, youtube_url, youtube_video_id, sort_order, is_active, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            foreach ($videos as $video) {
                $stmtVideo->execute([
                    $video['id'] ?? null,
                    $video['title'] ?? '',
                    $video['theme'] ?? 'Geral',
                    $video['description'] ?? null,
                    $video['youtube_url'] ?? '',
                    $video['youtube_video_id'] ?? '',
                    (int)($video['sort_order'] ?? 0),
                    (int)($video['is_active'] ?? 1),
                    $video['created_at'] ?? date('Y-m-d H:i:s'),
                    $video['updated_at'] ?? date('Y-m-d H:i:s')
                ]);
            }

            $stmtTarget = $this->db->prepare("
                INSERT INTO manual_video_targets (id, manual_video_id, target_type, target_key, created_at)
                VALUES (?, ?, ?, ?, ?)
            ");
            foreach ($targets as $target) {
                $stmtTarget->execute([
                    $target['id'] ?? null,
                    $target['manual_video_id'] ?? null,
                    $target['target_type'] ?? '',
                    $target['target_key'] ?? null,
                    $target['created_at'] ?? date('Y-m-d H:i:s')
                ]);
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function requestJson($url, array $config) {
        $headers = [
            'X-Instance-Code: ' . $config['instance_code'],
            'X-Sync-Token: ' . $config['token'],
            'Accept: application/json'
        ];

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $raw = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($raw === false) {
                throw new RuntimeException($error !== '' ? $error : 'Falha ao conectar com a central.');
            }

            return $this->decodeJsonResponse($raw, $status);
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers),
                'timeout' => 20
            ]
        ]);
        $raw = @file_get_contents($url, false, $context);
        $status = 500;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
            $status = (int)$matches[1];
        }
        if ($raw === false) {
            throw new RuntimeException('Falha ao conectar com a central.');
        }

        return $this->decodeJsonResponse($raw, $status);
    }

    private function decodeJsonResponse($raw, $status) {
        $raw = $this->normalizeJsonPayload((string)$raw);

        $decoded = json_decode($raw, true);
        if (is_string($decoded)) {
            $decoded = json_decode($this->normalizeJsonPayload($decoded), true);
        }

        if (!is_array($decoded)) {
            throw new RuntimeException('A central retornou uma resposta inválida.');
        }

        if ($status >= 400) {
            throw new RuntimeException($decoded['error'] ?? 'A central retornou um erro ao processar a requisição.');
        }

        return $decoded;
    }

    private function normalizeJsonPayload($raw) {
        $raw = (string)$raw;
        $raw = preg_replace('/^\xEF\xBB\xBF+/', '', $raw);
        $raw = trim($raw);

        $jsonStartObject = strpos($raw, '{');
        $jsonStartArray = strpos($raw, '[');
        $positions = array_values(array_filter([$jsonStartObject, $jsonStartArray], function ($value) {
            return $value !== false;
        }));

        if (!empty($positions)) {
            $start = min($positions);
            if ($start > 0) {
                $raw = substr($raw, $start);
            }
        }

        $raw = trim($raw);
        if (strlen($raw) >= 2 && (($raw[0] === '"' && substr($raw, -1) === '"') || ($raw[0] === "'" && substr($raw, -1) === "'"))) {
            $unwrapped = substr($raw, 1, -1);
            $unwrapped = stripcslashes($unwrapped);
            if ($unwrapped !== '') {
                $raw = $unwrapped;
            }
        }

        $raw = preg_replace('/^\xEF\xBB\xBF+/', '', $raw);
        return trim($raw);
    }

    private function validateConfig(array $config) {
        if ($config['central_url'] === '' || $config['instance_code'] === '' || $config['token'] === '') {
            throw new RuntimeException('Preencha URL da central, código da instância e token antes de sincronizar.');
        }
    }

    private function getSetting($key, $default = '') {
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    }

    private function saveSetting($key, $value) {
        $stmt = $this->db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$value, $key]);
        if ($stmt->rowCount() > 0) {
            return;
        }

        $insert = $this->db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        try {
            $insert->execute([$key, $value]);
        } catch (Exception $e) {
            $stmt->execute([$value, $key]);
        }
    }
}
