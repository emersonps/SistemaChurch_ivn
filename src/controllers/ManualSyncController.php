<?php

class ManualSyncController {
    private function requireDeveloper() {
        if (!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'developer')) {
            redirect('/admin/dashboard');
        }
    }

    public function index() {
        $this->requireDeveloper();

        $manualService = new CentralManualSyncService();
        $settingsService = new CentralGlobalSettingsSyncService();
        $rolesService = new CentralRolesSyncService();
        $config = $manualService->getConfig();
        $manualRemoteStatus = null;
        $globalSettingsRemoteStatus = null;
        $rolesRemoteStatus = null;

        if ($manualService->hasRemoteConfig()) {
            try {
                $manualRemoteStatus = $manualService->fetchRemoteStatus();
            } catch (Exception $e) {
                $manualRemoteStatus = ['error' => $e->getMessage()];
            }
            try {
                $globalSettingsRemoteStatus = $settingsService->fetchRemoteStatus();
            } catch (Exception $e) {
                $globalSettingsRemoteStatus = ['error' => $e->getMessage()];
            }
            try {
                $rolesRemoteStatus = $rolesService->fetchRemoteStatus();
            } catch (Exception $e) {
                $rolesRemoteStatus = ['error' => $e->getMessage()];
            }
        }

        $db = (new Database())->connect();
        $localVideoCount = (int)$db->query("SELECT COUNT(*) FROM manual_videos")->fetchColumn();
        $localThemeCount = (int)$db->query("SELECT COUNT(DISTINCT theme) FROM manual_videos")->fetchColumn();

        $rbac = require __DIR__ . '/../../config/rbac.php';
        $localRolesCount = count($rbac['roles'] ?? []);

        view('developer/manual_sync', [
            'config' => $config,
            'globalSettingsConfig' => $settingsService->getConfig(),
            'rolesConfig' => $rolesService->getConfig(),
            'manualRemoteStatus' => $manualRemoteStatus,
            'globalSettingsRemoteStatus' => $globalSettingsRemoteStatus,
            'rolesRemoteStatus' => $rolesRemoteStatus,
            'localVideoCount' => $localVideoCount,
            'localThemeCount' => $localThemeCount,
            'localRolesCount' => $localRolesCount,
            'siteProfile' => getChurchSiteProfileSettings()
        ]);
    }

    public function save() {
        $this->requireDeveloper();
        verify_csrf();

        $service = new CentralManualSyncService();
        $service->saveConfig([
            'enabled' => isset($_POST['manual_sync_enabled']) ? '1' : '0',
            'central_url' => $_POST['central_url'] ?? '',
            'instance_code' => $_POST['instance_code'] ?? '',
            'token' => $_POST['token'] ?? ''
        ]);
        (new CentralGlobalSettingsSyncService())->saveConfig([
            'enabled' => isset($_POST['global_settings_sync_enabled']) ? '1' : '0'
        ]);
        (new CentralRolesSyncService())->saveConfig([
            'enabled' => isset($_POST['roles_sync_enabled']) ? '1' : '0'
        ]);

        $_SESSION['success'] = 'Configuração da central salva com sucesso.';
        redirect('/developer/manual-sync');
    }

    public function sync() {
        $this->requireDeveloper();
        verify_csrf();

        try {
            $result = (new CentralManualSyncService())->syncManuals();
            $_SESSION['success'] = $result['message'];
            if (!empty($result['updated'])) {
                $_SESSION['success'] .= ' Versão importada: ' . $result['version'] . '.';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/developer/manual-sync');
    }

    public function syncGlobalSettings() {
        $this->requireDeveloper();
        verify_csrf();

        try {
            $result = (new CentralGlobalSettingsSyncService())->syncSettings();
            $_SESSION['success'] = $result['message'];
            if (!empty($result['updated'])) {
                $_SESSION['success'] .= ' Versão importada: ' . $result['version'] . '.';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/developer/manual-sync');
    }

    public function syncRoles() {
        $this->requireDeveloper();
        verify_csrf();

        try {
            $result = (new CentralRolesSyncService())->syncRoles();
            $_SESSION['success'] = $result['message'];
            if (!empty($result['updated'])) {
                $_SESSION['success'] .= ' Versão importada: ' . $result['version'] . '.';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/developer/manual-sync');
    }
}
