<?php
// src/models/DemoLandingService.php
//
// Powers an optional "product demo" landing page shown at / instead of the
// normal homepage. Off by default on every instance — only meant for a
// dedicated demo/sales instance (e.g. IgrejaBR). The enabled flag, public
// URL and the three demo usernames are edited centrally in Central's
// "Página de Demonstração" screen and reach this instance through the
// existing global-settings sync (CentralGlobalSettingsSyncService), which
// writes them into the same settings table read here. When on, this
// service keeps the configured demo accounts' passwords rotating every 2
// days so visitors always get a fresh, short-lived credential to try the
// product with.

class DemoLandingService {
    private $db;
    private $rotationDays = 2;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getConfig() {
        return [
            'enabled' => $this->getSetting('demo_landing_enabled', '0') === '1',
            'public_url' => $this->getSetting('demo_public_url', 'https://igrejabr.com.br'),
            'admin_username' => $this->getSetting('demo_admin_username', ''),
            'secretary_username' => $this->getSetting('demo_secretary_username', ''),
            'member_username' => $this->getSetting('demo_member_username', ''),
        ];
    }

    // Rotates any configured demo account's password if the last rotation
    // is 2+ days old (or never happened), then returns the current
    // credentials for display. Safe to call on every landing-page load.
    public function getDisplayCredentials() {
        $config = $this->getConfig();
        $slots = [
            'admin' => ['label' => 'Administrador', 'username' => $config['admin_username']],
            'secretary' => ['label' => 'Secretaria', 'username' => $config['secretary_username']],
            'member' => ['label' => 'Membro', 'username' => $config['member_username']],
        ];

        $rotatedAt = $this->getSetting('demo_credentials_rotated_at', '');
        $needsRotation = $rotatedAt === '' || (time() - strtotime($rotatedAt)) >= ($this->rotationDays * 86400);

        if ($needsRotation) {
            $this->rotateAll($slots);
            $rotatedAt = date('Y-m-d H:i:s');
            $this->saveSetting('demo_credentials_rotated_at', $rotatedAt);
        }

        $credentials = [];
        foreach ($slots as $key => $slot) {
            if ($slot['username'] === '') {
                continue;
            }
            $credentials[] = [
                'label' => $slot['label'],
                'username' => $slot['username'],
                'password' => $this->getSetting('demo_' . $key . '_password_plain', ''),
            ];
        }

        return [
            'credentials' => $credentials,
            'rotated_at' => $rotatedAt,
            'next_rotation_at' => $rotatedAt !== '' ? date('Y-m-d H:i:s', strtotime($rotatedAt) + $this->rotationDays * 86400) : '',
            'rotation_days' => $this->rotationDays,
        ];
    }

    // Called by CentralGlobalSettingsSyncService right after a sync brings
    // in new demo-landing config, so a corrected username takes effect
    // immediately instead of sitting unused until the next 2-day window.
    // Returns a per-slot report ([label => bool applied]) for logging.
    public function forceRotateNow() {
        $config = $this->getConfig();
        $slots = [
            'admin' => ['label' => 'Administrador', 'username' => $config['admin_username']],
            'secretary' => ['label' => 'Secretaria', 'username' => $config['secretary_username']],
            'member' => ['label' => 'Membro', 'username' => $config['member_username']],
        ];

        $report = $this->rotateAll($slots);
        $this->saveSetting('demo_credentials_rotated_at', date('Y-m-d H:i:s'));
        return $report;
    }

    private function rotateAll(array $slots) {
        $report = [];

        foreach ($slots as $key => $slot) {
            if ($slot['username'] === '') {
                continue;
            }

            $plainPassword = $this->generatePassword();
            $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
            $applied = false;

            if ($key === 'member') {
                // O login do portal usa CPF, não users.username — mesma
                // busca de "Alterar senha por CPF". Se ainda não existe
                // nenhum membro com esse CPF, cria um registro mínimo na
                // hora, para a demonstração já sair funcionando sem exigir
                // um cadastro manual prévio.
                $cpf = preg_replace('/[^0-9]/', '', $slot['username']);
                if ($cpf !== '') {
                    $memberStmt = $this->db->prepare("UPDATE members SET password = ? WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ?");
                    $memberStmt->execute([$hash, $cpf]);
                    if ($memberStmt->rowCount() > 0) {
                        $applied = true;
                    } else {
                        $insert = $this->db->prepare("INSERT INTO members (name, cpf, password, role) VALUES (?, ?, ?, 'Membro')");
                        $insert->execute(['Membro Demonstração', $cpf, $hash]);
                        $applied = true;
                    }
                }
            } else {
                // Idem para admin/secretary: se o username configurado ainda
                // não existe em `users`, cria um usuário mínimo com o papel
                // correspondente em vez de ficar rotacionando a senha de
                // ninguém silenciosamente.
                $stmt = $this->db->prepare('UPDATE users SET password = ? WHERE username = ?');
                $stmt->execute([$hash, $slot['username']]);
                if ($stmt->rowCount() > 0) {
                    $applied = true;
                } else {
                    $insert = $this->db->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
                    $insert->execute([$slot['username'], $hash, $key]);
                    $applied = true;
                }
            }

            if ($applied) {
                $this->saveSetting('demo_' . $key . '_password_plain', $plainPassword);
            }

            $report[] = ['label' => $slot['label'], 'username' => $slot['username'], 'applied' => $applied];
        }

        return $report;
    }

    private function generatePassword() {
        // Readable on purpose (no ambiguous 0/O/1/l/I) — it's typed by hand
        // by whoever is trying the demo.
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $password = '';
        for ($i = 0; $i < 8; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }

    private function getSetting($key, $default = '') {
        $stmt = $this->db->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    }

    private function saveSetting($key, $value) {
        $stmt = $this->db->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?');
        $stmt->execute([$value, $key]);
        if ($stmt->rowCount() > 0) {
            return;
        }

        $insert = $this->db->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)');
        try {
            $insert->execute([$key, $value]);
        } catch (Exception $e) {
            $stmt->execute([$value, $key]);
        }
    }
}
