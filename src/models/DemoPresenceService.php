<?php
// src/models/DemoPresenceService.php
//
// Powers the discreet "N pessoas online" counter on the demo landing page.
// Heartbeat-based: each open tab pings every few seconds to refresh its
// last_seen_at; anything not heard from within the timeout is treated as
// gone. There's also a best-effort explicit "leave" call fired on tab
// close/navigation for a snappier decrement in the common case, but the
// heartbeat timeout is what actually guarantees correctness (a browser
// crash or killed tab can't fire any JS at all).

class DemoPresenceService {
    private $db;
    private $timeoutSeconds = 20;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function ping($sessionKey) {
        $sessionKey = $this->normalizeKey($sessionKey);
        if ($sessionKey === '') {
            return $this->count();
        }

        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare('UPDATE demo_online_sessions SET last_seen_at = ? WHERE session_key = ?');
        $stmt->execute([$now, $sessionKey]);
        if ($stmt->rowCount() === 0) {
            $insert = $this->db->prepare('INSERT INTO demo_online_sessions (session_key, last_seen_at) VALUES (?, ?)');
            try {
                $insert->execute([$sessionKey, $now]);
            } catch (Exception $e) {
                // Unique-key race with another request for the same tab — fine, it's already there.
            }
        }

        return $this->count();
    }

    public function leave($sessionKey) {
        $sessionKey = $this->normalizeKey($sessionKey);
        if ($sessionKey === '') {
            return;
        }

        $this->db->prepare('DELETE FROM demo_online_sessions WHERE session_key = ?')->execute([$sessionKey]);
    }

    public function count() {
        $cutoff = date('Y-m-d H:i:s', time() - $this->timeoutSeconds);
        $this->db->prepare('DELETE FROM demo_online_sessions WHERE last_seen_at < ?')->execute([$cutoff]);

        return (int)$this->db->query('SELECT COUNT(*) FROM demo_online_sessions')->fetchColumn();
    }

    private function normalizeKey($sessionKey) {
        $sessionKey = trim((string)$sessionKey);
        if (!preg_match('/^[A-Za-z0-9_-]{8,64}$/', $sessionKey)) {
            return '';
        }
        return $sessionKey;
    }
}
