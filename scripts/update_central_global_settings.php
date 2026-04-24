<?php

$root = 'D:/SistemaChurch_Central';

function writeCentralFile($root, $relativePath, $content) {
    $fullPath = $root . '/' . str_replace('\\', '/', $relativePath);
    $directory = dirname($fullPath);
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
    file_put_contents($fullPath, $content);
}

$files = [];

$files['public/index.php'] = <<<'PHP'
<?php

ob_start();

require_once __DIR__ . '/../src/bootstrap.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($uri !== '/' && substr($uri, -1) === '/') {
    $uri = rtrim($uri, '/');
}
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && strpos($uri, '/api/') !== 0) {
    verify_csrf();
}

if ($uri === '/health') {
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'app' => APP_NAME,
        'env' => APP_ENV,
        'status' => 'ok',
        'time' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($uri === '/' || $uri === '/admin/dashboard') {
    (new DashboardController())->index();
} elseif ($uri === '/admin/login') {
    if ($method === 'POST') {
        (new AuthController())->login();
    } else {
        (new AuthController())->showLogin();
    }
} elseif ($uri === '/admin/logout') {
    (new AuthController())->logout();
} elseif ($uri === '/admin/instances') {
    if ($method === 'POST') {
        (new InstanceController())->store();
    } else {
        (new InstanceController())->index();
    }
} elseif ($method === 'POST' && preg_match('#^/admin/instances/delete/(\d+)$#', $uri, $matches)) {
    (new InstanceController())->delete($matches[1]);
} elseif ($uri === '/admin/manuals') {
    if ($method === 'POST') {
        (new ManualVideoController())->store();
    } else {
        (new ManualVideoController())->index();
    }
} elseif ($method === 'POST' && preg_match('#^/admin/manuals/delete/(\d+)$#', $uri, $matches)) {
    (new ManualVideoController())->delete($matches[1]);
} elseif ($uri === '/admin/manuals/publish' && $method === 'POST') {
    (new ManualVideoController())->publish();
} elseif ($uri === '/admin/global-settings') {
    if ($method === 'POST') {
        (new GlobalSettingsController())->save();
    } else {
        (new GlobalSettingsController())->index();
    }
} elseif ($uri === '/admin/global-settings/publish' && $method === 'POST') {
    (new GlobalSettingsController())->publish();
} elseif ($uri === '/api/v1/ping') {
    (new SyncApiController())->ping();
} elseif ($uri === '/api/v1/manuals/version') {
    (new SyncApiController())->manualsVersion();
} elseif ($uri === '/api/v1/manuals/export') {
    (new SyncApiController())->manualsExport();
} elseif ($uri === '/api/v1/global-settings/version') {
    (new SyncApiController())->globalSettingsVersion();
} elseif ($uri === '/api/v1/global-settings/export') {
    (new SyncApiController())->globalSettingsExport();
} else {
    http_response_code(404);
    echo 'Rota não encontrada.';
}
PHP;

$files['src/controllers/SyncApiController.php'] = <<<'PHP'
<?php

class SyncApiController {
    private function json($data, $status = 200) {
        if (ob_get_length()) {
            ob_clean();
        }
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function ping() {
        [$instance, $error] = (new ApiAuthService())->authenticate();
        if ($error) {
            $this->json(['error' => $error], 401);
        }

        $this->touchInstance($instance['id'], true, false);
        $this->log($instance['id'], 'ping', 'success', 'Ping recebido com sucesso.');
        $this->json([
            'status' => 'ok',
            'instance' => $instance['name'],
            'time' => date('Y-m-d H:i:s')
        ]);
    }

    public function manualsVersion() {
        $this->moduleVersion('manuals', 'manuals');
    }

    public function manualsExport() {
        $this->moduleExport('manuals', 'manuals', true, false);
    }

    public function globalSettingsVersion() {
        $this->moduleVersion('global_settings', 'global-settings');
    }

    public function globalSettingsExport() {
        $this->moduleExport('global_settings', 'global-settings', false, false);
    }

    private function moduleVersion($moduleSlug, $routeSlug) {
        [$instance, $error] = (new ApiAuthService())->authenticate($moduleSlug);
        if ($error) {
            $this->json(['error' => $error], 401);
        }

        $release = (new ReleaseBuilderService())->getLatestPublished($moduleSlug);
        if (!$release) {
            $this->log($instance['id'], $moduleSlug, 'error', 'Nenhuma publicação disponível para ' . $moduleSlug . '.');
            $this->json(['error' => 'Nenhuma publicação disponível para este módulo.'], 404);
        }

        $this->touchInstance($instance['id'], false, false);
        $this->log($instance['id'], $moduleSlug, 'success', 'Consulta de versão do módulo ' . $moduleSlug . '.');
        $this->json([
            'module' => $routeSlug,
            'version' => (int)$release['version'],
            'checksum' => $release['checksum'],
            'published_at' => $release['published_at']
        ]);
    }

    private function moduleExport($moduleSlug, $routeSlug, $touchManualSync, $touchGlobalSync) {
        [$instance, $error] = (new ApiAuthService())->authenticate($moduleSlug);
        if ($error) {
            $this->json(['error' => $error], 401);
        }

        $release = (new ReleaseBuilderService())->getLatestPublished($moduleSlug);
        if (!$release) {
            $this->log($instance['id'], $moduleSlug, 'error', 'Nenhuma exportação disponível para ' . $moduleSlug . '.');
            $this->json(['error' => 'Nenhuma exportação disponível para este módulo.'], 404);
        }

        $this->touchInstance($instance['id'], $touchManualSync, $touchGlobalSync);
        $this->log($instance['id'], $moduleSlug, 'success', 'Exportação do módulo ' . $moduleSlug . ' entregue.');

        $this->json([
            'module' => $routeSlug,
            'version' => (int)$release['version'],
            'checksum' => $release['checksum'],
            'published_at' => $release['published_at'],
            'payload' => $release['payload']
        ]);
    }

    private function touchInstance($instanceId, $touchManualSync, $touchGlobalSync) {
        $db = (new Database())->connect();
        $fields = ['last_ping_at = CURRENT_TIMESTAMP'];
        if ($touchManualSync) {
            $fields[] = 'last_manual_sync_at = CURRENT_TIMESTAMP';
        }
        if ($touchGlobalSync) {
            $fields[] = 'last_global_settings_sync_at = CURRENT_TIMESTAMP';
        }
        $sql = 'UPDATE instances SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $db->prepare($sql)->execute([$instanceId]);
    }

    private function log($instanceId, $module, $status, $message) {
        $db = (new Database())->connect();
        $stmt = $db->prepare('INSERT INTO sync_logs (instance_id, module_slug, direction, status, message, request_ip) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$instanceId, $module, 'outbound', $status, $message, $_SERVER['REMOTE_ADDR'] ?? null]);
    }
}
PHP;

$files['src/services/ReleaseBuilderService.php'] = <<<'PHP'
<?php

class ReleaseBuilderService {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function publishManuals($userId) {
        $videos = $this->db->query('SELECT * FROM manual_videos WHERE is_active = 1 ORDER BY theme ASC, sort_order ASC, title ASC')->fetchAll(PDO::FETCH_ASSOC);
        $targets = $this->db->query('SELECT * FROM manual_video_targets ORDER BY manual_video_id ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);

        return $this->publishModulePayload('manuals', [
            'manual_videos' => $videos,
            'manual_video_targets' => $targets
        ], $userId);
    }

    public function publishGlobalSettings($userId) {
        $rows = $this->db->query('SELECT setting_key, setting_value FROM central_global_settings ORDER BY setting_key ASC')->fetchAll(PDO::FETCH_ASSOC);
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $this->publishModulePayload('global_settings', [
            'settings' => $settings
        ], $userId);
    }

    public function getLatestPublished($slug) {
        $stmt = $this->db->prepare("
            SELECT mr.*, m.slug
            FROM module_releases mr
            JOIN modules m ON m.id = mr.module_id
            WHERE m.slug = ? AND mr.is_published = 1
            ORDER BY mr.version DESC
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $release = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$release) {
            return null;
        }

        $release['payload'] = json_decode($release['payload_json'], true) ?: [];
        return $release;
    }

    private function publishModulePayload($slug, array $payload, $userId) {
        $moduleId = $this->getModuleId($slug);

        $stmtVersion = $this->db->prepare('SELECT MAX(version) FROM module_releases WHERE module_id = ?');
        $stmtVersion->execute([$moduleId]);
        $version = (int)$stmtVersion->fetchColumn() + 1;
        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $checksum = hash('sha256', $payloadJson);

        $stmt = $this->db->prepare("
            INSERT INTO module_releases (module_id, version, payload_json, checksum, is_published, published_at, created_by)
            VALUES (?, ?, ?, ?, 1, CURRENT_TIMESTAMP, ?)
        ");
        $stmt->execute([$moduleId, $version, $payloadJson, $checksum, $userId]);

        return [
            'version' => $version,
            'checksum' => $checksum
        ];
    }

    private function getModuleId($slug) {
        $stmt = $this->db->prepare('SELECT id FROM modules WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int)$id;
        }

        $insert = $this->db->prepare('INSERT INTO modules (slug, name, is_active) VALUES (?, ?, 1)');
        $insert->execute([$slug, ucwords(str_replace('_', ' ', $slug))]);
        return (int)$this->db->lastInsertId();
    }
}
PHP;

$files['src/controllers/InstanceController.php'] = <<<'PHP'
<?php

class InstanceController {
    public function index() {
        requireLogin();
        $db = (new Database())->connect();
        $editing = null;

        if (!empty($_GET['edit'])) {
            $stmtEdit = $db->prepare('SELECT * FROM instances WHERE id = ?');
            $stmtEdit->execute([(int)$_GET['edit']]);
            $editing = $stmtEdit->fetch(PDO::FETCH_ASSOC) ?: null;
            if ($editing) {
                $editing['module_slugs'] = $this->getEnabledModuleSlugs($db, $editing['id']);
            }
        }

        $instances = $db->query('SELECT * FROM instances ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($instances as &$instance) {
            $stmtToken = $db->prepare('SELECT token_last4 FROM instance_tokens WHERE instance_id = ? AND is_active = 1 ORDER BY id DESC LIMIT 1');
            $stmtToken->execute([$instance['id']]);
            $instance['token_last4'] = $stmtToken->fetchColumn() ?: '----';
            $instance['modules'] = $this->getEnabledModuleSlugs($db, $instance['id']);
        }
        unset($instance);

        $rawToken = consume_flash('token_plain');
        view('admin/instances/index', [
            'instances' => $instances,
            'editing' => $editing,
            'rawToken' => $rawToken
        ]);
    }

    public function store() {
        requireLogin();
        verify_csrf();

        $db = (new Database())->connect();
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $name = trim((string)($_POST['name'] ?? ''));
        $code = trim((string)($_POST['code'] ?? ''));
        $baseUrl = rtrim(trim((string)($_POST['base_url'] ?? '')), '/');
        $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
        $environment = trim((string)($_POST['environment'] ?? 'production'));
        $notes = trim((string)($_POST['notes'] ?? ''));

        if ($name === '' || $code === '' || $baseUrl === '') {
            flash('error', 'Nome, código e URL base são obrigatórios.');
            redirect('/admin/instances' . ($id ? '?edit=' . $id : ''));
        }

        if ($id) {
            $stmt = $db->prepare('UPDATE instances SET name = ?, code = ?, base_url = ?, status = ?, environment = ?, notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $stmt->execute([$name, $code, $baseUrl, $status, $environment, $notes, $id]);
            $instanceId = $id;
        } else {
            $stmt = $db->prepare('INSERT INTO instances (name, code, base_url, status, environment, notes) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $code, $baseUrl, $status, $environment, $notes]);
            $instanceId = (int)$db->lastInsertId();
        }

        $this->syncModuleAccess($db, $instanceId, 'manuals', isset($_POST['module_manuals']));
        $this->syncModuleAccess($db, $instanceId, 'global_settings', isset($_POST['module_global_settings']));

        if (isset($_POST['rotate_token']) || !$this->hasActiveToken($db, $instanceId)) {
            $plainToken = bin2hex(random_bytes(24));
            $db->prepare('UPDATE instance_tokens SET is_active = 0 WHERE instance_id = ?')->execute([$instanceId]);
            $stmtToken = $db->prepare('INSERT INTO instance_tokens (instance_id, token_hash, token_last4, is_active) VALUES (?, ?, ?, 1)');
            $stmtToken->execute([$instanceId, password_hash($plainToken, PASSWORD_DEFAULT), substr($plainToken, -4)]);
            flash('token_plain', $plainToken);
        }

        flash('success', 'Instância salva com sucesso.');
        redirect('/admin/instances');
    }

    public function delete($id) {
        requireLogin();
        verify_csrf();
        $db = (new Database())->connect();
        $db->prepare('DELETE FROM instances WHERE id = ?')->execute([(int)$id]);
        flash('success', 'Instância removida com sucesso.');
        redirect('/admin/instances');
    }

    private function getEnabledModuleSlugs(PDO $db, $instanceId) {
        $stmt = $db->prepare("
            SELECT m.slug
            FROM instance_module_access ima
            JOIN modules m ON m.id = ima.module_id
            WHERE ima.instance_id = ? AND ima.is_enabled = 1
            ORDER BY m.slug ASC
        ");
        $stmt->execute([$instanceId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function syncModuleAccess(PDO $db, $instanceId, $slug, $enabled) {
        $stmtModule = $db->prepare('SELECT id FROM modules WHERE slug = ? LIMIT 1');
        $stmtModule->execute([$slug]);
        $moduleId = (int)$stmtModule->fetchColumn();
        if (!$moduleId) {
            return;
        }

        $stmtAccessCheck = $db->prepare('SELECT id FROM instance_module_access WHERE instance_id = ? AND module_id = ?');
        $stmtAccessCheck->execute([$instanceId, $moduleId]);
        $accessId = $stmtAccessCheck->fetchColumn();
        if ($accessId) {
            $stmtAccess = $db->prepare('UPDATE instance_module_access SET is_enabled = ? WHERE id = ?');
            $stmtAccess->execute([$enabled ? 1 : 0, $accessId]);
        } else {
            $stmtAccess = $db->prepare('INSERT INTO instance_module_access (instance_id, module_id, is_enabled) VALUES (?, ?, ?)');
            $stmtAccess->execute([$instanceId, $moduleId, $enabled ? 1 : 0]);
        }
    }

    private function hasActiveToken(PDO $db, $instanceId) {
        $stmt = $db->prepare('SELECT id FROM instance_tokens WHERE instance_id = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$instanceId]);
        return (bool)$stmt->fetchColumn();
    }
}
PHP;

$files['src/views/admin/instances/index.php'] = <<<'PHP'
<?php include __DIR__ . '/../../layout/header.php'; ?>
<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h1 class="h3 mb-1">Instâncias Clientes</h1>
        <p class="text-muted mb-0">Cadastre cada instalação que poderá consumir módulos da central.</p>
    </div>
</div>

<?php if ($rawToken): ?>
    <div class="alert alert-warning">
        <div class="fw-semibold">Token gerado agora</div>
        <div class="small mb-2">Guarde este valor. Ele não será exibido novamente.</div>
        <code><?= htmlspecialchars($rawToken) ?></code>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><?= empty($editing) ? 'Nova Instância' : 'Editar Instância' ?></div>
            <div class="card-body">
                <form action="/admin/instances" method="POST">
                    <?= csrf_field() ?>
                    <?php if (!empty($editing['id'])): ?>
                        <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($editing['name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Código</label>
                        <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($editing['code'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL Base</label>
                        <input type="url" name="base_url" class="form-control" value="<?= htmlspecialchars($editing['base_url'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ambiente</label>
                        <select name="environment" class="form-select">
                            <option value="production" <?= ($editing['environment'] ?? '') === 'production' ? 'selected' : '' ?>>Produção</option>
                            <option value="staging" <?= ($editing['environment'] ?? '') === 'staging' ? 'selected' : '' ?>>Homologação</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($editing['status'] ?? '') !== 'inactive' ? 'selected' : '' ?>>Ativa</option>
                            <option value="inactive" <?= ($editing['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inativa</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($editing['notes'] ?? '') ?></textarea>
                    </div>
                    <?php $editingModules = $editing['module_slugs'] ?? []; ?>
                    <div class="border rounded p-3 mb-3">
                        <div class="fw-semibold mb-2">Módulos liberados para esta instância</div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="module_manuals" id="module_manuals" <?= empty($editing) || in_array('manuals', $editingModules, true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="module_manuals">Manuais em vídeo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="module_global_settings" id="module_global_settings" <?= empty($editing) || in_array('global_settings', $editingModules, true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="module_global_settings">Configurações globais</label>
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="rotate_token" id="rotate_token">
                        <label class="form-check-label" for="rotate_token">Gerar/rotacionar token da API</label>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Salvar Instância</button>
                        <?php if (!empty($editing)): ?>
                            <a href="/admin/instances" class="btn btn-outline-secondary">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Instâncias Cadastradas</div>
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Código</th>
                            <th>Token</th>
                            <th>Módulos</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($instances)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Nenhuma instância cadastrada.</td></tr>
                        <?php else: ?>
                            <?php foreach ($instances as $instance): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($instance['name']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($instance['base_url']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($instance['code']) ?></td>
                                    <td>****<?= htmlspecialchars($instance['token_last4']) ?></td>
                                    <td><?= htmlspecialchars(implode(', ', $instance['modules'] ?: [])) ?></td>
                                    <td class="text-end">
                                        <a href="/admin/instances?edit=<?= (int)$instance['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form action="/admin/instances/delete/<?= (int)$instance['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Deseja remover esta instância?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../layout/footer.php'; ?>
PHP;

$files['src/views/layout/header.php'] = <<<'PHP'
<?php $successMessage = consume_flash('success'); ?>
<?php $errorMessage = consume_flash('error'); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php if (isLoggedIn()): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/admin/dashboard"><?= APP_NAME ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link <?= current_admin_path('/admin/dashboard') ?>" href="/admin/dashboard">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link <?= current_admin_path('/admin/instances') ?>" href="/admin/instances">Instâncias</a></li>
                    <li class="nav-item"><a class="nav-link <?= current_admin_path('/admin/manuals') ?>" href="/admin/manuals">Manuais</a></li>
                    <li class="nav-item"><a class="nav-link <?= current_admin_path('/admin/global-settings') ?>" href="/admin/global-settings">Configurações Globais</a></li>
                </ul>
                <span class="navbar-text text-white me-3"><?= htmlspecialchars($_SESSION['central_user_name'] ?? '') ?></span>
                <a href="/admin/logout" class="btn btn-outline-light btn-sm">Sair</a>
            </div>
        </div>
    </nav>
<?php endif; ?>
<main class="container py-4">
    <?php if ($successMessage): ?>
        <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>
PHP;

$files['src/controllers/GlobalSettingsController.php'] = <<<'PHP'
<?php

class GlobalSettingsController {
    public function index() {
        requireLogin();
        $release = (new ReleaseBuilderService())->getLatestPublished('global_settings');
        view('admin/global_settings/index', [
            'form' => $this->getFormData(),
            'release' => $release
        ]);
    }

    public function save() {
        requireLogin();
        verify_csrf();

        $phone = trim((string)($_POST['church_phone'] ?? ''));
        $email = trim((string)($_POST['church_email'] ?? ''));
        $about = trim((string)($_POST['church_about_text'] ?? ''));
        $socialLinks = $this->buildSocialLinks($_POST);

        $this->saveSetting('church_phone', $phone);
        $this->saveSetting('church_email', $email);
        $this->saveSetting('church_about_text', $about);
        $this->saveSetting('church_social_links', json_encode($socialLinks, JSON_UNESCAPED_UNICODE));

        flash('success', 'Configurações globais salvas com sucesso.');
        redirect('/admin/global-settings');
    }

    public function publish() {
        requireLogin();
        verify_csrf();

        $release = (new ReleaseBuilderService())->publishGlobalSettings((int)$_SESSION['central_user_id']);
        flash('success', 'Configurações globais publicadas na versão ' . $release['version'] . '.');
        redirect('/admin/global-settings');
    }

    private function getFormData() {
        $rawSocials = $this->getSetting('church_social_links', json_encode([], JSON_UNESCAPED_UNICODE));
        $decodedSocials = json_decode($rawSocials, true);
        if (!is_array($decodedSocials)) {
            $decodedSocials = [];
        }
        $socials = [];
        foreach ($decodedSocials as $item) {
            if (!empty($item['platform']) && isset($item['url'])) {
                $socials[$item['platform']] = $item['url'];
            }
        }

        return [
            'church_phone' => $this->getSetting('church_phone', ''),
            'church_email' => $this->getSetting('church_email', ''),
            'church_about_text' => $this->getSetting('church_about_text', ''),
            'socials' => $socials
        ];
    }

    private function buildSocialLinks(array $data) {
        $platforms = ['facebook', 'instagram', 'youtube', 'whatsapp', 'tiktok', 'telegram', 'linkedin', 'x-twitter'];
        $socialLinks = [];
        foreach ($platforms as $platform) {
            $url = trim((string)($data['social_' . $platform] ?? ''));
            if ($url !== '') {
                $socialLinks[] = [
                    'platform' => $platform,
                    'url' => $url
                ];
            }
        }
        return $socialLinks;
    }

    private function getSetting($key, $default = '') {
        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT setting_value FROM central_global_settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    }

    private function saveSetting($key, $value) {
        $db = (new Database())->connect();
        $stmt = $db->prepare('UPDATE central_global_settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = ?');
        $stmt->execute([$value, $key]);
        if ($stmt->rowCount() > 0) {
            return;
        }

        $insert = $db->prepare('INSERT INTO central_global_settings (setting_key, setting_value, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP)');
        try {
            $insert->execute([$key, $value]);
        } catch (Exception $e) {
            $stmt->execute([$value, $key]);
        }
    }
}
PHP;

$files['src/views/admin/global_settings/index.php'] = <<<'PHP'
<?php include __DIR__ . '/../../layout/header.php'; ?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Configurações Globais</h1>
        <p class="text-muted mb-0">Gerencie dados institucionais compartilhados entre as instâncias clientes.</p>
    </div>
    <form action="/admin/global-settings/publish" method="POST">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-cloud-upload-alt me-1"></i> Publicar Módulo
        </button>
    </form>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Dados institucionais</div>
            <div class="card-body">
                <form action="/admin/global-settings" method="POST">
                    <?= csrf_field() ?>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Telefone da igreja</label>
                            <input type="text" name="church_phone" class="form-control" value="<?= htmlspecialchars($form['church_phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-mail da igreja</label>
                            <input type="email" name="church_email" class="form-control" value="<?= htmlspecialchars($form['church_email'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Texto "Quem Somos"</label>
                        <textarea name="church_about_text" class="form-control" rows="6"><?= htmlspecialchars($form['church_about_text'] ?? '') ?></textarea>
                    </div>
                    <div class="border rounded p-3 mb-3">
                        <div class="fw-semibold mb-3">Redes sociais compartilhadas</div>
                        <?php
                        $socialFields = [
                            'facebook' => 'Facebook',
                            'instagram' => 'Instagram',
                            'youtube' => 'YouTube',
                            'whatsapp' => 'WhatsApp',
                            'tiktok' => 'TikTok',
                            'telegram' => 'Telegram',
                            'linkedin' => 'LinkedIn',
                            'x-twitter' => 'X / Twitter'
                        ];
                        ?>
                        <div class="row g-3">
                            <?php foreach ($socialFields as $key => $label): ?>
                                <div class="col-md-6">
                                    <label class="form-label"><?= htmlspecialchars($label) ?></label>
                                    <input type="url" name="social_<?= htmlspecialchars($key) ?>" class="form-control" value="<?= htmlspecialchars($form['socials'][$key] ?? '') ?>" placeholder="https://...">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Salvar Configurações
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">Publicação atual</div>
            <div class="card-body">
                <?php if (empty($release)): ?>
                    <div class="text-muted">Ainda não há nenhuma versão publicada para este módulo.</div>
                <?php else: ?>
                    <div class="mb-2"><strong>Versão:</strong> <?= (int)$release['version'] ?></div>
                    <div class="mb-2"><strong>Checksum:</strong> <?= htmlspecialchars($release['checksum']) ?></div>
                    <div class="mb-0"><strong>Publicado em:</strong> <?= htmlspecialchars($release['published_at']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">Escopo do módulo</div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Telefone e e-mail institucionais</li>
                    <li>Texto "Quem Somos"</li>
                    <li>Redes sociais exibidas no site</li>
                    <li>Payload pronto para sincronização nas instâncias clientes</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../layout/footer.php'; ?>
PHP;

$files['database/migrations/20260405_120000_create_global_settings_module.php'] = <<<'PHP'
<?php

class CreateGlobalSettingsModule {
    public function up($db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            $db->exec("
                CREATE TABLE IF NOT EXISTS central_global_settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    setting_key VARCHAR(120) NOT NULL UNIQUE,
                    setting_value LONGTEXT NULL,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            $columns = $db->query("SHOW COLUMNS FROM instances LIKE 'last_global_settings_sync_at'")->fetchAll(PDO::FETCH_ASSOC);
            if (empty($columns)) {
                $db->exec("ALTER TABLE instances ADD COLUMN last_global_settings_sync_at DATETIME NULL AFTER last_manual_sync_at");
            }
        } else {
            $db->exec("
                CREATE TABLE IF NOT EXISTS central_global_settings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    setting_key TEXT NOT NULL UNIQUE,
                    setting_value TEXT NULL,
                    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");

            $columns = $db->query("PRAGMA table_info(instances)")->fetchAll(PDO::FETCH_ASSOC);
            $hasColumn = false;
            foreach ($columns as $column) {
                if (($column['name'] ?? '') === 'last_global_settings_sync_at') {
                    $hasColumn = true;
                    break;
                }
            }
            if (!$hasColumn) {
                $db->exec("ALTER TABLE instances ADD COLUMN last_global_settings_sync_at TEXT NULL");
            }
        }

        $defaults = [
            'church_phone' => '',
            'church_email' => '',
            'church_about_text' => '',
            'church_social_links' => json_encode([], JSON_UNESCAPED_UNICODE),
        ];

        $stmt = $db->prepare('INSERT INTO central_global_settings (setting_key, setting_value) VALUES (?, ?)');
        foreach ($defaults as $key => $value) {
            try {
                $stmt->execute([$key, $value]);
            } catch (Exception $e) {
            }
        }

        $moduleStmt = $db->prepare('INSERT INTO modules (slug, name, is_active) VALUES (?, ?, 1)');
        try {
            $moduleStmt->execute(['global_settings', 'Configurações Globais']);
        } catch (Exception $e) {
        }
    }

    public function down($db) {
        $db->exec('DROP TABLE IF EXISTS central_global_settings');
    }
}
PHP;

foreach ($files as $relativePath => $content) {
    writeCentralFile($root, $relativePath, $content);
}

echo "Central atualizada com o módulo global_settings." . PHP_EOL;
