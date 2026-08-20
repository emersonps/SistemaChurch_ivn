<?php
// src/controllers/DemoPresenceController.php
//
// Same-origin heartbeat endpoints for the demo landing page's "N pessoas
// online" counter. No auth needed — these are public pages and the only
// thing exposed is an aggregate count, never any per-visitor detail.

class DemoPresenceController {
    public function ping() {
        $this->json((new DemoPresenceService())->ping($_POST['key'] ?? ''));
    }

    public function leave() {
        (new DemoPresenceService())->leave($_POST['key'] ?? '');
        $this->json(0, false);
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
}
