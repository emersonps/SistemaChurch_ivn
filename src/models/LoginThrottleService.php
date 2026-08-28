<?php
// src/models/LoginThrottleService.php
//
// Protecao contra forca bruta nos dois logins (admin por usuario, membro
// por CPF): bloqueio temporario por conta apos varias senhas erradas
// seguidas, mais um throttle por IP (login_failure_log) que pega ataques
// que giram entre varias contas a partir do mesmo IP.

class LoginThrottleService {
    const ACCOUNT_LOCK_THRESHOLD = 5;
    const ACCOUNT_LOCK_MINUTES = 15;
    const IP_THROTTLE_THRESHOLD = 20;
    const IP_THROTTLE_WINDOW_MINUTES = 15;

    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public static function clientIp() {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    // Minutos restantes se o IP estiver bloqueado (muitas falhas recentes,
    // de qualquer conta), ou null se liberado.
    public function ipBlockedMinutesRemaining($ip) {
        $windowStart = date('Y-m-d H:i:s', strtotime('-' . self::IP_THROTTLE_WINDOW_MINUTES . ' minutes'));
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM login_failure_log WHERE ip_address = ? AND created_at >= ?');
        $stmt->execute([$ip, $windowStart]);
        if ((int)$stmt->fetchColumn() < self::IP_THROTTLE_THRESHOLD) {
            return null;
        }

        $stmtLast = $this->db->prepare('SELECT MAX(created_at) FROM login_failure_log WHERE ip_address = ?');
        $stmtLast->execute([$ip]);
        $lastFailure = $stmtLast->fetchColumn();
        if (!$lastFailure) {
            return null;
        }

        $unlockAt = strtotime($lastFailure) + self::IP_THROTTLE_WINDOW_MINUTES * 60;
        $remaining = (int)ceil(($unlockAt - time()) / 60);
        return $remaining > 0 ? $remaining : null;
    }

    // Minutos restantes se a conta (linha de users/members) estiver
    // bloqueada, ou null se liberada.
    public function accountLockedMinutesRemaining(array $record) {
        if (empty($record['locked_until'])) {
            return null;
        }
        $unlockAt = strtotime($record['locked_until']);
        $remaining = (int)ceil(($unlockAt - time()) / 60);
        return $remaining > 0 ? $remaining : null;
    }

    // Chamado em toda falha de login (usuario/CPF nao encontrado OU senha
    // errada). $table/$recordId ficam null quando a conta nem existe —
    // nesse caso so o throttle por IP entra em acao e o retorno e null.
    // Quando a conta existe, retorna ['attempts', 'remaining', 'just_locked',
    // 'lock_minutes'] pro controller poder avisar quantas tentativas restam.
    public function recordFailure($loginType, $identifier, $table = null, $recordId = null) {
        $ip = self::clientIp();
        $insert = $this->db->prepare('INSERT INTO login_failure_log (ip_address, login_type, identifier, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)');
        $insert->execute([$ip, $loginType, $identifier]);

        // Limpeza leve a cada falha — nao existe cron nesse projeto, entao
        // o proprio caminho de escrita mantem a tabela de log enxuta.
        $this->db->exec("DELETE FROM login_failure_log WHERE created_at < '" . date('Y-m-d H:i:s', strtotime('-7 days')) . "'");

        if (!$table || !$recordId) {
            // Conta inexistente — sem contador real pra reportar. Devolver
            // "tentativas restantes" aqui denunciaria que o usuário/CPF não
            // existe (accounts reais mostram a contagem, inexistentes não).
            return null;
        }

        $this->db->prepare("UPDATE $table SET failed_login_attempts = failed_login_attempts + 1 WHERE id = ?")->execute([$recordId]);

        $stmtCount = $this->db->prepare("SELECT failed_login_attempts FROM $table WHERE id = ?");
        $stmtCount->execute([$recordId]);
        $attempts = (int)$stmtCount->fetchColumn();

        $justLocked = false;
        if ($attempts >= self::ACCOUNT_LOCK_THRESHOLD) {
            $lockUntil = date('Y-m-d H:i:s', strtotime('+' . self::ACCOUNT_LOCK_MINUTES . ' minutes'));
            $this->db->prepare("UPDATE $table SET locked_until = ? WHERE id = ?")->execute([$lockUntil, $recordId]);
            $justLocked = true;
        }

        return [
            'attempts' => $attempts,
            'remaining' => max(self::ACCOUNT_LOCK_THRESHOLD - $attempts, 0),
            'just_locked' => $justLocked,
            'lock_minutes' => self::ACCOUNT_LOCK_MINUTES,
        ];
    }

    // Chamado em todo login bem-sucedido — zera o contador da conta.
    public function resetAccount($table, $recordId) {
        $this->db->prepare("UPDATE $table SET failed_login_attempts = 0, locked_until = NULL WHERE id = ?")->execute([$recordId]);
    }
}
