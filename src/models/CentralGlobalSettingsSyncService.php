<?php

class CentralGlobalSettingsSyncService {
    private $db;
    private $connector;

    public function __construct() {
        $this->db = (new Database())->connect();
        $this->connector = new CentralManualSyncService();
    }

    public function getConfig() {
        return [
            'enabled' => $this->getSetting('central_global_settings_sync_enabled', '0') === '1',
            'last_sync_at' => $this->getSetting('central_global_settings_sync_last_at', ''),
            'last_sync_version' => $this->getSetting('central_global_settings_sync_last_version', ''),
            'last_sync_checksum' => $this->getSetting('central_global_settings_sync_last_checksum', ''),
        ];
    }

    public function isEnabled() {
        return $this->getConfig()['enabled'];
    }

    public function saveConfig(array $data) {
        $enabled = !empty($data['enabled']) ? '1' : '0';
        $this->saveSetting('central_global_settings_sync_enabled', $enabled);
        return $this->getConfig();
    }

    public function fetchRemoteStatus() {
        return $this->connector->fetchModuleVersion('global-settings');
    }

    public function syncSettings() {
        $config = $this->getConfig();
        $version = $this->connector->fetchModuleVersion('global-settings');
        $export = $this->connector->fetchModuleExport('global-settings');

        $remoteVersion = (string)($version['version'] ?? '');
        $remoteChecksum = (string)($version['checksum'] ?? '');
        if ($remoteVersion === '') {
            throw new RuntimeException('A central não retornou uma versão válida para o módulo de configurações globais.');
        }

        if (($config['last_sync_version'] ?? '') === $remoteVersion && ($config['last_sync_checksum'] ?? '') === $remoteChecksum) {
            return [
                'message' => 'As configurações globais já estão atualizadas com a última versão publicada.',
                'updated' => false,
                'version' => $remoteVersion,
                'checksum' => $remoteChecksum
            ];
        }

        $payload = $export['payload']['settings'] ?? null;
        if (!is_array($payload)) {
            throw new RuntimeException('A central retornou um payload inválido para configurações globais.');
        }

        $whiteLabelService = new WhiteLabelService();
        $brandingChanged = false;
        $newName = trim((string)($payload['church_name'] ?? ''));
        $newAlias = trim((string)($payload['church_alias'] ?? ''));
        $logoUrl = $this->resolveRemoteAssetUrl($payload['church_logo_url'] ?? '');

        if ($logoUrl !== '') {
            $payload['church_logo_url'] = $logoUrl;
        }

        foreach ($payload as $key => $value) {
            $this->saveSetting($key, (string)$value);
        }

        if ($newName !== '' && $newAlias !== '') {
            $whiteLabelService->saveBrandingSettings($this->db, $newAlias, $newName, $logoUrl !== '' ? $logoUrl : null);
            $whiteLabelService->applyBranding($newAlias, $newName);
            $brandingChanged = true;
        } elseif ($logoUrl !== '') {
            $whiteLabelService->saveBrandingSettings($this->db, $this->getSetting('church_alias', 'IVN'), $this->getSetting('church_name', 'Igreja Vida Nova'), $logoUrl);
        }

        $now = date('Y-m-d H:i:s');
        $this->saveSetting('central_global_settings_sync_last_at', $now);
        $this->saveSetting('central_global_settings_sync_last_version', $remoteVersion);
        $this->saveSetting('central_global_settings_sync_last_checksum', $remoteChecksum);

        return [
            'message' => 'Configurações globais sincronizadas com sucesso.',
            'updated' => true,
            'version' => $remoteVersion,
            'checksum' => $remoteChecksum,
            'keys' => count($payload),
            'branding_changed' => $brandingChanged
        ];
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

    private function resolveRemoteAssetUrl($url) {
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        $connectionConfig = $this->connector->getConnectionConfig();
        $centralUrl = rtrim((string)($connectionConfig['central_url'] ?? ''), '/');
        if ($centralUrl === '') {
            return $url;
        }

        return $centralUrl . '/' . ltrim($url, '/');
    }
}
