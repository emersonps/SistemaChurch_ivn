<?php
// src/controllers/DemoLandingPublicController.php
//
// Landing pública mostrada em / no lugar da home normal quando
// demo_landing_enabled está ativo (DemoLandingService). Deixa o visitante
// entrar em qualquer perfil configurado sem digitar usuário/senha (link
// mágico, ver enter()), mostra os clientes reais do sistema (puxados da
// Central) e um formulário que já cria o contrato inicial na Central,
// entregando o visitante na tela pública de aceite/PIX que já existe lá.

class DemoLandingPublicController {
    private $roleMeta = [
        'admin' => ['label' => 'Administrador', 'desc' => 'Visão completa, financeiro, usuários, configurações.', 'icon' => 'fa-crown'],
        'secretary' => ['label' => 'Secretaria', 'desc' => 'Cadastro de membros, congregações, cultos e grupos.', 'icon' => 'fa-users'],
        'treasurer' => ['label' => 'Tesoureiro', 'desc' => 'Lançamentos, relatórios financeiros, controle de dízimos.', 'icon' => 'fa-coins'],
        'member' => ['label' => 'Membro', 'desc' => 'Carteirinha digital, eventos e histórico pessoal.', 'icon' => 'fa-id-card'],
    ];

    public function index() {
        $service = new DemoLandingService();
        $config = $service->getConfig();
        $display = $service->getDisplayCredentials();

        $accessCards = [];
        foreach ($display['credentials'] as $cred) {
            $role = $this->labelToRole($cred['label']);
            if ($role === null || empty($this->roleMeta[$role])) {
                continue;
            }
            $accessCards[] = array_merge($this->roleMeta[$role], [
                'role' => $role,
                'magic_url' => '/demo/entrar/' . $role . '/' . $this->buildToken($role, $cred['username'], $cred['password']),
            ]);
        }

        $logos = $this->fetchClientLogos();

        view('public/demo_landing', [
            'siteProfile' => getChurchSiteProfileSettings(),
            'config' => $config,
            'rotationDays' => $display['rotation_days'],
            'accessCards' => $accessCards,
            'clients' => $logos['clients'],
            'salesWhatsapp' => $logos['sales_whatsapp'],
            'planPrices' => $logos['plan_prices'] ?? [],
            'promo' => $logos['promo'] ?? [],
            'leadError' => $_SESSION['flash_error'] ?? null,
        ]);
        unset($_SESSION['flash_error']);
    }

    // Loga direto sem senha nenhuma trafegar pelo navegador: o token é um
    // hash da credencial ATUAL daquele slot, recalculado aqui e comparado —
    // ninguém consegue reverter o hash pra descobrir a senha, e o link para
    // de funcionar sozinho assim que a senha rotacionar (a cada 2 dias).
    public function enter($role, $token) {
        $service = new DemoLandingService();
        if (!$service->getConfig()['enabled']) {
            redirect('/');
        }

        $display = $service->getDisplayCredentials();
        $cred = null;
        foreach ($display['credentials'] as $c) {
            if ($this->labelToRole($c['label']) === $role) {
                $cred = $c;
                break;
            }
        }

        if (!$cred || !hash_equals($this->buildToken($role, $cred['username'], $cred['password']), (string)$token)) {
            $_SESSION['flash_error'] = 'Este link de acesso expirou (a senha de demonstração já rotacionou). Volte à página e clique no card novamente.';
            redirect('/');
        }

        if ($role === 'member') {
            $this->enterAsMember($cred['username']);
        } else {
            $this->enterAsUser($cred['username']);
        }
    }

    public function submitLead() {
        $name = trim((string)($_POST['client_name'] ?? ''));
        $whatsapp = trim((string)($_POST['client_phone'] ?? ''));
        $email = trim((string)($_POST['client_email'] ?? ''));
        $churchName = trim((string)($_POST['church_name'] ?? ''));
        $plan = trim((string)($_POST['plan'] ?? 'mensal'));

        if ($name === '' || $whatsapp === '') {
            $_SESSION['flash_error'] = 'Preencha nome e WhatsApp para continuar.';
            redirect('/');
        }

        $central = new CentralManualSyncService();
        $centralUrl = $central->getConfig()['central_url'];
        $instanceCode = $central->getConfig()['instance_code'];

        if ($centralUrl === '') {
            $_SESSION['flash_error'] = 'Não foi possível processar sua solicitação agora. Tente novamente em instantes.';
            redirect('/');
        }

        $result = $this->postToCentral($centralUrl . '/api/v1/public/demo-leads', [
            'client_name' => $name,
            'client_phone' => $whatsapp,
            'client_email' => $email,
            'church_name' => $churchName,
            'plan' => $plan,
            'source_instance_code' => $instanceCode,
        ]);

        if (empty($result['token'])) {
            $_SESSION['flash_error'] = 'Não foi possível processar sua solicitação agora. Tente novamente em instantes ou fale conosco pelo WhatsApp.';
            redirect('/');
        }

        redirect($centralUrl . '/contrato/' . $result['token']);
    }

