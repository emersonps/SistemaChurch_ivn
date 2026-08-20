<?php
// src/controllers/DemoPresenceController.php
//
// Same-origin heartbeat endpoints for the demo landing page's "N pessoas
// online" counter. No auth needed — these are public pages and the only
// thing exposed is an aggregate count, never any per-visitor detail.

class DemoPresenceController {
    public function ping() {
        try {
            $this->json((new DemoPresenceService())->ping($_POST['key'] ?? ''));
        } catch (Throwable $e) {
            // Most likely cause: the demo_online_sessions migration hasn't
            // run on this database yet. Surface it as a real error instead
            // of the counter just silently never appearing.
            $this->error($e);
        }
    }

    public function leave() {
        try {
            (new DemoPresenceService())->leave($_POST['key'] ?? '');
            $this->json(0, false);
        } catch (Throwable $e) {
            $this->error($e);
        }
    }

    private function json($count, $includeCount = true) {
        header('Content-Type: application/json; charset=utf-8');
        $payload = ['status' => 'ok'];
        if ($includeCount) {
            $payload['count'] = $count;
        }
        echo json_encode($payload);
        exit;
    }

    private function error(Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}
