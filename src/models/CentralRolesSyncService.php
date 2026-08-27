<?php

class CentralRolesSyncService {
    private $db;
    private $connector;

    public function __construct() {
        $this->db = (new Database())->connect();
        $this->connector = new CentralManualSyncService();
    }

    public function getConfig() {
        return [
            'enabled' => $this->getSetting('central_roles_sync_enabled', '0') === '1',
            'last_sync_at' => $this->getSetting('central_roles_sync_last_at', ''),
            'last_sync_version' => $this->getSetting('central_roles_sync_last_version', ''),
            'last_sync_checksum' => $this->getSetting('central_roles_sync_last_checksum', ''),
        ];
    }

    public function isEnabled() {
        return $this->getConfig()['enabled'];
    }

    public function saveConfig(array $data) {
        $enabled = !empty($data['enabled']) ? '1' : '0';
        $this->saveSetting('central_roles_sync_enabled', $enabled);
        return $this->getConfig();
    }

    public function fetchRemoteStatus() {
        return $this->connector->fetchModuleVersion('roles');
    }

    public function syncRoles() {
        $config = $this->getConfig();
        $version = $this->connector->fetchModuleVersion('roles');
        $export = $this->connector->fetchModuleExport('roles');

        $remoteVersion = (string)($version['version'] ?? '');
        $remoteChecksum = (string)($version['checksum'] ?? '');
        if ($remoteVersion === '') {
            throw new RuntimeException('A central não retornou uma versão válida para o módulo de papéis.');
        }

        if (($config['last_sync_version'] ?? '') === $remoteVersion && ($config['last_sync_checksum'] ?? '') === $remoteChecksum) {
            return [
                'message' => 'Os papéis já estão atualizados com a última versão publicada.',
                'updated' => false,
                'version' => $remoteVersion,
                'checksum' => $remoteChecksum
            ];
        }

        $roles = $export['payload']['roles'] ?? null;
        if (!is_array($roles) || empty($roles)) {
            throw new RuntimeException('A central retornou um payload inválido para o módulo de papéis.');
        }

        $this->writeRbacFile($roles);

        $now = date('Y-m-d H:i:s');
        $this->saveSetting('central_roles_sync_last_at', $now);
        $this->saveSetting('central_roles_sync_last_version', $remoteVersion);
        $this->saveSetting('central_roles_sync_last_checksum', $remoteChecksum);

        return [
            'message' => 'Papéis e permissões sincronizados com sucesso.',
            'updated' => true,
            'version' => $remoteVersion,
            'checksum' => $remoteChecksum,
            'roles' => count($roles)
        ];
    }

    // Regrava config/rbac.php mantendo o mesmo formato que updateRole() já
    // produzia manualmente — o restante do app (getPermissionMenuDefinitions,
    // hasPermission, etc.) continua lendo esse arquivo normalmente, sem saber
    // se as permissões vieram de uma edição local antiga ou de uma sincronização.
    private function writeRbacFile(array $roles) {
        $rbacFile = __DIR__ . '/../../config/rbac.php';
        $existing = file_exists($rbacFile) ? (require $rbacFile) : [];
        $existing['roles'] = $roles;

        $content = "<?php\n// config/rbac.php\n\nreturn " . var_export($existing, true) . ";\n";
        file_put_contents($rbacFile, $content);
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
