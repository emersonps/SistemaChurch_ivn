<?php
// src/controllers/SyncTriggerController.php
//
// Central calls this right after queuing a username/password change so it
// applies immediately instead of waiting for the next admin page load. The
// request itself carries no sensitive data in or out — it just tells this
// instance to run its own already-authenticated pull from Central a little
// early (same call CentralUsersSyncService makes on every admin page load).
// Verified with this instance's own configured instance code so random
// requests can't trigger it; a short cooldown guards against hammering.

class SyncTriggerController {
    public function usersSyncNow() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $service = new CentralUsersSyncService();
            if (!$service->hasRemoteConfig()) {
                http_response_code(404);
                echo json_encode(['status' => 'not_configured']);
                exit;
            }

            $providedCode = trim((string)($_SERVER['HTTP_X_INSTANCE_CODE'] ?? ''));
            if ($providedCode === '' || $providedCode !== $service->getConfiguredInstanceCode()) {
                http_response_code(401);
                echo json_encode(['status' => 'unauthorized']);
                exit;
            }

            if (!$this->allowedNow('central_users_sync_trigger_last_at')) {
                http_response_code(429);
                echo json_encode(['status' => 'skipped']);
                exit;
            }

            $result = $service->forceSyncNow();
            echo json_encode(['status' => 'ok', 'applied' => (int)($result['applied'] ?? 0)]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error']);
        }
        exit;
    }

    // Central chama isso logo depois de salvar Configurações Globais (nome
    // da igreja, faixa de demonstração, etc.) pra aplicar na hora — sem
    // isso, a mudança so pegava quando um admin acessava o painel dessa
    // instancia (CentralGlobalSettingsSyncService::syncSettings() so roda
    // hoje pelo header.php, em toda pagina /admin).
    public function globalSettingsSyncNow() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $connector = new CentralManualSyncService();
            if (!$connector->hasRemoteConfig()) {
                http_response_code(404);
                echo json_encode(['status' => 'not_configured']);
                exit;
            }

            $providedCode = trim((string)($_SERVER['HTTP_X_INSTANCE_CODE'] ?? ''));
            $configuredCode = $connector->getConnectionConfig()['instance_code'] ?? '';
            if ($providedCode === '' || $providedCode !== $configuredCode) {
                http_response_code(401);
                echo json_encode(['status' => 'unauthorized']);
                exit;
            }

            if (!$this->allowedNow('central_global_settings_sync_trigger_last_at')) {
                http_response_code(429);
                echo json_encode(['status' => 'skipped']);
                exit;
            }

            $service = new CentralGlobalSettingsSyncService();
            if (!$service->isEnabled()) {
                http_response_code(404);
                echo json_encode(['status' => 'not_configured']);
                exit;
            }

            $result = $service->syncSettings();
            echo json_encode(['status' => 'ok', 'updated' => (bool)($result['updated'] ?? false)]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error']);
        }
        exit;
    }

    private function allowedNow($key) {
        $db = (new Database())->connect();

        $stmt = $db->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $last = (int)strtotime((string)$stmt->fetchColumn());
        if ($last > 0 && (time() - $last) < 3) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $update = $db->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?');
        $update->execute([$now, $key]);
        if ($update->rowCount() === 0) {
            $db->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)')->execute([$key, $now]);
        }

        return true;
    }
}
