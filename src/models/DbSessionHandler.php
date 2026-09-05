<?php
// src/models/DbSessionHandler.php
//
// Guarda sessoes PHP na tabela `sessions` em vez de arquivo local — ver
// database/migrations/20260904_235000_create_sessions_table.php pro porque.

class DbSessionHandler implements SessionHandlerInterface {
    private $db;
    private $lifetimeSeconds;

    public function __construct(PDO $db, $lifetimeSeconds = 86400) {
        $this->db = $db;
        $this->lifetimeSeconds = $lifetimeSeconds;
    }

    public function open($savePath, $sessionName): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read($id): string {
        $stmt = $this->db->prepare('SELECT data FROM sessions WHERE id = ? AND last_activity >= ?');
        $stmt->execute([$id, time() - $this->lifetimeSeconds]);
        $data = $stmt->fetchColumn();
        return $data !== false ? $data : '';
    }

    public function write($id, $data): bool {
        $stmt = $this->db->prepare('REPLACE INTO sessions (id, data, last_activity) VALUES (?, ?, ?)');
        return $stmt->execute([$id, $data, time()]);
    }

    public function destroy($id): bool {
        $stmt = $this->db->prepare('DELETE FROM sessions WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function gc($max_lifetime): int {
        $stmt = $this->db->prepare('DELETE FROM sessions WHERE last_activity < ?');
        $stmt->execute([time() - $max_lifetime]);
        return $stmt->rowCount();
    }
}
