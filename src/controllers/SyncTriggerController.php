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

        if (!$this->allowedNow()) {
            http_response_code(429);
            echo json_encode(['status' => 'skipped']);
            exit;
        }

        try {
            $result = $service->forceSyncNow();
            echo json_encode(['status' => 'ok', 'applied' => (int)($result['applied'] ?? 0)]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error']);
        }
        exit;
    }

    private function allowedNow() {
        $db = (new Database())->connect();
        $key = 'central_users_sync_trigger_last_at';

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
