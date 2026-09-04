<?php
// src/controllers/DataCleanupController.php
//
// Limpa os dados operacionais de uma instancia recem-provisionada (membros,
// congregacoes, financeiro, eventos, etc.), preservando so o que o sistema
// precisa pra continuar funcionando: usuarios, papeis/permissoes e
// configuracoes. Pensado pra depois de rodar um SQL de setup que trouxe
// dados de exemplo/demo junto com a estrutura.

class DataCleanupController {
    // Tabelas nunca tocadas — sistema de login/permissoes, configuracoes,
    // controle de migrations, e o hinario (dado de referencia universal,
    // nao especifico de uma igreja, caro de re-semear).
    private $keepTables = [
        'migrations',
        'users',
        'roles',
        'permissions',
        'user_permissions',
        'settings',
        'site_settings',
        'harpa_hymns',
    ];

    private function requireDeveloper() {
        if (!isset($_SESSION['user_id'])) {
            redirect('/admin/login');
        }
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            return;
        }
        if (function_exists('hasPermission') && hasPermission('developer.access')) {
            return;
        }
        redirect('/admin/dashboard');
    }

    private function allTables(PDO $db) {
        $stmt = $db->query('SHOW TABLES');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function tablesToClear(PDO $db) {
        $tables = $this->allTables($db);
        return array_values(array_diff($tables, $this->keepTables));
    }

    private function confirmationLabel() {
        $alias = getSystemSetting('church_alias', '');
        return $alias !== '' ? $alias : getSystemSetting('church_name', 'esta instância');
    }

    public function index() {
        $this->requireDeveloper();
        $db = (new Database())->connect();

        view('developer/data_cleanup', [
            'keepTables' => $this->keepTables,
            'clearTables' => $this->tablesToClear($db),
            'confirmationLabel' => $this->confirmationLabel(),
        ]);
    }

    public function run() {
        $this->requireDeveloper();
        verify_csrf();

        $db = (new Database())->connect();
        $expected = $this->confirmationLabel();
        $typed = trim((string)($_POST['confirmation'] ?? ''));

        if ($typed === '' || strcasecmp($typed, $expected) !== 0) {
            $_SESSION['error'] = 'Confirmação não confere. Nada foi apagado.';
            redirect('/developer/data-cleanup');
        }

        $tables = $this->tablesToClear($db);

        $db->exec('SET FOREIGN_KEY_CHECKS = 0');
        try {
            foreach ($tables as $table) {
                $db->exec('TRUNCATE TABLE `' . $table . '`');
            }
            // users aponta pra members/congregations (agora vazias) —
            // limpa as referências pra não deixar FK pendurada.
            $db->exec('UPDATE users SET member_id = NULL, congregation_id = NULL');
        } finally {
            $db->exec('SET FOREIGN_KEY_CHECKS = 1');
        }

        $_SESSION['success'] = count($tables) . ' tabela(s) limpa(s). Usuários, papéis, permissões, configurações e o hinário foram preservados.';
        redirect('/developer/data-cleanup');
    }
}
