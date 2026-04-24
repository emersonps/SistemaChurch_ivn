<?php

class PrayerController {
    private function db() {
        return (new Database())->connect();
    }

    private function isModerator() {
        $role = $_SESSION['user_role'] ?? '';
        return isset($_SESSION['user_id']) && in_array($role, ['admin', 'developer'], true);
    }

    private function requireModerator() {
        if (!$this->isModerator()) {
            $_SESSION['error'] = 'Somente admin ou developer podem editar e excluir pedidos.';
            redirect('/oracao');
        }
    }

    private function ensurePrayerSessionKey() {
        if (empty($_SESSION['prayer_session_key'])) {
            $_SESSION['prayer_session_key'] = bin2hex(random_bytes(16));
        }

        return $_SESSION['prayer_session_key'];
    }

    private function clientIpHash() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if ($ip === '') {
            return null;
        }

        return hash('sha256', $ip);
    }

    private function normalizeName($value) {
        $value = trim((string)$value);
        $value = preg_replace('/\s+/', ' ', $value);
        if ($value === '') {
            return '';
        }

        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, 120);
        }

        return substr($value, 0, 120);
    }

    private function normalizeMessage($value) {
        $value = trim((string)$value);
        $value = preg_replace("/\r\n|\r/", "\n", $value);
        $value = preg_replace("/\n{3,}/", "\n\n", $value);

        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, 3000);
        }

        return substr($value, 0, 3000);
    }

    private function requestExists($id) {
        $stmt = $this->db()->prepare("SELECT id FROM prayer_requests WHERE id = ? AND status = 'published' LIMIT 1");
        $stmt->execute([$id]);
        return (bool)$stmt->fetchColumn();
    }

    private function deleteAmensForRequest($id) {
        $stmt = $this->db()->prepare("DELETE FROM prayer_request_amens WHERE prayer_request_id = ?");
        $stmt->execute([$id]);
    }

    public function index() {
        $db = $this->db();
        $sessionKey = $this->ensurePrayerSessionKey();
        $canModerate = $this->isModerator();

        $requests = $db->query("
            SELECT *
            FROM prayer_requests
            WHERE status = 'published'
            ORDER BY created_at DESC, id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $amenedIds = [];
        if (!empty($requests)) {
            $stmt = $db->prepare("SELECT prayer_request_id FROM prayer_request_amens WHERE session_key = ?");
            $stmt->execute([$sessionKey]);
            $amenedIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        }

        $stats = $db->query("
            SELECT
                COUNT(*) AS total_requests,
                COALESCE(SUM(amen_count), 0) AS total_amens
            FROM prayer_requests
            WHERE status = 'published'
        ")->fetch(PDO::FETCH_ASSOC) ?: ['total_requests' => 0, 'total_amens' => 0];

        view('public/prayer', [
            'requests' => $requests,
            'amenedIds' => $amenedIds,
            'canModerate' => $canModerate,
            'stats' => $stats
        ]);
    }

    public function store() {
        verify_csrf();

        $name = $this->normalizeName($_POST['name'] ?? '');
        $message = $this->normalizeMessage($_POST['message'] ?? '');
        $isAnonymous = isset($_POST['is_anonymous']) ? 1 : 0;

        if ($message === '') {
            $_SESSION['error'] = 'Escreva o pedido de oração antes de enviar.';
            redirect('/oracao#pedido-form');
        }

        if (!$isAnonymous && $name === '') {
            $_SESSION['error'] = 'Informe seu nome ou marque a opção de pedido anônimo.';
            redirect('/oracao#pedido-form');
        }

        $db = $this->db();
        $stmt = $db->prepare("
            INSERT INTO prayer_requests (name, is_anonymous, message, status, amen_count)
            VALUES (?, ?, ?, 'published', 0)
        ");
        $stmt->execute([
            $name !== '' ? $name : null,
            $isAnonymous,
            $message
        ]);

        $_SESSION['success'] = 'Pedido enviado com sucesso. A igreja já pode orar com você.';
        redirect('/oracao#mural');
    }

    public function amen($id) {
        verify_csrf();

        $id = (int)$id;
        if ($id <= 0 || !$this->requestExists($id)) {
            $_SESSION['error'] = 'Pedido de oração não encontrado.';
            redirect('/oracao#mural');
        }

        $db = $this->db();
        $sessionKey = $this->ensurePrayerSessionKey();
        $ipHash = $this->clientIpHash();

        try {
            $stmt = $db->prepare("
                INSERT INTO prayer_request_amens (prayer_request_id, session_key, ip_hash)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$id, $sessionKey, $ipHash]);

            $db->prepare("UPDATE prayer_requests SET amen_count = amen_count + 1 WHERE id = ?")->execute([$id]);
            $_SESSION['success'] = 'Seu Amém foi registrado.';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Você já marcou Amém neste pedido.';
        }

        redirect('/oracao#mural');
    }

    public function update($id) {
        verify_csrf();
        $this->requireModerator();

        $id = (int)$id;
        $message = $this->normalizeMessage($_POST['message'] ?? '');
        $name = $this->normalizeName($_POST['name'] ?? '');
        $isAnonymous = isset($_POST['is_anonymous']) ? 1 : 0;

        if ($id <= 0 || $message === '') {
            $_SESSION['error'] = 'Não foi possível salvar esse pedido.';
            redirect('/oracao#mural');
        }

        if (!$isAnonymous && $name === '') {
            $_SESSION['error'] = 'Informe um nome ou marque o pedido como anônimo.';
            redirect('/oracao#mural');
        }

        $stmt = $this->db()->prepare("
            UPDATE prayer_requests
            SET name = ?, is_anonymous = ?, message = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([
            $name !== '' ? $name : null,
            $isAnonymous,
            $message,
            $id
        ]);

        $_SESSION['success'] = 'Pedido atualizado com sucesso.';
        redirect('/oracao#mural');
    }

    public function delete($id) {
        verify_csrf();
        $this->requireModerator();

        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = 'Pedido inválido.';
            redirect('/oracao#mural');
        }

        $db = $this->db();
        $this->deleteAmensForRequest($id);
        $stmt = $db->prepare("DELETE FROM prayer_requests WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['success'] = 'Pedido removido com sucesso.';
        redirect('/oracao#mural');
    }
}
