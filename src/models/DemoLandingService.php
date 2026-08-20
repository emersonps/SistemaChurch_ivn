<?php
// src/models/DemoLandingService.php
//
// Powers an optional "product demo" landing page shown at / instead of the
// normal homepage. Off by default on every instance — only meant for a
// dedicated demo/sales instance (e.g. IgrejaBR), turned on via
// /developer/settings on that instance specifically. When on, it keeps a
// small set of demo accounts' passwords rotating every 2 days so visitors
// always get a fresh, short-lived credential to try the product with.

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

    public function saveConfig(array $data) {
        $this->saveSetting('demo_landing_enabled', !empty($data['enabled']) ? '1' : '0');
        $this->saveSetting('demo_public_url', trim((string)($data['public_url'] ?? '')));
        $this->saveSetting('demo_admin_username', trim((string)($data['admin_username'] ?? '')));
        $this->saveSetting('demo_secretary_username', trim((string)($data['secretary_username'] ?? '')));
        $this->saveSetting('demo_member_username', trim((string)($data['member_username'] ?? '')));

        return $this->getConfig();
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

    // Manual override for /developer/settings — rotates immediately instead
    // of waiting for the 2-day window, e.g. right after fixing a
    // misconfigured username so the fix doesn't sit unused for 2 days.
    // Returns a per-slot report ([label => bool applied]) so the settings
    // screen can say exactly which accounts were found, instead of the
    // demo page silently showing a blank password with no explanation.
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

            $stmt = $this->db->prepare('UPDATE users SET password = ? WHERE username = ?');
            $stmt->execute([$hash, $slot['username']]);
            if ($stmt->rowCount() > 0) {
                $applied = true;
            }

            // The "member" slot is usually a portal login identified by
            // CPF, not a users.username row — try that too (same lookup
            // as "Alterar senha por CPF"). Harmless no-op if the value
            // isn't CPF-shaped or doesn't match anyone.
            $cpf = preg_replace('/[^0-9]/', '', $slot['username']);
            if ($cpf !== '') {
                $memberStmt = $this->db->prepare("UPDATE members SET password = ? WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ?");
                $memberStmt->execute([$hash, $cpf]);
                if ($memberStmt->rowCount() > 0) {
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
