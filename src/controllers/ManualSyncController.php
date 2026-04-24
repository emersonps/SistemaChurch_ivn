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
        $config = $manualService->getConfig();
        $manualRemoteStatus = null;
        $globalSettingsRemoteStatus = null;

        if ($manualService->hasRemoteConfig()) {
            try {
                $manualRemoteStatus = $manualService->fetchRemoteStatus();
                $globalSettingsRemoteStatus = $settingsService->fetchRemoteStatus();
            } catch (Exception $e) {
                $manualRemoteStatus = ['error' => $e->getMessage()];
                $globalSettingsRemoteStatus = ['error' => $e->getMessage()];
            }
        }

        $db = (new Database())->connect();
        $localVideoCount = (int)$db->query("SELECT COUNT(*) FROM manual_videos")->fetchColumn();
        $localThemeCount = (int)$db->query("SELECT COUNT(DISTINCT theme) FROM manual_videos")->fetchColumn();

        view('developer/manual_sync', [
            'config' => $config,
            'globalSettingsConfig' => $settingsService->getConfig(),
            'manualRemoteStatus' => $manualRemoteStatus,
            'globalSettingsRemoteStatus' => $globalSettingsRemoteStatus,
            'localVideoCount' => $localVideoCount,
            'localThemeCount' => $localThemeCount,
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
}
