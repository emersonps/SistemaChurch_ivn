<?php

$root = 'D:/SistemaChurch_Central';

function writeCentralInstanceFile($root, $relativePath, $content) {
    $fullPath = $root . '/' . str_replace('\\', '/', $relativePath);
    $directory = dirname($fullPath);
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
    file_put_contents($fullPath, $content);
}

$files = [];

$files['src/controllers/GlobalSettingsController.php'] = <<<'PHP'
<?php

class GlobalSettingsController {
    public function index() {
        requireLogin();
        $db = (new Database())->connect();
        $instances = $db->query("SELECT id, name, code, status FROM instances ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $selectedInstanceId = !empty($_GET['instance_id']) ? (int)$_GET['instance_id'] : (int)($instances[0]['id'] ?? 0);
        $selectedInstance = $selectedInstanceId ? $this->getInstance($selectedInstanceId) : null;

        view('admin/global_settings/index', [
            'instances' => $instances,
            'selectedInstanceId' => $selectedInstanceId,
            'selectedInstance' => $selectedInstance,
            'form' => $selectedInstanceId ? $this->getFormData($selectedInstanceId, $selectedInstance) : null
        ]);
    }

    public function save() {
        requireLogin();
        verify_csrf();

        $instanceId = (int)($_POST['instance_id'] ?? 0);
        if ($instanceId <= 0) {
            flash('error', 'Selecione uma instância para salvar as configurações.');
            redirect('/admin/global-settings');
        }

        $instance = $this->getInstance($instanceId);
        $churchName = trim((string)($_POST['church_name'] ?? ''));
        $churchAlias = strtoupper(trim((string)($_POST['church_alias'] ?? '')));
        $churchLogoUrl = $this->handleLogoUpload($instanceId, $instance, $this->getSetting($instanceId, 'church_logo_url', ''));
        $phone = trim((string)($_POST['church_phone'] ?? ''));
        $email = trim((string)($_POST['church_email'] ?? ''));
        $about = trim((string)($_POST['church_about_text'] ?? ''));
        $socialLinks = $this->buildSocialLinks($_POST);

        $this->saveSetting($instanceId, 'church_name', $churchName !== '' ? $churchName : ($instance['name'] ?? ''));
        $this->saveSetting($instanceId, 'church_alias', $churchAlias !== '' ? $churchAlias : ($instance['code'] ?? ''));
        $this->saveSetting($instanceId, 'church_logo_url', $churchLogoUrl);
        $this->saveSetting($instanceId, 'church_phone', $phone);
        $this->saveSetting($instanceId, 'church_email', $email);
        $this->saveSetting($instanceId, 'church_about_text', $about);
        $this->saveSetting($instanceId, 'church_social_links', json_encode($socialLinks, JSON_UNESCAPED_UNICODE));

        flash('success', 'Configurações institucionais da instância salvas com sucesso.');
        redirect('/admin/global-settings?instance_id=' . $instanceId);
    }

    private function getFormData($instanceId, array $instance = null) {
        $rawSocials = $this->getSetting($instanceId, 'church_social_links', json_encode([], JSON_UNESCAPED_UNICODE));
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
            'church_name' => $this->getSetting($instanceId, 'church_name', $instance['name'] ?? ''),
            'church_alias' => $this->getSetting($instanceId, 'church_alias', $instance['code'] ?? ''),
            'church_logo_url' => $this->normalizeStoredLogoUrl($this->getSetting($instanceId, 'church_logo_url', '')),
            'church_phone' => $this->getSetting($instanceId, 'church_phone', ''),
            'church_email' => $this->getSetting($instanceId, 'church_email', ''),
            'church_about_text' => $this->getSetting($instanceId, 'church_about_text', ''),
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

    private function getSetting($instanceId, $key, $default = '') {
        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT setting_value FROM instance_global_settings WHERE instance_id = ? AND setting_key = ?');
        $stmt->execute([$instanceId, $key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    }

    private function getInstance($instanceId) {
        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT id, name, code, status FROM instances WHERE id = ? LIMIT 1');
        $stmt->execute([$instanceId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function handleLogoUpload($instanceId, array $instance = null, $currentLogoUrl = '') {
        if (!isset($_FILES['church_logo_file']) || !is_array($_FILES['church_logo_file'])) {
            return $this->normalizeStoredLogoUrl($currentLogoUrl);
        }

        $file = $_FILES['church_logo_file'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $this->normalizeStoredLogoUrl($currentLogoUrl);
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            flash('error', 'Não foi possível enviar a logo da instância.');
            redirect('/admin/global-settings?instance_id=' . $instanceId);
        }

        $extension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'webp'];
        if (!in_array($extension, $allowedExtensions, true)) {
            flash('error', 'A logo deve estar nos formatos PNG, JPG, JPEG ou WEBP.');
            redirect('/admin/global-settings?instance_id=' . $instanceId);
        }

        $uploadDir = $this->publicUploadsBasePath();
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $instanceCode = strtolower(preg_replace('/[^a-zA-Z0-9_-]+/', '-', (string)($instance['code'] ?? ('instance-' . $instanceId))));
        $fileName = trim($instanceCode, '-');
        if ($fileName === '') {
            $fileName = 'instance-' . $instanceId;
        }
        $fileName .= '_' . time() . '.' . $extension;
        $destination = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            flash('error', 'Não foi possível salvar a logo enviada.');
            redirect('/admin/global-settings?instance_id=' . $instanceId);
        }

        $this->deleteLocalLogoFile($currentLogoUrl);

        return $this->publicUploadsBaseUrl() . '/' . $fileName;
    }

    private function deleteLocalLogoFile($logoUrl) {
        if (!$this->isLocalCentralFileUrl($logoUrl)) {
            return;
        }

        $relativePath = parse_url($logoUrl, PHP_URL_PATH) ?: $logoUrl;
        $fullPath = dirname(__DIR__, 2) . '/public' . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function normalizeStoredLogoUrl($logoUrl) {
        $logoUrl = trim((string)$logoUrl);
        if ($logoUrl === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $logoUrl)) {
            return $logoUrl;
        }

        if ($logoUrl[0] === '/') {
            return $logoUrl;
        }

        return '/' . ltrim($logoUrl, '/');
    }

    private function isLocalCentralFileUrl($logoUrl) {
        $logoUrl = trim((string)$logoUrl);
        if ($logoUrl === '') {
            return false;
        }

        $path = parse_url($logoUrl, PHP_URL_PATH) ?: $logoUrl;
        return strpos($path, '/uploads/instance-branding/') === 0;
    }

    private function publicUploadsBasePath() {
        return dirname(__DIR__, 2) . '/public/uploads/instance-branding';
    }

    private function publicUploadsBaseUrl() {
        return '/uploads/instance-branding';
    }

    private function saveSetting($instanceId, $key, $value) {
        $db = (new Database())->connect();
        $stmt = $db->prepare('UPDATE instance_global_settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE instance_id = ? AND setting_key = ?');
        $stmt->execute([$value, $instanceId, $key]);
        if ($stmt->rowCount() > 0) {
            return;
        }

        $insert = $db->prepare('INSERT INTO instance_global_settings (instance_id, setting_key, setting_value, updated_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)');
        try {
            $insert->execute([$instanceId, $key, $value]);
        } catch (Exception $e) {
            $stmt->execute([$value, $instanceId, $key]);
        }
    }
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
        [$instance, $error] = (new ApiAuthService())->authenticate('global_settings');
        if ($error) {
            $this->json(['error' => $error], 401);
        }

        $payload = $this->buildInstanceGlobalSettingsPayload($instance['id']);
        if (empty($payload['settings'])) {
            $this->log($instance['id'], 'global_settings', 'error', 'Nenhuma configuração institucional cadastrada para esta instância.');
            $this->json(['error' => 'Nenhuma configuração institucional cadastrada para esta instância.'], 404);
        }

        $meta = $this->buildPayloadMeta($payload);
        $this->touchInstance($instance['id'], false, false);
        $this->log($instance['id'], 'global_settings', 'success', 'Consulta de versão do módulo global_settings.');
        $this->json([
            'module' => 'global-settings',
            'version' => $meta['version'],
            'checksum' => $meta['checksum'],
            'published_at' => $meta['published_at']
        ]);
    }

    public function globalSettingsExport() {
        [$instance, $error] = (new ApiAuthService())->authenticate('global_settings');
        if ($error) {
            $this->json(['error' => $error], 401);
        }

        $payload = $this->buildInstanceGlobalSettingsPayload($instance['id']);
        if (empty($payload['settings'])) {
            $this->log($instance['id'], 'global_settings', 'error', 'Nenhuma configuração institucional cadastrada para esta instância.');
            $this->json(['error' => 'Nenhuma configuração institucional cadastrada para esta instância.'], 404);
        }

        $meta = $this->buildPayloadMeta($payload);
        $this->touchInstance($instance['id'], false, true);
        $this->log($instance['id'], 'global_settings', 'success', 'Exportação do módulo global_settings entregue.');
        $this->json([
            'module' => 'global-settings',
            'version' => $meta['version'],
            'checksum' => $meta['checksum'],
            'published_at' => $meta['published_at'],
            'payload' => $payload
        ]);
    }

    public function billingsVersion() {
        [$instance, $error] = (new ApiAuthService())->authenticate();
        if ($error) {
            $this->json(['error' => $error], 401);
        }

        $payload = $this->buildInstanceBillingsPayload($instance['id']);
        $meta = $this->buildPayloadMeta($payload);
        $this->touchInstance($instance['id'], false, false);
        $this->log($instance['id'], 'billings', 'success', 'Consulta de versão do módulo de cobranças.');
        $this->json([
            'module' => 'billings',
            'version' => $meta['version'],
            'checksum' => $meta['checksum'],
            'published_at' => $meta['published_at']
        ]);
    }

    public function billingsExport() {
        [$instance, $error] = (new ApiAuthService())->authenticate();
        if ($error) {
            $this->json(['error' => $error], 401);
        }

        $payload = $this->buildInstanceBillingsPayload($instance['id']);
        $meta = $this->buildPayloadMeta($payload);
        $this->touchInstance($instance['id'], false, false);
        $this->log($instance['id'], 'billings', 'success', 'Exportação do módulo de cobranças entregue.');
        $this->json([
            'module' => 'billings',
            'version' => $meta['version'],
            'checksum' => $meta['checksum'],
            'published_at' => $meta['published_at'],
            'payload' => $payload
        ]);
    }

    public function billingsImport() {
        [$instance, $error] = (new ApiAuthService())->authenticate();
        if ($error) {
            $this->json(['error' => $error], 401);
        }

        $raw = file_get_contents('php://input');
        $decoded = json_decode((string)$raw, true);
        $payments = $decoded['payments'] ?? null;
        if (!is_array($payments)) {
            $this->log($instance['id'], 'billings', 'error', 'Payload inválido recebido para importação de cobranças.');
            $this->json(['error' => 'Payload inválido para importação de cobranças.'], 422);
        }

        $db = (new Database())->connect();
        $db->beginTransaction();

        try {
            $referenceMonths = [];
            $selectExisting = $db->prepare('SELECT id FROM instance_billings WHERE instance_id = ? AND reference_month = ? LIMIT 1');
            $updateExisting = $db->prepare('UPDATE instance_billings SET status = ?, amount = ?, due_date = ?, payment_date = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $insertNew = $db->prepare('INSERT INTO instance_billings (instance_id, reference_month, status, amount, due_date, payment_date, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');

            foreach ($payments as $payment) {
                $referenceMonth = trim((string)($payment['reference_month'] ?? ''));
                if (!preg_match('/^\d{4}-\d{2}$/', $referenceMonth)) {
                    continue;
                }

                $status = trim((string)($payment['status'] ?? 'pending'));
                if (!in_array($status, ['pending', 'paid', 'overdue'], true)) {
                    $status = 'pending';
                }

                $amount = (float)($payment['amount'] ?? 59.99);
                if ($amount <= 0) {
                    $amount = 59.99;
                }

                $dueDate = $this->normalizeNullableDateTime($payment['due_date'] ?? null, $referenceMonth . '-05 00:00:00');
                $paymentDate = $this->normalizeNullableDateTime($payment['payment_date'] ?? null, null);

                $selectExisting->execute([$instance['id'], $referenceMonth]);
                $existingId = $selectExisting->fetchColumn();

                if ($existingId) {
                    $updateExisting->execute([$status, $amount, $dueDate, $paymentDate, $existingId]);
                } else {
                    $insertNew->execute([$instance['id'], $referenceMonth, $status, $amount, $dueDate, $paymentDate]);
                }

                $referenceMonths[] = $referenceMonth;
            }

            $referenceMonths = array_values(array_unique($referenceMonths));
            if (empty($referenceMonths)) {
                $db->prepare('DELETE FROM instance_billings WHERE instance_id = ?')->execute([$instance['id']]);
            } else {
                $placeholders = implode(',', array_fill(0, count($referenceMonths), '?'));
                $params = array_merge([$instance['id']], $referenceMonths);
                $stmtDelete = $db->prepare("DELETE FROM instance_billings WHERE instance_id = ? AND reference_month NOT IN ($placeholders)");
                $stmtDelete->execute($params);
            }

            $db->commit();
            $this->log($instance['id'], 'billings', 'success', 'Cobranças importadas da instância com sucesso.');
            $this->json([
                'status' => 'ok',
                'message' => 'Cobranças importadas para a central com sucesso.',
                'count' => count($referenceMonths)
            ]);
        } catch (Exception $e) {
            $db->rollBack();
            $this->log($instance['id'], 'billings', 'error', 'Falha ao importar cobranças: ' . $e->getMessage());
            $this->json(['error' => 'Falha ao importar cobranças da instância.'], 500);
        }
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

    private function buildInstanceGlobalSettingsPayload($instanceId) {
        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT setting_key, setting_value, updated_at FROM instance_global_settings WHERE instance_id = ? ORDER BY setting_key ASC');
        $stmt->execute([$instanceId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $instanceStmt = $db->prepare('SELECT name, code, updated_at FROM instances WHERE id = ? LIMIT 1');
        $instanceStmt->execute([$instanceId]);
        $instance = $instanceStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $settings = [];
        $updatedAt = null;
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
            if ($updatedAt === null || strtotime($row['updated_at']) > strtotime($updatedAt)) {
                $updatedAt = $row['updated_at'];
            }
        }

        if (empty($settings['church_name']) && !empty($instance['name'])) {
            $settings['church_name'] = $instance['name'];
        }

        if (empty($settings['church_alias']) && !empty($instance['code'])) {
            $settings['church_alias'] = strtoupper($instance['code']);
        }

        if (!isset($settings['church_logo_url'])) {
            $settings['church_logo_url'] = '';
        }
        $settings['church_logo_url'] = $this->normalizeLogoUrl($settings['church_logo_url']);

        if (empty($updatedAt) && !empty($instance['updated_at'])) {
            $updatedAt = $instance['updated_at'];
        } elseif (!empty($instance['updated_at']) && strtotime($instance['updated_at']) > strtotime($updatedAt)) {
            $updatedAt = $instance['updated_at'];
        }

        return [
            'settings' => $settings,
            'updated_at' => $updatedAt
        ];
    }

    private function buildInstanceBillingsPayload($instanceId) {
        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT reference_month, status, amount, due_date, payment_date, updated_at FROM instance_billings WHERE instance_id = ? ORDER BY reference_month ASC');
        $stmt->execute([$instanceId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $payments = [];
        $updatedAt = null;
        foreach ($rows as $row) {
            $payments[] = [
                'reference_month' => $row['reference_month'],
                'status' => $row['status'],
                'amount' => (float)$row['amount'],
                'due_date' => $row['due_date'],
                'payment_date' => $row['payment_date']
            ];

            if ($updatedAt === null || strtotime($row['updated_at']) > strtotime($updatedAt)) {
                $updatedAt = $row['updated_at'];
            }
        }

        if ($updatedAt === null) {
            $updatedAt = date('Y-m-d H:i:s');
        }

        return [
            'payments' => $payments,
            'updated_at' => $updatedAt
        ];
    }

    private function buildPayloadMeta(array $payload) {
        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
        return [
            'version' => (string)strtotime($payload['updated_at'] ?? date('Y-m-d H:i:s')),
            'checksum' => hash('sha256', $payloadJson),
            'published_at' => $payload['updated_at'] ?? date('Y-m-d H:i:s')
        ];
    }

    private function normalizeNullableDateTime($value, $default = null) {
        $value = trim((string)$value);
        if ($value === '') {
            return $default;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return $default;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function normalizeLogoUrl($logoUrl) {
        $logoUrl = trim((string)$logoUrl);
        if ($logoUrl === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $logoUrl)) {
            return $logoUrl;
        }

        if ($logoUrl[0] === '/') {
            return $logoUrl;
        }

        return '/' . ltrim($logoUrl, '/');
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

$files['src/controllers/BillingController.php'] = <<<'PHP'
<?php

class BillingController {
    public function index() {
        requireLogin();
        $db = (new Database())->connect();
        $instances = $db->query('SELECT id, name, code, status FROM instances ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
        $selectedInstanceId = !empty($_GET['instance_id']) ? (int)$_GET['instance_id'] : (int)($instances[0]['id'] ?? 0);
        $overviewRows = $this->buildOverviewRows($db, $instances);

        $payments = [];
        $summary = [
            'total' => 0,
            'pending' => 0,
            'overdue' => 0,
            'paid' => 0,
            'next_due' => null
        ];

        if ($selectedInstanceId > 0) {
            $payments = $this->getPayments($db, $selectedInstanceId);
            $summary = $this->buildSummary($payments);
        }

        view('admin/billing/index', [
            'instances' => $instances,
            'selectedInstanceId' => $selectedInstanceId,
            'overviewRows' => $overviewRows,
            'payments' => $payments,
            'summary' => $summary
        ]);
    }

    public function generate() {
        requireLogin();
        verify_csrf();

        $db = (new Database())->connect();
        $instanceId = (int)($_POST['instance_id'] ?? 0);
        $month = trim((string)($_POST['month'] ?? ''));
        $status = trim((string)($_POST['status'] ?? 'pending'));
        $amount = (float)($_POST['amount'] ?? 59.99);
        $dueDay = (int)($_POST['due_day'] ?? 5);

        if ($instanceId <= 0 || !$this->instanceExists($db, $instanceId)) {
            flash('error', 'Selecione uma instância válida para gerar a cobrança.');
            redirect('/admin/billing');
        }

        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            flash('error', 'Informe um mês de referência válido.');
            redirect('/admin/billing?instance_id=' . $instanceId);
        }

        if ($amount <= 0) {
            $amount = 59.99;
        }

        if (!in_array($status, ['pending', 'paid', 'overdue'], true)) {
            $status = 'pending';
        }

        if ($dueDay < 1 || $dueDay > 31) {
            $dueDay = 5;
        }

        $dueDate = $this->buildDueDate($month, $dueDay);
        $paymentDate = $status === 'paid' ? date('Y-m-d H:i:s') : null;

        $stmt = $db->prepare('SELECT id FROM instance_billings WHERE instance_id = ? AND reference_month = ? LIMIT 1');
        $stmt->execute([$instanceId, $month]);
        $existingId = $stmt->fetchColumn();

        if ($existingId) {
            $update = $db->prepare('UPDATE instance_billings SET status = ?, amount = ?, due_date = ?, payment_date = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $update->execute([$status, $amount, $dueDate, $paymentDate, $existingId]);
        } else {
            $insert = $db->prepare('INSERT INTO instance_billings (instance_id, reference_month, status, amount, due_date, payment_date, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
            $insert->execute([$instanceId, $month, $status, $amount, $dueDate, $paymentDate]);
        }

        if ($status === 'paid') {
            $this->ensureNextMonthCharge($db, $instanceId, $month, $amount, $dueDate);
        }

        flash('success', 'Cobrança salva com sucesso.');
        redirect('/admin/billing?instance_id=' . $instanceId);
    }

    public function update() {
        requireLogin();
        verify_csrf();

        $db = (new Database())->connect();
        $id = (int)($_POST['id'] ?? 0);
        $instanceId = (int)($_POST['instance_id'] ?? 0);
        $referenceMonth = trim((string)($_POST['reference_month'] ?? ''));
        $status = trim((string)($_POST['status'] ?? 'pending'));
        $dueDateInput = trim((string)($_POST['due_date'] ?? ''));
        $amount = (float)($_POST['amount'] ?? 59.99);

        if ($id <= 0 || $instanceId <= 0 || !preg_match('/^\d{4}-\d{2}$/', $referenceMonth)) {
            flash('error', 'Dados inválidos para atualizar a cobrança.');
            redirect('/admin/billing' . ($instanceId > 0 ? '?instance_id=' . $instanceId : ''));
        }

        if (!in_array($status, ['pending', 'paid', 'overdue'], true)) {
            $status = 'pending';
        }

        if ($amount <= 0) {
            $amount = 59.99;
        }

        $stmt = $db->prepare('SELECT * FROM instance_billings WHERE id = ? AND instance_id = ? LIMIT 1');
        $stmt->execute([$id, $instanceId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payment) {
            flash('error', 'Cobrança não encontrada para a instância selecionada.');
            redirect('/admin/billing?instance_id=' . $instanceId);
        }

        $duplicate = $db->prepare('SELECT COUNT(*) FROM instance_billings WHERE instance_id = ? AND reference_month = ? AND id <> ?');
        $duplicate->execute([$instanceId, $referenceMonth, $id]);
        if ((int)$duplicate->fetchColumn() > 0) {
            flash('error', 'Já existe uma cobrança para este mês de referência nesta instância.');
            redirect('/admin/billing?instance_id=' . $instanceId);
        }

        $storedDueDate = $payment['due_date'] ?? null;
        if ($status === 'paid') {
            $resolvedDueDate = $storedDueDate ?: $this->buildDueDate($referenceMonth, 5);
            $paymentDate = date('Y-m-d H:i:s');
        } else {
            if ($dueDateInput !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDateInput)) {
                $resolvedDueDate = $dueDateInput . ' 00:00:00';
            } elseif (!empty($storedDueDate)) {
                $resolvedDueDate = date('Y-m-d 00:00:00', strtotime($storedDueDate));
            } else {
                $resolvedDueDate = $this->buildDueDate($referenceMonth, 5);
            }
            $paymentDate = null;
        }

        $update = $db->prepare('UPDATE instance_billings SET reference_month = ?, status = ?, amount = ?, due_date = ?, payment_date = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $update->execute([$referenceMonth, $status, $amount, $resolvedDueDate, $paymentDate, $id]);

        if ($status === 'paid') {
            $this->ensureNextMonthCharge($db, $instanceId, $referenceMonth, $amount, $resolvedDueDate);
        }

        flash('success', 'Cobrança atualizada com sucesso.');
        redirect('/admin/billing?instance_id=' . $instanceId);
    }

    public function delete($id) {
        requireLogin();
        verify_csrf();

        $instanceId = (int)($_POST['instance_id'] ?? 0);
        $db = (new Database())->connect();
        $db->prepare('DELETE FROM instance_billings WHERE id = ?')->execute([(int)$id]);

        flash('success', 'Cobrança removida com sucesso.');
        redirect('/admin/billing' . ($instanceId > 0 ? '?instance_id=' . $instanceId : ''));
    }

    private function getPayments(PDO $db, $instanceId) {
        $stmt = $db->prepare('SELECT * FROM instance_billings WHERE instance_id = ? ORDER BY reference_month DESC');
        $stmt->execute([$instanceId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $today = date('Y-m-d');
        foreach ($rows as &$row) {
            $row = $this->normalizePaymentRow($row, $today);
        }
        unset($row);

        return $rows;
    }

    private function normalizePaymentRow(array $payment, $today) {
        $effectiveDueDate = !empty($payment['due_date']) ? date('Y-m-d', strtotime($payment['due_date'])) : ($payment['reference_month'] . '-05');
        $payment['due_date_effective'] = $effectiveDueDate;
        $payment['due_date_display'] = date('d/m/Y', strtotime($effectiveDueDate));
        $payment['paid_at_display'] = !empty($payment['payment_date']) ? date('d/m/Y H:i', strtotime($payment['payment_date'])) : '-';

        if (($payment['status'] ?? '') === 'paid') {
            $payment['display_status'] = 'paid';
        } elseif (strtotime($effectiveDueDate) < strtotime($today)) {
            $payment['display_status'] = 'overdue';
        } else {
            $payment['display_status'] = $payment['status'] ?? 'pending';
        }

        return $payment;
    }

    private function buildSummary(array $payments) {
        $summary = [
            'total' => count($payments),
            'pending' => 0,
            'overdue' => 0,
            'paid' => 0,
            'next_due' => null
        ];

        foreach ($payments as $payment) {
            $status = $payment['display_status'] ?? ($payment['status'] ?? 'pending');
            if (isset($summary[$status])) {
                $summary[$status]++;
            }

            if ($status !== 'paid') {
                if ($summary['next_due'] === null || strtotime($payment['due_date_effective']) < strtotime($summary['next_due']['due_date_effective'])) {
                    $summary['next_due'] = $payment;
                }
            }
        }

        return $summary;
    }

    private function buildOverviewRows(PDO $db, array $instances) {
        $overviewRows = [];

        foreach ($instances as $instance) {
            $payments = $this->getPayments($db, (int)$instance['id']);
            $summary = $this->buildSummary($payments);
            $latestPayment = $payments[0] ?? null;

            $overviewRows[] = [
                'instance' => $instance,
                'summary' => $summary,
                'latest_payment' => $latestPayment
            ];
        }

        usort($overviewRows, function ($left, $right) {
            $leftDue = $left['summary']['next_due']['due_date_effective'] ?? '9999-12-31';
            $rightDue = $right['summary']['next_due']['due_date_effective'] ?? '9999-12-31';
            return strcmp($leftDue, $rightDue);
        });

        return $overviewRows;
    }

    private function ensureNextMonthCharge(PDO $db, $instanceId, $referenceMonth, $amount, $baseDueDate) {
        $nextMonthDate = DateTime::createFromFormat('!Y-m-d', $referenceMonth . '-01');
        if (!$nextMonthDate) {
            return;
        }

        $nextMonthDate->modify('+1 month');
        $nextMonth = $nextMonthDate->format('Y-m');

        $stmt = $db->prepare('SELECT COUNT(*) FROM instance_billings WHERE instance_id = ? AND reference_month = ?');
        $stmt->execute([$instanceId, $nextMonth]);
        if ((int)$stmt->fetchColumn() > 0) {
            return;
        }

        $dueDay = (int)date('d', strtotime($baseDueDate));
        if ($dueDay < 1 || $dueDay > 31) {
            $dueDay = 5;
        }

        $nextDueDate = $this->buildDueDate($nextMonth, $dueDay);
        $insert = $db->prepare("INSERT INTO instance_billings (instance_id, reference_month, status, amount, due_date, payment_date, created_at, updated_at) VALUES (?, ?, 'pending', ?, ?, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
        $insert->execute([$instanceId, $nextMonth, $amount, $nextDueDate]);
    }

    private function buildDueDate($referenceMonth, $dueDay) {
        $daysInMonth = (int)date('t', strtotime($referenceMonth . '-01'));
        $actualDay = min(max($dueDay, 1), $daysInMonth);
        return $referenceMonth . '-' . str_pad((string)$actualDay, 2, '0', STR_PAD_LEFT) . ' 00:00:00';
    }

    private function instanceExists(PDO $db, $instanceId) {
        $stmt = $db->prepare('SELECT id FROM instances WHERE id = ? LIMIT 1');
        $stmt->execute([$instanceId]);
        return (bool)$stmt->fetchColumn();
    }
}
PHP;

$files['src/helpers.php'] = <<<'PHP'
<?php

function view($view, $data = []) {
    extract($data);
    $path = __DIR__ . '/views/' . $view . '.php';
    if (!file_exists($path)) {
        die('View não encontrada: ' . $view);
    }
    require $path;
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function isLoggedIn() {
    return !empty($_SESSION['central_user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/admin/login');
    }
}

function csrf_token() {
    if (empty($_SESSION['csrf_tokens']) || !is_array($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    if (!in_array($_SESSION['csrf_token'], $_SESSION['csrf_tokens'], true)) {
        $_SESSION['csrf_tokens'][] = $_SESSION['csrf_token'];
    }

    $_SESSION['csrf_tokens'] = array_slice(array_values(array_unique($_SESSION['csrf_tokens'])), -5);

    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf() {
    $token = trim((string)($_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')));
    $token = preg_replace('/^\xEF\xBB\xBF+/', '', $token);
    $sessionToken = csrf_token();
    $validTokens = $_SESSION['csrf_tokens'] ?? [$sessionToken];

    if (!is_array($validTokens)) {
        $validTokens = [$sessionToken];
    }

    foreach ($validTokens as $validToken) {
        if (is_string($validToken) && $validToken !== '' && hash_equals($validToken, $token)) {
            return;
        }
    }

    http_response_code(419);
    exit('Token CSRF inválido.');
}

function flash($type, $message) {
    $_SESSION['flash_' . $type] = $message;
}

function consume_flash($type) {
    $key = 'flash_' . $type;
    if (!isset($_SESSION[$key])) {
        return null;
    }
    $message = $_SESSION[$key];
    unset($_SESSION[$key]);
    return $message;
}

function current_admin_path($path) {
    return strpos($_SERVER['REQUEST_URI'] ?? '', $path) === 0 ? 'active' : '';
}
PHP;

$files['src/views/admin/global_settings/index.php'] = <<<'PHP'
<?php include __DIR__ . '/../../layout/header.php'; ?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Configurações por Instância</h1>
        <p class="text-muted mb-0">Cada site possui seus próprios dados institucionais. Edite a instância desejada e a API entregará somente o payload dela.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Instâncias</div>
            <div class="list-group list-group-flush">
                <?php if (empty($instances)): ?>
                    <div class="list-group-item text-muted">Nenhuma instância cadastrada.</div>
                <?php else: ?>
                    <?php foreach ($instances as $instance): ?>
                        <a href="/admin/global-settings?instance_id=<?= (int)$instance['id'] ?>" class="list-group-item list-group-item-action <?= (int)$selectedInstanceId === (int)$instance['id'] ? 'active' : '' ?>">
                            <div class="fw-semibold"><?= htmlspecialchars($instance['name']) ?></div>
                            <div class="small <?= (int)$selectedInstanceId === (int)$instance['id'] ? 'text-white' : 'text-muted' ?>"><?= htmlspecialchars($instance['code']) ?></div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Dados institucionais da instância</div>
            <div class="card-body">
                <?php if (empty($selectedInstanceId) || empty($form)): ?>
                    <div class="text-muted">Selecione uma instância para editar suas informações.</div>
                <?php else: ?>
                    <form action="/admin/global-settings" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="hidden" name="instance_id" value="<?= (int)$selectedInstanceId ?>">

                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label">Nome completo da igreja</label>
                                <input type="text" name="church_name" class="form-control" value="<?= htmlspecialchars($form['church_name'] ?? '') ?>" placeholder="Ex: Igreja Vida Nova">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sigla</label>
                                <input type="text" name="church_alias" class="form-control text-uppercase" value="<?= htmlspecialchars($form['church_alias'] ?? '') ?>" placeholder="Ex: IVN">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Logo da igreja</label>
                                <?php if (!empty($form['church_logo_url'])): ?>
                                    <div class="mb-2">
                                        <img src="<?= htmlspecialchars($form['church_logo_url']) ?>" alt="Logo atual" style="max-height: 90px; max-width: 240px; object-fit: contain;" class="border rounded bg-light p-2">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="church_logo_file" class="form-control" accept=".png,.jpg,.jpeg,.webp">
                                <div class="form-text">Selecione a imagem da logo. Ao salvar, a central publica o arquivo e envia o endereço correto para a instância.</div>
                            </div>
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
                            <div class="fw-semibold mb-3">Redes sociais da instância</div>
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
                            <i class="fas fa-save me-1"></i> Salvar Configurações da Instância
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../layout/footer.php'; ?>
PHP;

$files['src/views/admin/billing/index.php'] = <<<'PHP'
<?php include __DIR__ . '/../../layout/header.php'; ?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Cobranças das Instâncias</h1>
        <p class="text-muted mb-0">Gerencie vencimentos, valide pagamentos e gere cobranças de cada igreja diretamente pela central.</p>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">Visão Geral das Igrejas</div>
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>Igreja</th>
                    <th>Status</th>
                    <th>Próximo Vencimento</th>
                    <th>Última Cobrança</th>
                    <th>Resumo</th>
                    <th class="text-end">Abrir</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($overviewRows)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Nenhuma instância cadastrada.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($overviewRows as $row): ?>
                        <?php
                        $instance = $row['instance'];
                        $instanceSummary = $row['summary'];
                        $nextDue = $instanceSummary['next_due'] ?? null;
                        $latestPayment = $row['latest_payment'] ?? null;
                        $statusBadge = 'secondary';
                        $statusLabel = 'Sem cobranças';

                        if ($nextDue) {
                            $statusLabel = $nextDue['display_status'] === 'overdue' ? 'Atrasado' : 'Em dia';
                            $statusBadge = $nextDue['display_status'] === 'overdue' ? 'danger' : 'warning';
                        } elseif (!empty($instanceSummary['paid'])) {
                            $statusLabel = 'Pago';
                            $statusBadge = 'success';
                        }
                        ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($instance['name']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($instance['code']) ?></div>
                            </td>
                            <td><span class="badge bg-<?= $statusBadge ?>"><?= htmlspecialchars($statusLabel) ?></span></td>
                            <td>
                                <?php if ($nextDue): ?>
                                    <div class="fw-semibold"><?= htmlspecialchars($nextDue['reference_month']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($nextDue['due_date_display']) ?></div>
                                <?php else: ?>
                                    <span class="text-muted">Nenhum pendente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($latestPayment): ?>
                                    <div class="fw-semibold"><?= htmlspecialchars($latestPayment['reference_month']) ?></div>
                                    <div class="small text-muted">R$ <?= number_format((float)$latestPayment['amount'], 2, ',', '.') ?></div>
                                <?php else: ?>
                                    <span class="text-muted">Sem histórico</span>
                                <?php endif; ?>
                            </td>
                            <td class="small">
                                <span class="me-2 text-warning">Pend.: <?= (int)($instanceSummary['pending'] ?? 0) ?></span>
                                <span class="me-2 text-danger">Atr.: <?= (int)($instanceSummary['overdue'] ?? 0) ?></span>
                                <span class="text-success">Pg.: <?= (int)($instanceSummary['paid'] ?? 0) ?></span>
                            </td>
                            <td class="text-end">
                                <a href="/admin/billing?instance_id=<?= (int)$instance['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i> Detalhar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="/admin/billing" class="row g-3 align-items-end">
            <div class="col-md-6 col-lg-4">
                <label class="form-label">Instância</label>
                <select name="instance_id" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($instances as $instance): ?>
                        <option value="<?= (int)$instance['id'] ?>" <?= (int)$selectedInstanceId === (int)$instance['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($instance['name']) ?> (<?= htmlspecialchars($instance['code']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 col-lg-8 text-md-end">
                <?php if (!empty($summary['next_due'])): ?>
                    <div class="small text-muted">Próximo vencimento: <strong><?= htmlspecialchars($summary['next_due']['reference_month']) ?></strong> em <strong><?= htmlspecialchars($summary['next_due']['due_date_display']) ?></strong></div>
                <?php else: ?>
                    <div class="small text-muted">Nenhuma cobrança pendente para a instância selecionada.</div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if ($selectedInstanceId > 0): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Total</div>
                    <div class="fs-4 fw-bold"><?= (int)($summary['total'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Pendentes</div>
                    <div class="fs-4 fw-bold text-warning"><?= (int)($summary['pending'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Atrasadas</div>
                    <div class="fs-4 fw-bold text-danger"><?= (int)($summary['overdue'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small">Pagas</div>
                    <div class="fs-4 fw-bold text-success"><?= (int)($summary['paid'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">Gerar ou Atualizar Cobrança</div>
        <div class="card-body">
            <form method="POST" action="/admin/billing/generate" class="row g-3 align-items-end">
                <?= csrf_field() ?>
                <input type="hidden" name="instance_id" value="<?= (int)$selectedInstanceId ?>">
                <div class="col-md-3">
                    <label class="form-label">Mês de Referência</label>
                    <input type="month" name="month" class="form-control" required value="<?= date('Y-m') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dia Venc.</label>
                    <input type="number" name="due_day" class="form-control" min="1" max="31" value="5" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Valor (R$)</label>
                    <input type="number" name="amount" class="form-control" min="0.01" step="0.01" value="59.99" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="pending">Pendente</option>
                        <option value="paid">Pago</option>
                        <option value="overdue">Atrasado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-file-invoice-dollar me-1"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">Histórico da Instância</div>
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mês Ref.</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Vencimento</th>
                        <th>Pagamento</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Nenhuma cobrança cadastrada para esta instância.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <?php $badgeClass = $payment['display_status'] === 'paid' ? 'success' : ($payment['display_status'] === 'overdue' ? 'danger' : 'warning'); ?>
                            <tr>
                                <td><?= (int)$payment['id'] ?></td>
                                <td><?= htmlspecialchars($payment['reference_month']) ?></td>
                                <td>R$ <?= number_format((float)$payment['amount'], 2, ',', '.') ?></td>
                                <td><span class="badge bg-<?= $badgeClass ?>"><?= htmlspecialchars(ucfirst($payment['display_status'])) ?></span></td>
                                <td><?= htmlspecialchars($payment['due_date_display']) ?></td>
                                <td><?= htmlspecialchars($payment['paid_at_display']) ?></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal<?= (int)$payment['id'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="/admin/billing/delete/<?= (int)$payment['id'] ?>" class="d-inline" onsubmit="return confirm('Deseja remover esta cobrança?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="instance_id" value="<?= (int)$selectedInstanceId ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <div class="modal fade" id="paymentModal<?= (int)$payment['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Atualizar Cobrança #<?= (int)$payment['id'] ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                        </div>
                                        <form method="POST" action="/admin/billing/update">
                                            <div class="modal-body">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= (int)$payment['id'] ?>">
                                                <input type="hidden" name="instance_id" value="<?= (int)$selectedInstanceId ?>">
                                                <div class="mb-3">
                                                    <label class="form-label">Mês de Referência</label>
                                                    <input type="month" name="reference_month" class="form-control" value="<?= htmlspecialchars($payment['reference_month']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="pending" <?= $payment['status'] === 'pending' ? 'selected' : '' ?>>Pendente</option>
                                                        <option value="paid" <?= $payment['status'] === 'paid' ? 'selected' : '' ?>>Pago</option>
                                                        <option value="overdue" <?= $payment['status'] === 'overdue' ? 'selected' : '' ?>>Atrasado</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Valor (R$)</label>
                                                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" value="<?= htmlspecialchars((string)$payment['amount']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Data de Vencimento</label>
                                                    <input type="date" name="due_date" class="form-control" value="<?= !empty($payment['due_date']) ? date('Y-m-d', strtotime($payment['due_date'])) : '' ?>">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary">Salvar alterações</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
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
                    <li class="nav-item"><a class="nav-link <?= current_admin_path('/admin/billing') ?>" href="/admin/billing">Cobranças</a></li>
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

$files['database/migrations/20260405_123000_create_instance_global_settings.php'] = <<<'PHP'
<?php

class CreateInstanceGlobalSettings {
    public function up($db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            $db->exec("
                CREATE TABLE IF NOT EXISTS instance_global_settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    instance_id INT NOT NULL,
                    setting_key VARCHAR(120) NOT NULL,
                    setting_value LONGTEXT NULL,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY uniq_instance_setting (instance_id, setting_key),
                    FOREIGN KEY (instance_id) REFERENCES instances(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } else {
            $db->exec("
                CREATE TABLE IF NOT EXISTS instance_global_settings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    instance_id INTEGER NOT NULL,
                    setting_key TEXT NOT NULL,
                    setting_value TEXT NULL,
                    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $db->exec("CREATE UNIQUE INDEX IF NOT EXISTS uniq_instance_setting ON instance_global_settings(instance_id, setting_key)");
        }

        $instances = $db->query('SELECT id FROM instances')->fetchAll(PDO::FETCH_COLUMN);
        $defaults = [
            'church_name' => '',
            'church_alias' => '',
            'church_logo_url' => '',
            'church_phone' => '',
            'church_email' => '',
            'church_about_text' => '',
            'church_social_links' => json_encode([], JSON_UNESCAPED_UNICODE),
        ];

        $stmt = $db->prepare('INSERT INTO instance_global_settings (instance_id, setting_key, setting_value) VALUES (?, ?, ?)');
        foreach ($instances as $instanceId) {
            foreach ($defaults as $key => $value) {
                try {
                    $stmt->execute([$instanceId, $key, $value]);
                } catch (Exception $e) {
                }
            }
        }
    }

    public function down($db) {
        $db->exec('DROP TABLE IF EXISTS instance_global_settings');
    }
}
PHP;

$files['database/migrations/20260405_140000_create_instance_billings.php'] = <<<'PHP'
<?php

class CreateInstanceBillings {
    public function up($db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            $db->exec("
                CREATE TABLE IF NOT EXISTS instance_billings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    instance_id INT NOT NULL,
                    reference_month VARCHAR(7) NOT NULL,
                    status VARCHAR(20) NOT NULL DEFAULT 'pending',
                    amount DECIMAL(10,2) NOT NULL DEFAULT 59.99,
                    due_date DATETIME NULL,
                    payment_date DATETIME NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY uniq_instance_billing_month (instance_id, reference_month),
                    FOREIGN KEY (instance_id) REFERENCES instances(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } else {
            $db->exec("
                CREATE TABLE IF NOT EXISTS instance_billings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    instance_id INTEGER NOT NULL,
                    reference_month TEXT NOT NULL,
                    status TEXT NOT NULL DEFAULT 'pending',
                    amount DECIMAL(10,2) NOT NULL DEFAULT 59.99,
                    due_date TEXT NULL,
                    payment_date TEXT NULL,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $db->exec("CREATE UNIQUE INDEX IF NOT EXISTS uniq_instance_billing_month ON instance_billings(instance_id, reference_month)");
        }
    }

    public function down($db) {
        $db->exec('DROP TABLE IF EXISTS instance_billings');
    }
}
PHP;

$files['public/index.php'] = <<<'PHP'
<?php

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (PHP_SAPI === 'cli-server') {
    $requestedFile = __DIR__ . $uri;
    if ($uri !== '/' && is_file($requestedFile)) {
        return false;
    }
}

ob_start();

require_once __DIR__ . '/../src/bootstrap.php';

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
} elseif ($uri === '/admin/billing') {
    (new BillingController())->index();
} elseif ($uri === '/admin/billing/generate' && $method === 'POST') {
    (new BillingController())->generate();
} elseif ($uri === '/admin/billing/update' && $method === 'POST') {
    (new BillingController())->update();
} elseif ($method === 'POST' && preg_match('#^/admin/billing/delete/(\d+)$#', $uri, $matches)) {
    (new BillingController())->delete($matches[1]);
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
} elseif ($uri === '/api/v1/billings/version') {
    (new SyncApiController())->billingsVersion();
} elseif ($uri === '/api/v1/billings/export') {
    (new SyncApiController())->billingsExport();
} elseif ($uri === '/api/v1/billings/import' && $method === 'POST') {
    (new SyncApiController())->billingsImport();
} else {
    http_response_code(404);
    echo 'Rota não encontrada.';
}
PHP;

foreach ($files as $relativePath => $content) {
    writeCentralInstanceFile($root, $relativePath, $content);
}

echo "Central ajustada para configurações institucionais por instância." . PHP_EOL;
