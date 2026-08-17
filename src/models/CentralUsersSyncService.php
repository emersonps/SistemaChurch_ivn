<?php

class CentralUsersSyncService {
    private $db;
    private $connector;

    public function __construct() {
        $this->db = (new Database())->connect();
        $this->connector = new CentralManualSyncService();
    }

    public function hasRemoteConfig() {
        return $this->connector->hasRemoteConfig();
    }

    public function getConfiguredInstanceCode() {
        return $this->connector->getConnectionConfig()['instance_code'] ?? '';
    }

    // Throttled combined push+pull, safe to call on every admin page load.
    // Never throws — sync failures must never block page rendering.
    public function syncSilently() {
        $lastRun = (int)strtotime((string)$this->getSetting('central_users_sync_last_at', ''));
        if ($lastRun > 0 && (time() - $lastRun) < 15) {
            return;
        }

        $this->runSync();
        $this->saveSetting('central_users_sync_last_at', date('Y-m-d H:i:s'));
    }

    // Unthrottled pull+push, used when Central nudges this instance right
    // after queuing a credential change so it applies immediately instead
    // of waiting for the next admin page load.
    public function forceSyncNow() {
        $result = $this->runSync();
        $this->saveSetting('central_users_sync_last_at', date('Y-m-d H:i:s'));
        return $result;
    }

    private function runSync() {
        try {
            $pullResult = $this->pullPendingChanges();
            $this->pushUsers();
            return $pullResult;
        } catch (Throwable $e) {
            return ['applied' => 0];
        }
    }

    public function pushUsers() {
        $config = $this->getConnectionConfig();
        $this->validateConfig($config);

        $users = $this->getLocalUsers();
        $response = $this->postJson($config['central_url'] . '/api/v1/users/import', $config, [
            'users' => $users
        ]);

        return [
            'message' => $response['message'] ?? 'Usuários sincronizados com a central.',
            'count' => (int)($response['count'] ?? count($users))
        ];
    }

    public function pullPendingChanges() {
        $config = $this->getConnectionConfig();
        $this->validateConfig($config);

        $response = $this->postJson($config['central_url'] . '/api/v1/users/pull-pending-changes', $config, []);
        $changes = $response['changes'] ?? [];
        if (!is_array($changes) || empty($changes)) {
            return ['applied' => 0];
        }

        $applied = 0;
        foreach ($changes as $change) {
            $remoteUserId = (int)($change['remote_user_id'] ?? 0);
            $newUsername = isset($change['new_username']) ? trim((string)$change['new_username']) : '';
            $newPasswordHash = isset($change['new_password_hash']) ? (string)$change['new_password_hash'] : '';

            if ($remoteUserId <= 0 || ($newUsername === '' && $newPasswordHash === '')) {
                continue;
            }

            try {
                if ($newUsername !== '' && $newPasswordHash !== '') {
                    $stmt = $this->db->prepare('UPDATE users SET username = ?, password = ? WHERE id = ?');
                    $stmt->execute([$newUsername, $newPasswordHash, $remoteUserId]);
                } elseif ($newUsername !== '') {
                    $stmt = $this->db->prepare('UPDATE users SET username = ? WHERE id = ?');
                    $stmt->execute([$newUsername, $remoteUserId]);
                } else {
                    $stmt = $this->db->prepare('UPDATE users SET password = ? WHERE id = ?');
                    $stmt->execute([$newPasswordHash, $remoteUserId]);
                }
                $applied++;
            } catch (Exception $e) {
                // Skip this row (e.g. duplicate username) without aborting the rest of the batch.
            }
        }

        return ['applied' => $applied];
    }

    private function getLocalUsers() {
        $stmt = $this->db->query('SELECT id, username, role, congregation_id, created_at FROM users ORDER BY id ASC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $payload = [];
        foreach ($rows as $row) {
            $payload[] = [
                'remote_user_id' => (int)$row['id'],
                'username' => trim((string)($row['username'] ?? '')),
                'role' => trim((string)($row['role'] ?? '')),
                'congregation_id' => !empty($row['congregation_id']) ? (int)$row['congregation_id'] : null,
                'created_at' => !empty($row['created_at']) ? date('Y-m-d H:i:s', strtotime($row['created_at'])) : null
            ];
        }

        return $payload;
    }

    private function getConnectionConfig() {
        return $this->connector->getConnectionConfig();
    }

    private function postJson($url, array $config, array $payload) {
        $headers = [
            'X-Instance-Code: ' . $config['instance_code'],
            'X-Sync-Token: ' . $config['token'],
            'Accept: application/json',
            'Content-Type: application/json'
        ];
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
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
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $body,
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
        $raw = preg_replace('/^\xEF\xBB\xBF+/', '', (string)$raw);
        $raw = trim($raw);
        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('A central retornou uma resposta inválida ao sincronizar usuários.');
        }

        if ($status >= 400) {
            throw new RuntimeException($decoded['error'] ?? 'A central retornou um erro ao sincronizar usuários.');
        }

        return $decoded;
    }

    private function validateConfig(array $config) {
        if (empty($config['central_url']) || empty($config['instance_code']) || empty($config['token'])) {
            throw new RuntimeException('Configure a URL, o código da instância e o token da central antes de sincronizar usuários.');
        }
    }

    private function saveSetting($key, $value) {
        $stmt = $this->db->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?');
        $stmt->execute([$value, $key]);
        if ($stmt->rowCount() > 0) {
            return;
        }

        $insert = $this->db->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)');
        $insert->execute([$key, $value]);
    }

    private function getSetting($key, $default = '') {
        $stmt = $this->db->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    }
}