    private function enterAsUser($username) {
        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['flash_error'] = 'Conta de demonstração indisponível no momento.';
            redirect('/');
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_congregation_id'] = $user['congregation_id'];

        redirect('/admin/dashboard');
    }

    private function enterAsMember($cpfOrUsername) {
        $cpf = preg_replace('/[^0-9]/', '', $cpfOrUsername);
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT * FROM members WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ? LIMIT 1");
        $stmt->execute([$cpf]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$member) {
            $_SESSION['flash_error'] = 'Conta de demonstração indisponível no momento.';
            redirect('/');
        }

        session_regenerate_id(true);
        $_SESSION['member_id'] = $member['id'];
        $_SESSION['member_name'] = $member['name'];
        $_SESSION['member_congregation'] = $member['congregation_id'];

        redirect('/portal/dashboard');
    }

    private function labelToRole($label) {
        $map = ['Administrador' => 'admin', 'Secretaria' => 'secretary', 'Tesoureiro' => 'treasurer', 'Membro' => 'member'];
        return $map[$label] ?? null;
    }

    private function buildToken($role, $username, $password) {
        // A senha atual (nunca enviada ao navegador) é o próprio segredo do
        // HMAC — só quem já sabe a senha do lado do servidor reproduz o
        // mesmo hash.
        return hash('sha256', $role . '|' . $username . '|' . $password);
    }

    // Cache local (5min) da lista de clientes + preços/promoção puxados da
    // Central — a landing nunca quebra se a Central estiver fora do ar, só
    // mostra o último resultado bom conhecido (ou nada, na primeira vez).
    // Curto de propósito: quando o admin configura um preço ou promoção na
    // Central, ele espera ver refletido na landing rapidamente, não em até
    // 6h — a query em si é leve, então cachear por minutos já resolve o
    // problema de nunca bater na Central a cada visita, sem deixar o admin
    // esperando horas pra ver a própria mudança.
    private function fetchClientLogos() {
        $db = (new Database())->connect();
        $cachedAt = $this->getSetting($db, 'demo_client_logos_cached_at', '');
        $stale = $cachedAt === '' || (time() - strtotime($cachedAt)) > 5 * 60;

        if ($stale) {
            $central = new CentralManualSyncService();
            $centralUrl = $central->getConfig()['central_url'];
            if ($centralUrl !== '') {
                $fresh = $this->getFromCentral($centralUrl . '/api/v1/public/client-logos');
                if (is_array($fresh) && isset($fresh['clients'])) {
                    $this->saveSetting($db, 'demo_client_logos_cache', json_encode($fresh));
                    $this->saveSetting($db, 'demo_client_logos_cached_at', date('Y-m-d H:i:s'));
                    return $fresh;
                }
            }
        }

        $cached = json_decode($this->getSetting($db, 'demo_client_logos_cache', ''), true);
        return is_array($cached) ? $cached : ['clients' => [], 'sales_whatsapp' => ''];
    }

    private function postToCentral($url, array $data) {
        if (!function_exists('curl_init')) {
            return null;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $raw = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $status >= 400) {
            return null;
        }
        $decoded = json_decode($this->stripBom($raw), true);
        return is_array($decoded) ? $decoded : null;
    }

    private function getFromCentral($url) {
        if (!function_exists('curl_init')) {
            return null;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $raw = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $status >= 400) {
            return null;
        }
        $decoded = json_decode($this->stripBom($raw), true);
        return is_array($decoded) ? $decoded : null;
    }

    // Bootstrap da Central injeta um BOM UTF-8 antes de qualquer output —
    // json_decode() falha silenciosamente se não removido primeiro.
    private function stripBom($raw) {
        return preg_replace('/^(?:\\xEF\\xBB\\xBF)+/', '', (string)$raw);
    }

    private function getSetting(PDO $db, $key, $default = '') {
        $stmt = $db->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    }

    private function saveSetting(PDO $db, $key, $value) {
        $stmt = $db->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?');
        $stmt->execute([$value, $key]);
        if ($stmt->rowCount() > 0) {
            return;
        }
        $insert = $db->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)');
        try {
            $insert->execute([$key, $value]);
        } catch (Exception $e) {
            $stmt->execute([$value, $key]);
        }
    }
}
