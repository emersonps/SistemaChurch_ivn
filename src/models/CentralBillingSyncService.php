<?php

class CentralBillingSyncService {
    private $db;
    private $connector;

    public function __construct() {
        $this->db = (new Database())->connect();
        $this->connector = new CentralManualSyncService();
    }

    public function hasRemoteConfig() {
        return $this->connector->hasRemoteConfig();
    }

    public function getConnectionConfig() {
        return $this->connector->getConnectionConfig();
    }

    public function fetchRemoteStatus() {
        $config = $this->getConnectionConfig();
        $this->validateConfig($config);

        return [
            'version' => $this->requestJson($config['central_url'] . '/api/v1/billings/version', $config),
        ];
    }

    public function syncPayments() {
        $config = $this->getConnectionConfig();
        $this->validateConfig($config);

        $payments = $this->getLocalPayments();
        $response = $this->postJson($config['central_url'] . '/api/v1/billings/import', $config, [
            'payments' => $payments
        ]);

        $now = date('Y-m-d H:i:s');
        $this->saveSetting('central_billing_sync_last_at', $now);
        $this->saveSetting('central_billing_sync_last_count', (string)count($payments));

        return [
            'message' => $response['message'] ?? 'Cobranças sincronizadas com a central.',
            'count' => (int)($response['count'] ?? count($payments)),
            'synced_at' => $now
        ];
    }

    public function syncFromCentral($force = false) {
        $config = $this->getConnectionConfig();
        $this->validateConfig($config);

        $version = $this->requestJson($config['central_url'] . '/api/v1/billings/version', $config);
        $export = $this->requestJson($config['central_url'] . '/api/v1/billings/export', $config);

        $remoteVersion = (string)($version['version'] ?? '');
        $remoteChecksum = (string)($version['checksum'] ?? '');
        if ($remoteVersion === '') {
            throw new RuntimeException('A central não retornou uma versão válida para cobranças.');
        }

        if (!$force
            && $this->getSetting('central_billing_sync_pull_version', '') === $remoteVersion
            && $this->getSetting('central_billing_sync_pull_checksum', '') === $remoteChecksum) {
            return [
                'message' => 'As cobranças locais já estão atualizadas com a central.',
                'updated' => false,
                'version' => $remoteVersion,
                'checksum' => $remoteChecksum
            ];
        }

        $payload = $export['payload']['payments'] ?? null;
        if (!is_array($payload)) {
            throw new RuntimeException('A central retornou um payload inválido para cobranças.');
        }

        $this->replaceLocalPayments($payload);

        $now = date('Y-m-d H:i:s');
        $this->saveSetting('central_billing_sync_pull_at', $now);
        $this->saveSetting('central_billing_sync_pull_version', $remoteVersion);
        $this->saveSetting('central_billing_sync_pull_checksum', $remoteChecksum);

        return [
            'message' => 'Cobranças atualizadas a partir da central.',
            'updated' => true,
            'version' => $remoteVersion,
            'checksum' => $remoteChecksum,
            'count' => count($payload)
        ];
    }

    private function getLocalPayments() {
        $hasDueDateColumn = $this->tableHasColumn('system_payments', 'due_date');

        if ($hasDueDateColumn) {
            $payments = $this->db->query("SELECT reference_month, status, amount, due_date, payment_date FROM system_payments ORDER BY reference_month ASC")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $payments = $this->db->query("SELECT reference_month, status, amount, payment_date FROM system_payments ORDER BY reference_month ASC")->fetchAll(PDO::FETCH_ASSOC);
        }

        $payload = [];
        foreach ($payments as $payment) {
            $payload[] = [
                'reference_month' => trim((string)($payment['reference_month'] ?? '')),
                'status' => trim((string)($payment['status'] ?? 'pending')),
                'amount' => (float)($payment['amount'] ?? 59.99),
                'due_date' => !empty($payment['due_date']) ? date('Y-m-d H:i:s', strtotime($payment['due_date'])) : null,
                'payment_date' => !empty($payment['payment_date']) ? date('Y-m-d H:i:s', strtotime($payment['payment_date'])) : null
            ];
        }

        return $payload;
    }

    private function replaceLocalPayments(array $payments) {
        $hasDueDateColumn = $this->tableHasColumn('system_payments', 'due_date');

        $this->db->beginTransaction();
        try {
            $this->db->exec("DELETE FROM system_payments");

            if ($hasDueDateColumn) {
                $stmt = $this->db->prepare("INSERT INTO system_payments (reference_month, status, amount, due_date, payment_date) VALUES (?, ?, ?, ?, ?)");
                foreach ($payments as $payment) {
                    $stmt->execute([
                        trim((string)($payment['reference_month'] ?? '')),
                        trim((string)($payment['status'] ?? 'pending')),
                        (float)($payment['amount'] ?? 59.99),
                        $this->normalizeNullableDateTime($payment['due_date'] ?? null, trim((string)($payment['reference_month'] ?? '')) . '-05 00:00:00'),
                        $this->normalizeNullableDateTime($payment['payment_date'] ?? null)
                    ]);
                }
            } else {
                $stmt = $this->db->prepare("INSERT INTO system_payments (reference_month, status, amount, payment_date) VALUES (?, ?, ?, ?)");
                foreach ($payments as $payment) {
                    $legacyDate = trim((string)($payment['status'] ?? 'pending')) === 'paid'
                        ? $this->normalizeNullableDateTime($payment['payment_date'] ?? null)
                        : $this->normalizeNullableDateTime($payment['due_date'] ?? null, trim((string)($payment['reference_month'] ?? '')) . '-05 00:00:00');
                    $stmt->execute([
                        trim((string)($payment['reference_month'] ?? '')),
                        trim((string)($payment['status'] ?? 'pending')),
                        (float)($payment['amount'] ?? 59.99),
                        $legacyDate
                    ]);
                }
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

            return $this->decodeJsonResponse($raw, $status, 'consultar cobranças');
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

        return $this->decodeJsonResponse($raw, $status, 'consultar cobranças');
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

            return $this->decodeJsonResponse($raw, $status, 'enviar cobranças');
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

        return $this->decodeJsonResponse($raw, $status, 'enviar cobranças');
    }

    private function decodeJsonResponse($raw, $status, $actionLabel) {
        $raw = preg_replace('/^\xEF\xBB\xBF+/', '', (string)$raw);
        $raw = trim($raw);
        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('A central retornou uma resposta inválida ao ' . $actionLabel . '.');
        }

        if ($status >= 400) {
            throw new RuntimeException($decoded['error'] ?? ('A central retornou um erro ao ' . $actionLabel . '.'));
        }

        return $decoded;
    }

    private function normalizeNullableDateTime($value, $default = null) {
        $value = trim((string)$value);
        if ($value === '') {
            return $default;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return $default;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function tableHasColumn($table, $column) {
        try {
            $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $stmt = $this->db->query("PRAGMA table_info($table)");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (($row['name'] ?? '') === $column) {
                        return true;
                    }
                }
                return false;
            }

            $stmt = $this->db->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
            $stmt->execute([$column]);
            return (bool)$stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }

    private function saveSetting($key, $value) {
        $stmt = $this->db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$value, $key]);
        if ($stmt->rowCount() > 0) {
            return;
        }

        $insert = $this->db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $insert->execute([$key, $value]);
    }

    private function getSetting($key, $default = '') {
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    }

    private function validateConfig(array $config) {
        if (empty($config['central_url']) || empty($config['instance_code']) || empty($config['token'])) {
            throw new RuntimeException('Configure a URL, o código da instância e o token da central antes de sincronizar as cobranças.');
        }
    }
}
