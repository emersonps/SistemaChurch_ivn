<?php
// src/helpers.php

function view($view, $data = []) {
    extract($data);
    $viewPath = __DIR__ . "/views/$view.php";
    if (file_exists($viewPath)) {
        require $viewPath;
    } else {
        die("View $view not found at $viewPath");
    }
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/admin/login');
    }
}

function hasPermission($permission) {
    if (!isLoggedIn()) return false;
    
    $userId = $_SESSION['user_id'];
    $role = $_SESSION['user_role'] ?? 'guest';
    
    // Developer override
    if ($role === 'developer') return true;

    // Cache database connection and queries to prevent "Too many connections" error
    static $db = null;
    static $userPermsCache = null;
    static $isOverride = null;

    if ($db === null) {
        $db = (new Database())->connect();
        
        // Fetch all permissions for this user at once
        $stmtAll = $db->prepare("SELECT permission_slug FROM user_permissions WHERE user_id = ?");
        $stmtAll->execute([$userId]);
        $userPermsCache = $stmtAll->fetchAll(PDO::FETCH_COLUMN);
        
        $isOverride = in_array('__override__', $userPermsCache);
    }

    // Admin nunca entra em modo override: mantém comportamento aditivo (papel + permissões do usuário)
    if ($role === 'admin') {
        $isOverride = false;
    }

    if ($isOverride) {
        // Absolute mode: only permissions explicitly saved in DB are valid
        if (in_array($permission, $userPermsCache)) return true;

        // Fallback for .view if has .manage in custom perms
        if (strpos($permission, '.view') !== false) {
            $managePermission = str_replace('.view', '.manage', $permission);
            if (in_array($managePermission, $userPermsCache)) return true;
        }
        
        return false; // In override mode, if not found, access denied
    }

    // 1. Check Role Permissions (from config) FIRST as base (Additive mode)
    static $rbac = null;
    if ($rbac === null) {
        $rbac = require __DIR__ . '/../config/rbac.php';
    }
    $rolePermissions = [];
    if (isset($rbac['roles'][$role])) {
        $rolePermissions = $rbac['roles'][$role]['permissions'];
    }
    
    // Se o usuário tem admin.manage (super admin legacy), permite tudo
    if (in_array('admin.manage', $rolePermissions)) {
        return true;
    }

    // If role has permission, return true
    if (in_array($permission, $rolePermissions)) {
        return true;
    }
    
    // Fallback: if checking for .view, allow if user has .manage (CHECK ROLE PERMISSIONS AGAIN)
    if (strpos($permission, '.view') !== false) {
        $managePermission = str_replace('.view', '.manage', $permission);
        if (in_array($managePermission, $rolePermissions)) {
            return true;
        }
    }
    
    // 2. Check Custom User Permissions in DB (Additive)
    if (in_array($permission, $userPermsCache)) {
        return true;
    }
    
    // Additive fallback for .view from custom perms
    if (strpos($permission, '.view') !== false) {
        $managePermission = str_replace('.view', '.manage', $permission);
        if (in_array($managePermission, $userPermsCache)) {
            return true;
        }
    }
    
    return false;
}

function requirePermission($permission) {
    if (!isLoggedIn()) {
        redirect('/admin/login');
    }
    
    // Check main permission
    if (hasPermission($permission)) {
        return true;
    }
    
    // Fallback: if checking for .view, allow if user has .manage
    if (strpos($permission, '.view') !== false) {
        $managePermission = str_replace('.view', '.manage', $permission);
        if (hasPermission($managePermission)) {
            return true;
        }
    }

    // Se estivermos no dashboard e ele não tem permissão, a gente quebra.
    // MAS NUNCA REDIRECIONAR PRO DASHBOARD SE ELE ESTIVER TENTANDO ACESSAR O DASHBOARD! (Loop infinito)
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, '/admin/dashboard') !== false) {
        // Se ele não pode ver o dashboard, joga pro login ou desloga
        redirect('/admin/logout');
    }

    http_response_code(403);
    echo "<h1>403 - Acesso Negado</h1>";
    echo "<p>Você não tem permissão para acessar este recurso: <strong>$permission</strong></p>";
    echo "<p><a href='/admin/dashboard'>Voltar ao Painel</a></p>";
    exit;
}

// CSRF Protection Functions
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
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $postToken = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        $postToken = trim((string)$postToken);
        $postToken = preg_replace('/^\xEF\xBB\xBF+/', '', $postToken);
        $sessionToken = csrf_token();
        $validTokens = $_SESSION['csrf_tokens'] ?? [$sessionToken];

        if (!is_array($validTokens)) {
            $validTokens = [$sessionToken];
        }

        $isValid = false;
        foreach ($validTokens as $validToken) {
            if (is_string($validToken) && $validToken !== '' && hash_equals($validToken, $postToken)) {
                $isValid = true;
                break;
            }
        }

        if (!$isValid) {
            // Debug Log (opcional, remover em produção se desejar)
            $logDir = dirname(__DIR__) . '/tmp/logs';
            if (!file_exists($logDir)) @mkdir($logDir, 0777, true);
            file_put_contents($logDir . '/csrf_error.log', date('Y-m-d H:i:s') . " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? '-') . " - POST: '$postToken' vs SESSION: '$sessionToken'\n", FILE_APPEND);
            
            http_response_code(403);
            die('
                <div style="font-family: sans-serif; text-align: center; padding: 50px;">
                    <h1 style="color: #d33;">Erro de Segurança (CSRF)</h1>
                    <p>O token de segurança da sua sessão é inválido ou expirou.</p>
                    <p>Isso geralmente acontece quando você fica muito tempo com a página aberta ou sua conexão mudou.</p>
                    <hr style="width: 50%; margin: 20px auto;">
                    <p>
                        <a href="javascript:history.back()" style="background: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Tentar Novamente</a>
                        &nbsp;
                        <a href="/admin/login" style="color: #666;">Fazer Login Novamente</a>
                    </p>
                </div>
            ');
        }
    }
}

function base_url($path = '') {
    // Adjust based on your server setup, simpler for local dev
    return $path;
}

/**
 * Gera o payload do Pix (Copia e Cola)
 * 
 * @param string $key Chave Pix
 * @param string $name Nome do Beneficiário
 * @param string $city Cidade do Beneficiário
 * @param string|float $amount Valor (opcional)
 * @param string $txid Identificador da Transação (opcional, default ***)
 * @return string Payload Pix
 */
function generatePixPayload($key, $name, $city, $amount = null, $txid = '***') {
    $payload = [];

    // 00 - Payload Format Indicator
    $payload[] = '000201';

    // 26 - Merchant Account Information
    $gui = '0014BR.GOV.BCB.PIX';
    $keyLen = str_pad(strlen($key), 2, '0', STR_PAD_LEFT);
    $merchantAccount = $gui . '01' . $keyLen . $key;
    $merchantAccountLen = str_pad(strlen($merchantAccount), 2, '0', STR_PAD_LEFT);
    $payload[] = '26' . $merchantAccountLen . $merchantAccount;

    // 52 - Merchant Category Code
    $payload[] = '52040000';

    // 53 - Transaction Currency (986 = BRL)
    $payload[] = '5303986';

    // 54 - Transaction Amount
    if ($amount) {
        $amount = number_format((float)$amount, 2, '.', '');
        $amountLen = str_pad(strlen($amount), 2, '0', STR_PAD_LEFT);
        $payload[] = '54' . $amountLen . $amount;
    }

    // 58 - Country Code
    $payload[] = '5802BR';

    // 59 - Merchant Name
    // Remove special chars and limit length
    $name = substr(preg_replace('/[^a-zA-Z0-9 ]/', '', removeAccents($name)), 0, 25);
    $nameLen = str_pad(strlen($name), 2, '0', STR_PAD_LEFT);
    $payload[] = '59' . $nameLen . $name;

    // 60 - Merchant City
    $city = substr(preg_replace('/[^a-zA-Z0-9 ]/', '', removeAccents($city)), 0, 15);
    $cityLen = str_pad(strlen($city), 2, '0', STR_PAD_LEFT);
    $payload[] = '60' . $cityLen . $city;

    // 62 - Additional Data Field Template
    $txidLen = str_pad(strlen($txid), 2, '0', STR_PAD_LEFT);
    $additionalData = '05' . $txidLen . $txid;
    $additionalDataLen = str_pad(strlen($additionalData), 2, '0', STR_PAD_LEFT);
    $payload[] = '62' . $additionalDataLen . $additionalData;

    // 63 - CRC16
    $payloadStr = implode('', $payload) . '6304';
    $crc = calculateCRC16($payloadStr);
    
    return $payloadStr . $crc;
}

function calculateCRC16($payload) {
    $crc = 0xFFFF;
    $polynomial = 0x1021;
    $data = $payload;
    
    for ($i = 0; $i < strlen($data); $i++) {
        $crc ^= (ord($data[$i]) << 8);
        for ($j = 0; $j < 8; $j++) {
            if (($crc & 0x8000) != 0) {
                $crc = (($crc << 1) ^ $polynomial);
            } else {
                $crc = $crc << 1;
            }
        }
    }
    
    return strtoupper(str_pad(dechex($crc & 0xFFFF), 4, '0', STR_PAD_LEFT));
}

function removeAccents($string) {
    return strtr((string)$string, [
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
        'ç' => 'c',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ñ' => 'n',
        'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
        'ý' => 'y', 'ÿ' => 'y',
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
        'Ç' => 'C',
        'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'Ñ' => 'N',
        'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
        'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
        'Ý' => 'Y'
    ]);
}

/**
 * Registra um acesso ou atividade no sistema
 */
function logAccess() {
    $db = (new Database())->connect();
    
    // Ignora logs para arquivos estáticos (css, js, imagens)
    $uri = $_SERVER['REQUEST_URI'];
    if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/i', $uri)) {
        return;
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $sessionId = session_id();

    // Determina o usuário atual
    $userId = null;
    $userName = 'Visitante';
    $userType = 'visitor';

    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        // Tenta pegar o nome da sessão, senão tenta 'username'
        $userName = $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Admin/Staff';
        $userType = 'admin';
    } elseif (isset($_SESSION['member_id'])) {
        $userId = $_SESSION['member_id'];
        $userName = $_SESSION['member_name'] ?? 'Membro';
        $userType = 'member';
    }

    // OTIMIZAÇÃO: Não logar 100% dos visitantes (apenas 10% de amostra)
    // Mas logar 100% de admins e membros logados
    if ($userType === 'visitor') {
        // Ignora requisições de bots comuns para não encher o banco
        if (preg_match('/(bot|crawl|spider|slurp)/i', $userAgent)) {
            return;
        }
        // Amostragem: 1 em cada 10 visitantes (10%)
        // Remova ou comente se quiser logar todos
        // if (rand(1, 10) !== 1) { return; }
    }

    try {
        // OTIMIZAÇÃO: Limpeza automática de logs muito antigos (probabilidade de 1%)
        // Remove logs de visitantes com mais de 7 dias
        if (rand(1, 100) === 1) {
            $cleanupSql = "DELETE FROM access_logs WHERE user_type = 'visitor' AND created_at < datetime('now', '-7 days')";
            if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
                $cleanupSql = "DELETE FROM access_logs WHERE user_type = 'visitor' AND created_at < NOW() - INTERVAL 7 DAY";
            }
            $db->exec($cleanupSql);
        }

        // Verifica se a sessão já tem um log recente (últimos 5 minutos) para a mesma URL
        // Isso evita criar múltiplas linhas para F5 ou navegação rápida na mesma página
        $stmt = $db->prepare("
            SELECT id FROM access_logs 
            WHERE session_id = ? 
            AND requested_url = ? 
            AND created_at > datetime('now', '-5 minutes')
            ORDER BY id DESC LIMIT 1
        ");
        
        // Ajuste datetime MySQL
        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $stmt = $db->prepare("
                SELECT id FROM access_logs 
                WHERE session_id = ? 
                AND requested_url = ? 
                AND created_at > NOW() - INTERVAL 5 MINUTE
                ORDER BY id DESC LIMIT 1
            ");
        }
        
        $stmt->execute([$sessionId, $uri]);
        $existingLog = $stmt->fetch();

        if ($existingLog) {
            // Atualiza apenas o last_activity
            $updateSql = "UPDATE access_logs SET last_activity = CURRENT_TIMESTAMP WHERE id = ?";
            if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
                $updateSql = "UPDATE access_logs SET last_activity = NOW() WHERE id = ?";
            }
            $updateStmt = $db->prepare($updateSql);
            $updateStmt->execute([$existingLog['id']]);
        } else {
            // Insere novo log
            $insertSql = "
                INSERT INTO access_logs 
                (user_id, user_name, user_type, ip_address, user_agent, requested_url, request_method, session_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $insertStmt = $db->prepare($insertSql);
            $insertStmt->execute([
                $userId, $userName, $userType, $ip, $userAgent, $uri, $method, $sessionId
            ]);
        }
    } catch (PDOException $e) {
        // Ignora erros silenciosamente para não quebrar a navegação se a tabela não existir
        error_log("Erro ao registrar log de acesso: " . $e->getMessage());
    }
}

/**
 * Registra a despesa do pagamento do sistema automaticamente.
 * 
 * @param string $month Mês de referência (YYYY-MM)
 * @param float|null $amount Valor do pagamento (opcional, default 59.99 se null)
 * @return bool Sucesso ou falha
 */
function registerSystemPaymentExpense($month, $amount = null) {
    try {
        $db = (new Database())->connect();
        
        // Se o valor não foi passado, tenta buscar do registro de pagamento
        if ($amount === null) {
            $stmtAmount = $db->prepare("SELECT amount FROM system_payments WHERE reference_month = ?");
            $stmtAmount->execute([$month]);
            if ($rowAmount = $stmtAmount->fetch()) {
                $amount = $rowAmount['amount'];
            }
        }
        
        // Valor padrão se ainda for nulo (fallback)
        if ($amount === null) {
            $amount = 59.99;
        }
        $hqId = null;
        $stmtHqType = $db->query("SELECT id FROM congregations WHERE LOWER(type) IN ('headquarters', 'sede', 'matriz', 'principal') ORDER BY id ASC LIMIT 1");
        if ($row = $stmtHqType->fetch()) {
            $hqId = $row['id'];
        }
        if (!$hqId) {
            $stmtHqName = $db->query("SELECT id FROM congregations WHERE name LIKE '%Sede%' OR name LIKE '%Matriz%' OR name LIKE '%Mãe%' OR name LIKE '%Mae%' ORDER BY id ASC LIMIT 1");
            if ($row = $stmtHqName->fetch()) {
                $hqId = $row['id'];
            }
        }
        if (!$hqId) {
            $stmtAny = $db->query("SELECT id FROM congregations ORDER BY id ASC LIMIT 1");
            if ($row = $stmtAny->fetch()) {
                $hqId = $row['id'];
            }
        }
        
        // DEBUG LOGGING
        $logMsg = date('Y-m-d H:i:s') . " - Tentando registrar despesa. Month: $month, HQ: " . ($hqId ?? 'NULL') . "\n";
        file_put_contents(__DIR__ . '/../../debug_payment.log', $logMsg, FILE_APPEND);

        // 2. Inserir Despesa
        $expenseDate = date('Y-m-d');
        
        // Format description as requested: "Pagamento Sistema - Ref: MM/YYYY (Venc: DD/MM/YYYY - Pago: DD/MM/YYYY)"
        $refDate = DateTime::createFromFormat('!Y-m-d', $month . '-01');
        $refMonthStr = $refDate ? $refDate->format('m/Y') : $month;
        
        // Assume due date is day 05 of the reference month
        $dueDateStr = '05/' . $refMonthStr;
        $paidDateStr = date('d/m/Y');
        
        $description = "Pagamento Sistema - Ref: $refMonthStr (Venc: $dueDateStr - Pago: $paidDateStr)";
        
        $notes = 'Pagamento automático registrado via módulo de Pagamentos do Sistema';
        $category = 'Contas Fixas';
        // $amount is already set above

        // Check duplicate
        $stmtCheckExp = $db->prepare("SELECT id FROM expenses WHERE description = ?");
        $stmtCheckExp->execute([$description]);
        if ($stmtCheckExp->fetch()) {
            $logMsg = date('Y-m-d H:i:s') . " - Despesa já existe. Pulando.\n";
            file_put_contents(__DIR__ . '/../../debug_payment.log', $logMsg, FILE_APPEND);
            return true; // Already registered
        } else {
            $stmtExpense = $db->prepare("INSERT INTO expenses (description, amount, expense_date, category, congregation_id, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtExpense->execute([
                $description,
                $amount,
                $expenseDate,
                $category,
                $hqId, 
                $notes
            ]);
            $logMsg = date('Y-m-d H:i:s') . " - Despesa inserida com SUCESSO. ID: " . $db->lastInsertId() . "\n";
            file_put_contents(__DIR__ . '/../../debug_payment.log', $logMsg, FILE_APPEND);
            return true;
        }
    } catch (Exception $e) {
        $logMsg = date('Y-m-d H:i:s') . " - ERRO ao inserir despesa: " . $e->getMessage() . "\n";
        file_put_contents(__DIR__ . '/../../debug_payment.log', $logMsg, FILE_APPEND);
        return false;
    }
}

function getCardLayouts() {
    $layouts = [
        'model_1' => ['name' => 'Padrão (Azul)', 'bg' => 'linear-gradient(135deg, #ffffff 40%, #f8f9fa 100%)', 'left' => '#0d6efd', 'top' => 'linear-gradient(to bottom, rgba(13,110,253,0.05), transparent)', 'bottom' => '#ffc107', 'text_top' => '#fff', 'back_top' => '#0d6efd', 'type' => 'color'],
        'model_2' => ['name' => 'Vermelho Clássico', 'bg' => 'linear-gradient(135deg, #ffffff 40%, #fff0f0 100%)', 'left' => '#dc3545', 'top' => 'linear-gradient(to bottom, rgba(220,53,69,0.05), transparent)', 'bottom' => '#212529', 'text_top' => '#fff', 'back_top' => '#dc3545', 'type' => 'color'],
        'model_3' => ['name' => 'Dourado Premium', 'bg' => 'linear-gradient(135deg, #fdfbf7 40%, #f0ece1 100%)', 'left' => '#212529', 'top' => 'linear-gradient(to bottom, rgba(33,37,41,0.05), transparent)', 'bottom' => '#ffc107', 'text_top' => '#fff', 'back_top' => '#212529', 'type' => 'color'],
        'model_4' => ['name' => 'Verde Esperança', 'bg' => 'linear-gradient(135deg, #ffffff 40%, #f0fdf4 100%)', 'left' => '#198754', 'top' => 'linear-gradient(to bottom, rgba(25,135,84,0.05), transparent)', 'bottom' => '#ffc107', 'text_top' => '#fff', 'back_top' => '#198754', 'type' => 'color'],
        'model_5' => ['name' => 'Roxo Real', 'bg' => 'linear-gradient(135deg, #ffffff 40%, #f8f0fc 100%)', 'left' => '#6f42c1', 'top' => 'linear-gradient(to bottom, rgba(111,66,193,0.05), transparent)', 'bottom' => '#ffc107', 'text_top' => '#fff', 'back_top' => '#6f42c1', 'type' => 'color'],
        'model_6' => ['name' => 'Laranja Vibrante', 'bg' => 'linear-gradient(135deg, #ffffff 40%, #fff6ec 100%)', 'left' => '#fd7e14', 'top' => 'linear-gradient(to bottom, rgba(253,126,20,0.05), transparent)', 'bottom' => '#212529', 'text_top' => '#fff', 'back_top' => '#fd7e14', 'type' => 'color'],
        'model_7' => ['name' => 'Rosa Suave', 'bg' => 'linear-gradient(135deg, #ffffff 40%, #fdf0f5 100%)', 'left' => '#d63384', 'top' => 'linear-gradient(to bottom, rgba(214,51,132,0.05), transparent)', 'bottom' => '#ffc107', 'text_top' => '#fff', 'back_top' => '#d63384', 'type' => 'color'],
        'model_8' => ['name' => 'Ciano Moderno', 'bg' => 'linear-gradient(135deg, #ffffff 40%, #f0fcfd 100%)', 'left' => '#0dcaf0', 'top' => 'linear-gradient(to bottom, rgba(13,202,240,0.05), transparent)', 'bottom' => '#212529', 'text_top' => '#000', 'back_top' => '#0dcaf0', 'type' => 'color'],
        'model_9' => ['name' => 'Cinza Executivo', 'bg' => 'linear-gradient(135deg, #ffffff 40%, #e9ecef 100%)', 'left' => '#6c757d', 'top' => 'linear-gradient(to bottom, rgba(108,117,125,0.05), transparent)', 'bottom' => '#0d6efd', 'text_top' => '#fff', 'back_top' => '#6c757d', 'type' => 'color'],
        'model_10' => ['name' => 'Preto Absoluto', 'bg' => 'linear-gradient(135deg, #ffffff 40%, #e0e0e0 100%)', 'left' => '#000000', 'top' => 'linear-gradient(to bottom, rgba(0,0,0,0.05), transparent)', 'bottom' => '#dc3545', 'text_top' => '#fff', 'back_top' => '#000000', 'type' => 'color'],
    ];

    // Scan the src/layoutcards directory for image models
    $dir = __DIR__ . '/layoutcards';
    if (is_dir($dir)) {
        $files = scandir($dir);
        $imageCount = 1;
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $key = 'img_model_' . $imageCount;
                
                // Get the image content and encode it to base64
                $imagePath = $dir . '/' . $file;
                $imageData = base64_encode(file_get_contents($imagePath));
                $mimeType = mime_content_type($imagePath);
                $base64Image = "data:{$mimeType};base64,{$imageData}";

                $layouts[$key] = [
                    'name' => 'Imagem: ' . pathinfo($file, PATHINFO_FILENAME),
                    'bg' => "url('{$base64Image}') center/cover no-repeat",
                    'left' => '#0d6efd', // Default fallback color
                    'top' => 'transparent', // Make top gradient transparent to show image
                    'bottom' => 'transparent', // Optional: hide bottom bar or set a neutral color
                    'text_top' => '#fff', // White text on dark bar
                    'back_top' => '#212529', // Almost black top bar for back when using image
                    'type' => 'image',
                    'file' => $file
                ];
                $imageCount++;
            }
        }
    }

    // Inject the admin's own uploaded custom card background, if one was saved
    try {
        $db = (new Database())->connect();
        $customImage = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'card_custom_image'")->fetchColumn();
    } catch (Exception $e) {
        $customImage = false;
    }
    if ($customImage && is_file(__DIR__ . '/../public/uploads/card_layouts/' . $customImage)) {
        $layouts['custom_upload'] = [
            'name' => 'Minha Imagem',
            'bg' => "url('/uploads/card_layouts/{$customImage}') center/cover no-repeat",
            'left' => '#0d6efd',
            'top' => 'transparent',
            'bottom' => 'transparent',
            'text_top' => '#fff',
            'back_top' => '#212529',
            'type' => 'image',
            'file' => $customImage,
            'custom' => true
        ];
    }

    return $layouts;
}

function getSystemSetting($key, $default = null) {
    try {
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    } catch (Exception $e) {
        return $default;
    }
}

function getChurchSocialIconOptions() {
    return [
        'facebook' => ['label' => 'Facebook', 'icon' => 'fab fa-facebook'],
        'instagram' => ['label' => 'Instagram', 'icon' => 'fab fa-instagram'],
        'youtube' => ['label' => 'YouTube', 'icon' => 'fab fa-youtube'],
        'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'fab fa-whatsapp'],
        'tiktok' => ['label' => 'TikTok', 'icon' => 'fab fa-tiktok'],
        'telegram' => ['label' => 'Telegram', 'icon' => 'fab fa-telegram'],
        'linkedin' => ['label' => 'LinkedIn', 'icon' => 'fab fa-linkedin'],
        'x-twitter' => ['label' => 'X / Twitter', 'icon' => 'fab fa-x-twitter'],
    ];
}

function getSignatureDocumentTypes() {
    return [
        'receipt' => 'Recibo de Dízimos e Ofertas',
        'member_card' => 'Cartão de Membro',
        'contribution_receipt' => 'Recibo de Contribuição',
        'expense_report' => 'Relatório de Despesas',
        'offering_receipt' => 'Recibo de Ofertas',
    ];
}

function appendVersionToUrl($url, $version) {
    $url = trim((string)$url);
    if ($url === '') {
        return '';
    }

    $separator = strpos($url, '?') === false ? '?' : '&';
    return $url . $separator . 'v=' . rawurlencode((string)$version);
}

function resolvePublicPathFromUrl($url) {
    $url = trim((string)$url);
    if ($url === '') {
        return null;
    }

    $path = parse_url($url, PHP_URL_PATH);
    if (!is_string($path) || $path === '' || $path[0] !== '/') {
        return null;
    }

    $candidate = dirname(__DIR__) . '/public' . str_replace('/', DIRECTORY_SEPARATOR, $path);
    return file_exists($candidate) ? $candidate : null;
}

function getChurchBrandingName($siteProfile = null) {
    if (!is_array($siteProfile)) {
        $siteProfile = getChurchSiteProfileSettings();
    }

    $name = trim((string)($siteProfile['name'] ?? ''));
    if ($name !== '') {
        return $name;
    }

    $alias = trim((string)($siteProfile['alias'] ?? ''));
    return $alias !== '' ? $alias : 'Igreja';
}

function getChurchBrandingAlias($siteProfile = null) {
    if (!is_array($siteProfile)) {
        $siteProfile = getChurchSiteProfileSettings();
    }

    $alias = trim((string)($siteProfile['alias'] ?? ''));
    if ($alias !== '') {
        return $alias;
    }

    return getChurchBrandingName($siteProfile);
}

function getChurchBrandingVersion($siteProfile = null) {
    if (!is_array($siteProfile)) {
        $siteProfile = getChurchSiteProfileSettings();
    }

    $logoUrl = trim((string)($siteProfile['logo_url'] ?? '/assets/img/logo.png'));
    $logoPath = resolvePublicPathFromUrl($logoUrl);
    $logoMtime = $logoPath ? (string)filemtime($logoPath) : 'remote';

    return substr(sha1(
        getChurchBrandingName($siteProfile) . '|' .
        getChurchBrandingAlias($siteProfile) . '|' .
        $logoUrl . '|' .
        $logoMtime
    ), 0, 12);
}

function getChurchLogoUrl($siteProfile = null, $versioned = false) {
    if (!is_array($siteProfile)) {
        $siteProfile = getChurchSiteProfileSettings();
    }

    $logoUrl = trim((string)($siteProfile['logo_url'] ?? '/assets/img/logo.png'));
    if ($logoUrl === '') {
        $logoUrl = '/assets/img/logo.png';
    }

    if (!$versioned) {
        return $logoUrl;
    }

    return appendVersionToUrl($logoUrl, getChurchBrandingVersion($siteProfile));
}

// Monta o payload "BR Code" (EMV) de uma chave PIX estática, com valor livre
// (o campo de valor é omitido de propósito para que o app do doador peça o valor).
function buildPixBrCode($pixKey, $merchantName, $merchantCity, $txid = null) {
    $emvField = function ($id, $value) {
        $len = str_pad(strlen($value), 2, '0', STR_PAD_LEFT);
        return $id . $len . $value;
    };

    $sanitize = function ($value) {
        $value = preg_replace('/[^A-Za-z0-9 ]/', '', (string)$value);
        return trim($value);
    };

    $merchantName = substr($sanitize($merchantName), 0, 25) ?: 'IGREJA';
    $merchantCity = substr($sanitize($merchantCity), 0, 15) ?: 'BRASIL';
    $txid = $txid ? substr(preg_replace('/[^A-Za-z0-9]/', '', $txid), 0, 25) : '***';

    $merchantAccountInfo = $emvField('00', 'br.gov.bcb.pix') . $emvField('01', trim($pixKey));

    $payload =
        $emvField('00', '01') .
        $emvField('26', $merchantAccountInfo) .
        $emvField('52', '0000') .
        $emvField('53', '986') .
        $emvField('58', 'BR') .
        $emvField('59', $merchantName) .
        $emvField('60', $merchantCity) .
        $emvField('62', $emvField('05', $txid));

    $payload .= '6304';
    $payload .= crc16Ccitt($payload);

    return $payload;
}

function crc16Ccitt($payload) {
    $polynomial = 0x1021;
    $result = 0xFFFF;

    for ($i = 0; $i < strlen($payload); $i++) {
        $result ^= (ord($payload[$i]) << 8);
        for ($j = 0; $j < 8; $j++) {
            if (($result & 0x8000) !== 0) {
                $result = (($result << 1) ^ $polynomial) & 0xFFFF;
            } else {
                $result = ($result << 1) & 0xFFFF;
            }
        }
    }

    return strtoupper(str_pad(dechex($result), 4, '0', STR_PAD_LEFT));
}

function getChurchManifestUrl($siteProfile = null) {
    if (!is_array($siteProfile)) {
        $siteProfile = getChurchSiteProfileSettings();
    }

    return appendVersionToUrl('/manifest.webmanifest', getChurchBrandingVersion($siteProfile));
}

function getChurchSiteProfileSettings() {
    $defaultSocials = [
        ['platform' => 'facebook', 'url' => ''],
        ['platform' => 'instagram', 'url' => ''],
        ['platform' => 'youtube', 'url' => ''],
    ];

    $rawSocials = getSystemSetting('church_social_links', json_encode($defaultSocials, JSON_UNESCAPED_UNICODE));
    $socials = json_decode($rawSocials, true);
    if (!is_array($socials)) {
        $socials = $defaultSocials;
    }

    $iconOptions = getChurchSocialIconOptions();
    $normalizedSocials = [];
    foreach ($socials as $social) {
        $platform = $social['platform'] ?? '';
        $url = trim($social['url'] ?? '');
        if ($platform === '' || !isset($iconOptions[$platform]) || $url === '') {
            continue;
        }
        $normalizedSocials[] = [
            'platform' => $platform,
            'label' => $iconOptions[$platform]['label'],
            'icon' => $iconOptions[$platform]['icon'],
            'url' => $url,
        ];
    }

    return [
        'name' => getSystemSetting('church_name', 'Igreja Vida Nova'),
        'alias' => getSystemSetting('church_alias', 'IVN'),
        'logo_url' => getSystemSetting('church_logo_url', '/assets/img/logo.png'),
        'address' => getSystemSetting('church_address', ''),
        'phone' => getSystemSetting('church_phone', '+55 (92) 99386-6290'),
        'email' => getSystemSetting('church_email', 'contato@ivn.com.br'),
        'about_text' => getSystemSetting(
            'church_about_text',
            'É uma comunidade cristã comprometida com a centralidade das Escrituras, a proclamação do Evangelho e a edificação de famílias firmadas na fé. Somos apaixonados por Jesus e pelas pessoas. Cremos no poder transformador da Palavra de Deus e trabalhamos para levar o evangelho a toda criatura, vivendo em comunhão, promovendo discipulado e servindo ao próximo com amor, responsabilidade e propósito.'
        ),
        'social_links' => $normalizedSocials,
        'show_example_banner' => getSystemSetting('show_example_banner', '0') === '1',
    ];
}

function getPermissionMenuDefinitions() {
    return [
        [
            'section' => 'Principal',
            'title' => 'Painel',
            'parent' => 'dashboard.view',
            'children' => []
        ],
        [
            'section' => 'Secretaria',
            'title' => 'Membros',
            'parent' => 'members.view',
            'children' => ['members.manage']
        ],
        [
            'section' => 'Secretaria',
            'title' => 'Congregações',
            'parent' => 'congregations.view',
            'children' => ['congregations.manage']
        ],
        [
            'section' => 'Secretaria',
            'title' => 'Eventos / Cultos',
            'parent' => 'events.view',
            'children' => ['events.manage']
        ],
        [
            'section' => 'Secretaria',
            'title' => 'Relatórios de Culto',
            'parent' => 'service_reports.view',
            'children' => ['service_reports.manage']
        ],
        [
            'section' => 'Secretaria',
            'title' => 'Estatísticas Gerais',
            'parent' => 'general_reports.view',
            'children' => []
        ],
        [
            'section' => 'Secretaria',
            'title' => 'Assinaturas',
            'parent' => 'signatures.view',
            'children' => ['signatures.manage']
        ],
        [
            'section' => 'Secretaria',
            'title' => 'Grupos / Células',
            'parent' => 'groups.view',
            'children' => ['groups.manage']
        ],
        [
            'section' => 'Secretaria',
            'title' => 'Galeria',
            'parent' => 'gallery.view',
            'children' => ['gallery.manage']
        ],
        [
            'section' => 'Secretaria',
            'title' => 'Banners',
            'parent' => 'banners.view',
            'children' => ['banners.manage']
        ],
        [
            'section' => 'Secretaria',
            'title' => 'Mural de Vídeos',
            'parent' => 'video_wall.view',
            'children' => ['video_wall.manage']
        ],
        [
            'section' => 'Secretaria',
            'title' => 'Escalas Litúrgicas',
            'parent' => 'liturgy_schedules.view',
            'children' => ['liturgy_schedules.manage']
        ],
        [
            'section' => 'Financeiro',
            'title' => 'Financeiro',
            'parent' => 'financial.view',
            'children' => ['financial.manage', 'financial_accounts.manage', 'financial_ofx.manage']
        ],
        [
            'section' => 'Financeiro',
            'title' => 'Pagamento do Sistema',
            'parent' => 'system_payments.view',
            'children' => ['system_payments.manage']
        ],
        [
            'section' => 'Financeiro',
            'title' => 'Campanhas',
            'parent' => 'campaigns.view',
            'children' => ['campaigns.manage']
        ],
        [
            'section' => 'Ensino',
            'title' => 'Escola Bíblica',
            'parent' => 'ebd.view',
            'children' => ['ebd.manage', 'ebd.lessons']
        ],
        [
            'section' => 'Ensino',
            'title' => 'Estudos',
            'parent' => 'studies.view',
            'children' => ['studies.manage']
        ],
        [
            'section' => 'Sistema',
            'title' => 'Contas / Usuários',
            'parent' => 'users.manage',
            'children' => ['users.view', 'permissions.manage']
        ],
        [
            'section' => 'Sistema',
            'title' => 'Configurações',
            'parent' => 'settings.view',
            'children' => ['settings.manage', 'settings.system.view', 'settings.layout.view', 'settings.card.view']
        ],
        [
            'section' => 'Sistema',
            'title' => 'Desenvolvedor',
            'parent' => 'developer.access',
            'children' => []
        ],
    ];
}

function getPermissionLabelFallback($slug) {
    $slug = str_replace(['.', '_'], ' ', $slug);
    return ucwords($slug);
}

function getAdminEditablePermissionSlugs() {
    return [
        'settings.view',
        'settings.manage',
        'settings.system.view',
        'settings.layout.view',
        'settings.card.view'
    ];
}

function buildPermissionGroups(array $permissions) {
    $catalog = [];
    foreach ($permissions as $permission) {
        if (!empty($permission['slug'])) {
            $catalog[$permission['slug']] = $permission;
        }
    }

    $groups = [];
    $assigned = [];

    foreach (getPermissionMenuDefinitions() as $definition) {
        $items = [];
        $slugs = array_merge([$definition['parent']], $definition['children']);
        foreach ($slugs as $slug) {
            if (isset($catalog[$slug])) {
                $item = $catalog[$slug];
                $item['is_parent'] = $slug === $definition['parent'];
                $items[] = $item;
                $assigned[$slug] = true;
            }
        }

        if (!empty($items)) {
            $groups[] = [
                'section' => $definition['section'],
                'title' => $definition['title'],
                'parent_slug' => $definition['parent'],
                'children_slugs' => $definition['children'],
                'items' => $items
            ];
        }
    }

    $miscItems = [];
    foreach ($permissions as $permission) {
        if (empty($assigned[$permission['slug'] ?? ''])) {
            $permission['is_parent'] = false;
            $miscItems[] = $permission;
        }
    }

    if (!empty($miscItems)) {
        $groups[] = [
            'section' => 'Outros',
            'title' => 'Outras Permissões',
            'parent_slug' => null,
            'children_slugs' => [],
            'items' => $miscItems
        ];
    }

    return $groups;
}

function normalizePermissionSelection(array $selectedPermissions) {
    $selected = array_values(array_unique(array_filter($selectedPermissions)));
    $selectedMap = array_fill_keys($selected, true);

    foreach (getPermissionMenuDefinitions() as $definition) {
        foreach ($definition['children'] as $childSlug) {
            if (isset($selectedMap[$childSlug]) && !empty($definition['parent'])) {
                $selectedMap[$definition['parent']] = true;
            }
        }
    }

    return array_keys($selectedMap);
}

function eventDateWeekdayName($dateValue) {
    $timestamp = strtotime((string)$dateValue);
    if ($timestamp === false) {
        return '';
    }
    $map = [
        'Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'
    ];
    return $map[(int)date('w', $timestamp)] ?? '';
}

function eventParseDateTime($dateValue) {
    $timestamp = strtotime((string)$dateValue);
    if ($timestamp === false) {
        return null;
    }
    return (new DateTimeImmutable())->setTimestamp($timestamp);
}

function eventGetDateTimes(array $event) {
    $itemsByKey = [];

    $push = function ($raw) use (&$itemsByKey) {
        $raw = trim((string)$raw);
        if ($raw === '' || strtotime($raw) === false) {
            return;
        }

        $hasTime = (bool)preg_match('/\b\d{1,2}:\d{2}\b/', $raw);
        $date = date('Y-m-d', strtotime($raw));
        $time = date('H:i', strtotime($raw));
        $key = $hasTime ? ($date . ' ' . $time) : $date;

        if (!isset($itemsByKey[$key])) {
            $itemsByKey[$key] = $key;
        }
    };

    $eventDates = trim((string)($event['event_dates'] ?? ''));
    if ($eventDates !== '') {
        $decoded = json_decode($eventDates, true);
        if (is_array($decoded)) {
            foreach ($decoded as $dt) {
                $push($dt);
            }
        }
    }

    $eventDate = trim((string)($event['event_date'] ?? ''));
    if ($eventDate !== '' && strpos($eventDate, '1970-01-01') !== 0) {
        $push($eventDate);
    }

    $items = array_values($itemsByKey);
    usort($items, function ($a, $b) {
        return strtotime($a) <=> strtotime($b);
    });

    return $items;
}

function eventGetDateBadges(array $event) {
    $items = eventGetDateTimes($event);
    $out = [];
    foreach ($items as $dt) {
        $hasTime = (bool)preg_match('/\b\d{1,2}:\d{2}\b/', (string)$dt);
        $time = $hasTime ? date('H:i', strtotime($dt)) : '';
        if ($time === '00:00') {
            $time = '';
        }
        $out[] = [
            'raw' => $dt,
            'date' => date('d/m/Y', strtotime($dt)),
            'time' => $time,
            'weekday' => eventDateWeekdayName($dt),
        ];
    }
    return $out;
}

function eventNextOccurrence(array $event, $now = null) {
    $now = $now instanceof DateTimeImmutable ? $now : new DateTimeImmutable('now');

    $items = eventGetDateTimes($event);
    foreach ($items as $dt) {
        $parsed = eventParseDateTime($dt);
        if ($parsed && $parsed >= $now) {
            return $parsed;
        }
    }

    $recurring = trim((string)($event['recurring_days'] ?? ''));
    if ($recurring !== '') {
        $days = json_decode($recurring, true);
        if (is_array($days) && !empty($days)) {
            $map = [
                'Domingo' => 0,
                'Segunda' => 1,
                'Terça' => 2,
                'Terca' => 2,
                'Quarta' => 3,
                'Quinta' => 4,
                'Sexta' => 5,
                'Sábado' => 6,
                'Sabado' => 6,
            ];
            $allowed = [];
            foreach ($days as $d) {
                $d = trim((string)$d);
                if ($d === '') continue;
                if (isset($map[$d])) $allowed[$map[$d]] = true;
            }

            if (!empty($allowed)) {
                $timeValue = '19:30';
                $timeFromEvent = trim((string)($event['event_date'] ?? ''));
                if ($timeFromEvent !== '' && strtotime($timeFromEvent) !== false) {
                    $timeValue = date('H:i', strtotime($timeFromEvent));
                }

                [$hour, $minute] = array_pad(explode(':', $timeValue), 2, '00');
                for ($i = 0; $i <= 14; $i++) {
                    $candidate = $now->modify('+' . $i . ' day')->setTime((int)$hour, (int)$minute);
                    if (!empty($allowed[(int)$candidate->format('w')])) {
                        if ($candidate >= $now) {
                            return $candidate;
                        }
                    }
                }
            }
        }
    }

    return null;
}

function eventHasFutureOccurrence(array $event, $now = null) {
    return eventNextOccurrence($event, $now) instanceof DateTimeImmutable;
}

/**
 * Multa (one-time, 2%) + juros de mora (1%/month, prorated per day overdue) on
 * a system_payments / instance_billings charge — the standard Brazilian boleto
 * late-fee convention. Computed fresh from $baseAmount/$dueDate/$today every
 * call (nothing is persisted), so the total always reflects "as of right now"
 * without needing a background job to keep a stored value in sync.
 */
function calculateOverdueCharge($baseAmount, $dueDate, $today = null) {
    $baseAmount = (float)$baseAmount;
    $today = $today ?? date('Y-m-d');

    $dueTs = strtotime(date('Y-m-d', strtotime($dueDate)));
    $todayTs = strtotime(date('Y-m-d', strtotime($today)));
    $daysOverdue = ($dueTs && $todayTs) ? (int)floor(($todayTs - $dueTs) / 86400) : 0;

    if ($daysOverdue <= 0) {
        return [
            'days_overdue' => 0,
            'late_fee' => 0.0,
            'interest' => 0.0,
            'total' => round($baseAmount, 2),
        ];
    }

    $lateFee = round($baseAmount * 0.02, 2);
    $interest = round($baseAmount * 0.01 * ($daysOverdue / 30), 2);

    return [
        'days_overdue' => $daysOverdue,
        'late_fee' => $lateFee,
        'interest' => $interest,
        'total' => round($baseAmount + $lateFee + $interest, 2),
    ];
}

// Allowlist-based HTML sanitizer for basic rich text (bold/italic/underline,
// color, size, alignment, line breaks) coming from the Quill editor. Strips
// any tag not on the allowlist (unwrapping its content instead of dropping
// it) and any attribute other than a filtered `style`, so a crafted POST
// bypassing the editor can't inject scripts/handlers into a public page.
function sanitizeBasicRichText($html) {
    $html = trim((string)$html);
    if ($html === '') {
        return '';
    }

    $allowedTags = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'span', 'div', 'ul', 'ol', 'li'];
    $allowedStyleProps = ['color', 'background-color', 'font-size', 'font-weight', 'font-style', 'text-decoration', 'text-align'];

    libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadHTML('<?xml encoding="UTF-8"><div id="__root__">' . $html . '</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();

    $root = $dom->getElementById('__root__');
    if (!$root) {
        return '';
    }

    $sanitizeNode = function ($node) use (&$sanitizeNode, $allowedTags, $allowedStyleProps) {
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                continue;
            }

            if ($child->nodeType !== XML_ELEMENT_NODE) {
                $node->removeChild($child);
                continue;
            }

            $tag = strtolower($child->nodeName);
            if (!in_array($tag, $allowedTags, true)) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            if ($child->hasAttributes()) {
                $styleValue = $child->getAttribute('style');
                $attrNames = [];
                foreach ($child->attributes as $attr) {
                    $attrNames[] = $attr->name;
                }
                foreach ($attrNames as $attrName) {
                    $child->removeAttribute($attrName);
                }

                if ($styleValue !== '') {
                    $cleanDeclarations = [];
                    foreach (explode(';', $styleValue) as $decl) {
                        $parts = explode(':', $decl, 2);
                        if (count($parts) !== 2) {
                            continue;
                        }
                        $prop = strtolower(trim($parts[0]));
                        $val = trim($parts[1]);
                        if (!in_array($prop, $allowedStyleProps, true)) {
                            continue;
                        }
                        if (preg_match('/url\s*\(|expression\s*\(|javascript:/i', $val)) {
                            continue;
                        }
                        if (!preg_match('/^[a-zA-Z0-9#%,.()\s\-]+$/', $val)) {
                            continue;
                        }
                        $cleanDeclarations[] = $prop . ': ' . $val;
                    }
                    if (!empty($cleanDeclarations)) {
                        $child->setAttribute('style', implode('; ', $cleanDeclarations));
                    }
                }
            }

            $sanitizeNode($child);
        }
    };

    $sanitizeNode($root);

    $result = '';
    foreach ($root->childNodes as $child) {
        $result .= $dom->saveHTML($child);
    }

    return trim($result);
}

// Extracts the 11-char video id from any common YouTube URL shape
// (watch?v=, youtu.be/, /embed/, /shorts/). Returns '' if it doesn't
// recognize the URL at all.
function extractYoutubeVideoId($url) {
    $url = trim((string)$url);
    if ($url === '') {
        return '';
    }

    if (preg_match('#(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})#', $url, $matches)) {
        return $matches[1];
    }

    return '';
}

function getVideoWallCategories() {
    try {
        $db = (new Database())->connect();
        $names = $db->query('SELECT name FROM video_wall_categories ORDER BY name ASC')->fetchAll(PDO::FETCH_COLUMN);
        return $names ?: ['Mensagens'];
    } catch (Throwable $e) {
        return ['Cultos', 'Mensagens', 'Louvores', 'Jovens', 'Eventos'];
    }
}

// Formats a naive "Y-m-d H:i:s" datetime (stored in the app's own
// timezone, America/Sao_Paulo) as an ISO 8601 string with an explicit UTC
// offset, so `new Date(...)` on the client resolves the same instant in
// time regardless of the visitor's own timezone.
function formatLivestreamScheduledAtIso($datetime) {
    $datetime = trim((string)$datetime);
    if ($datetime === '') {
        return '';
    }
    $timestamp = strtotime($datetime);
    if ($timestamp === false) {
        return '';
    }
    return date('Y-m-d\TH:i:sP', $timestamp);
}

// Formats a 'YYYY-MM' reference_month (campaign_installments,
// system_payments) as "Mês/Ano" in Portuguese, e.g. '2026-09' -> 'Setembro/2026'.
function formatReferenceMonth($yearMonth) {
    $parts = explode('-', (string)$yearMonth);
    if (count($parts) !== 2) {
        return (string)$yearMonth;
    }
    $months = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
    $month = (int)$parts[1];
    return ($months[$month] ?? $parts[1]) . '/' . $parts[0];
}

// Progress of a fundraising campaign toward its goal, based on the sum of
// paid installments. Percent is not capped here — callers that render a
// progress bar should clamp it to 100 for the bar width while still
// showing the real percent (a campaign can exceed its goal).
function getCampaignProgress($campaignId) {
    $db = (new Database())->connect();

    $stmt = $db->prepare('SELECT goal_amount FROM campaigns WHERE id = ?');
    $stmt->execute([$campaignId]);
    $goal = (float)$stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COALESCE(SUM(paid_amount), 0) FROM campaign_installments WHERE campaign_id = ? AND status = 'paid'");
    $stmt->execute([$campaignId]);
    $raised = (float)$stmt->fetchColumn();

    $percent = $goal > 0 ? ($raised / $goal) * 100 : 0;

    return [
        'goal' => $goal,
        'raised' => $raised,
        'percent' => $percent,
        'percent_display' => min(100, round($percent)),
    ];
}

// Lista curada de papéis litúrgicos que um modelo de escala pode ligar/desligar.
// As chaves são fixas (usadas como chave em roles_config/values_json); os labels
// são só o texto padrão sugerido — cada modelo pode reescrevê-los.
function getLiturgyScheduleRoleCatalog() {
    return [
        'dirigente' => 'Dirigente',
        'pregador' => 'Pregação',
        'portaria' => 'Portaria',
        'recepcao' => 'Recepção',
        'louvor' => 'Louvor/Ministração',
        'diacono' => 'Diácono de Plantão',
        'regencia' => 'Regência',
        'ebd' => 'EBD',
    ];
}

function getLiturgyScheduleWeekdayPt($dateStr) {
    $days = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
    $ts = strtotime((string)$dateStr);
    if ($ts === false) {
        return '';
    }
    return $days[(int)date('w', $ts)];
}

function getDevotionalVerses() {
        $devotionalVersesByTheme = [
            'Ansiedade' => [
                ['text' => 'Lançando sobre ele toda a vossa ansiedade, porque ele tem cuidado de vós.', 'reference' => '1 Pedro 5:7', 'testament' => 'Novo Testamento'],
                ['text' => 'Não andeis ansiosos por coisa alguma; em tudo, porém, sejam conhecidas diante de Deus as vossas petições.', 'reference' => 'Filipenses 4:6', 'testament' => 'Novo Testamento'],
                ['text' => 'No dia em que eu temer, hei de confiar em ti.', 'reference' => 'Salmo 56:3', 'testament' => 'Velho Testamento'],
                ['text' => 'Deixo-vos a paz, a minha paz vos dou; não se turbe o vosso coração, nem se atemorize.', 'reference' => 'João 14:27', 'testament' => 'Novo Testamento'],
                ['text' => 'Entregue o seu caminho ao Senhor; confie nele, e ele agirá.', 'reference' => 'Salmo 37:5', 'testament' => 'Velho Testamento'],
                ['text' => 'Quando a ansiedade já me dominava no íntimo, o teu consolo trouxe alívio à minha alma.', 'reference' => 'Salmo 94:19', 'testament' => 'Velho Testamento'],
                ['text' => 'O Senhor é bom, um refúgio em tempos de angústia. Ele protege os que nele confiam.', 'reference' => 'Naum 1:7', 'testament' => 'Velho Testamento'],
                ['text' => 'Venham a mim todos os que estão cansados e sobrecarregados, e eu lhes darei descanso.', 'reference' => 'Mateus 11:28', 'testament' => 'Novo Testamento'],
                ['text' => 'Tu conservarás em perfeita paz aquele cujo propósito é firme, porque em ti confia.', 'reference' => 'Isaías 26:3', 'testament' => 'Velho Testamento']
            ],
            'Fé' => [
                ['text' => 'Ora, a fé é a certeza daquilo que esperamos e a prova das coisas que não vemos.', 'reference' => 'Hebreus 11:1', 'testament' => 'Novo Testamento'],
                ['text' => 'Sem fé é impossível agradar a Deus.', 'reference' => 'Hebreus 11:6', 'testament' => 'Novo Testamento'],
                ['text' => 'Porque vivemos por fé, e não pelo que vemos.', 'reference' => '2 Coríntios 5:7', 'testament' => 'Novo Testamento'],
                ['text' => 'Se tiverdes fé do tamanho de um grão de mostarda, nada vos será impossível.', 'reference' => 'Mateus 17:20', 'testament' => 'Novo Testamento'],
                ['text' => 'O justo viverá pela sua fé.', 'reference' => 'Habacuque 2:4', 'testament' => 'Velho Testamento'],
                ['text' => 'Tudo é possível ao que crê.', 'reference' => 'Marcos 9:23', 'testament' => 'Novo Testamento'],
                ['text' => 'Confia no Senhor de todo o teu coração e não te estribes no teu próprio entendimento.', 'reference' => 'Provérbios 3:5', 'testament' => 'Velho Testamento'],
                ['text' => 'Peça-a, porém, com fé, em nada duvidando.', 'reference' => 'Tiago 1:6', 'testament' => 'Novo Testamento'],
                ['text' => 'Esperei confiantemente pelo Senhor, e ele se inclinou para mim e me ouviu.', 'reference' => 'Salmo 40:1', 'testament' => 'Velho Testamento']
            ],
            'Promessas' => [
                ['text' => 'Porque eu bem sei os planos que tenho para vós, diz o Senhor, planos de paz e não de mal.', 'reference' => 'Jeremias 29:11', 'testament' => 'Velho Testamento'],
                ['text' => 'Das promessas do Senhor nenhuma falhou; todas se cumpriram.', 'reference' => 'Josué 21:45', 'testament' => 'Velho Testamento'],
                ['text' => 'Fiel é o que vos chama, o qual também o fará.', 'reference' => '1 Tessalonicenses 5:24', 'testament' => 'Novo Testamento'],
                ['text' => 'Todas as promessas de Deus encontram nele o sim.', 'reference' => '2 Coríntios 1:20', 'testament' => 'Novo Testamento'],
                ['text' => 'Aquele que prometeu é fiel.', 'reference' => 'Hebreus 10:23', 'testament' => 'Novo Testamento'],
                ['text' => 'O céu e a terra passarão, mas as minhas palavras jamais passarão.', 'reference' => 'Mateus 24:35', 'testament' => 'Novo Testamento'],
                ['text' => 'A palavra do nosso Deus permanece eternamente.', 'reference' => 'Isaías 40:8', 'testament' => 'Velho Testamento'],
                ['text' => 'Bendito seja o Senhor, que deu repouso ao seu povo, segundo tudo o que prometera.', 'reference' => '1 Reis 8:56', 'testament' => 'Velho Testamento'],
                ['text' => 'Guardemos firme a confissão da esperança, sem vacilar.', 'reference' => 'Hebreus 10:23', 'testament' => 'Novo Testamento']
            ],
            'Força' => [
                ['text' => 'Posso todas as coisas naquele que me fortalece.', 'reference' => 'Filipenses 4:13', 'testament' => 'Novo Testamento'],
                ['text' => 'O Senhor é a minha força e o meu cântico.', 'reference' => 'Salmo 118:14', 'testament' => 'Velho Testamento'],
                ['text' => 'Os que esperam no Senhor renovarão as suas forças.', 'reference' => 'Isaías 40:31', 'testament' => 'Velho Testamento'],
                ['text' => 'Diga o fraco: Eu sou forte.', 'reference' => 'Joel 3:10', 'testament' => 'Velho Testamento'],
                ['text' => 'A minha graça te basta, porque o poder se aperfeiçoa na fraqueza.', 'reference' => '2 Coríntios 12:9', 'testament' => 'Novo Testamento'],
                ['text' => 'Sede fortes e revigore-se o vosso coração, vós todos que esperais no Senhor.', 'reference' => 'Salmo 31:24', 'testament' => 'Velho Testamento'],
                ['text' => 'Fortalecei-vos no Senhor e na força do seu poder.', 'reference' => 'Efésios 6:10', 'testament' => 'Novo Testamento'],
                ['text' => 'Deus é o que me cinge de força e aperfeiçoa o meu caminho.', 'reference' => 'Salmo 18:32', 'testament' => 'Velho Testamento'],
                ['text' => 'Não temas, porque eu sou contigo; eu te fortaleço e te ajudo.', 'reference' => 'Isaías 41:10', 'testament' => 'Velho Testamento']
            ],
            'Esperança' => [
                ['text' => 'Porque eu sei em quem tenho crido.', 'reference' => '2 Timóteo 1:12', 'testament' => 'Novo Testamento'],
                ['text' => 'Bendito o homem que confia no Senhor e cuja esperança é o Senhor.', 'reference' => 'Jeremias 17:7', 'testament' => 'Velho Testamento'],
                ['text' => 'Quero trazer à memória o que me pode dar esperança.', 'reference' => 'Lamentações 3:21', 'testament' => 'Velho Testamento'],
                ['text' => 'Alegrai-vos na esperança, sede pacientes na tribulação, perseverai na oração.', 'reference' => 'Romanos 12:12', 'testament' => 'Novo Testamento'],
                ['text' => 'Ora, o Deus de esperança vos encha de todo o gozo e paz no vosso crer.', 'reference' => 'Romanos 15:13', 'testament' => 'Novo Testamento'],
                ['text' => 'A esperança que se retarda deixa o coração doente, mas o desejo cumprido é árvore de vida.', 'reference' => 'Provérbios 13:12', 'testament' => 'Velho Testamento'],
                ['text' => 'Bom é aguardar a salvação do Senhor, e isso, em silêncio.', 'reference' => 'Lamentações 3:26', 'testament' => 'Velho Testamento'],
                ['text' => 'Temos esta esperança como âncora da alma, firme e segura.', 'reference' => 'Hebreus 6:19', 'testament' => 'Novo Testamento'],
                ['text' => 'Mas eu esperarei continuamente e te louvarei cada vez mais.', 'reference' => 'Salmo 71:14', 'testament' => 'Velho Testamento']
            ],
            'Paz' => [
                ['text' => 'Tu conservarás em perfeita paz aquele cujo propósito é firme, porque em ti confia.', 'reference' => 'Isaías 26:3', 'testament' => 'Velho Testamento'],
                ['text' => 'Deixo-vos a paz, a minha paz vos dou.', 'reference' => 'João 14:27', 'testament' => 'Novo Testamento'],
                ['text' => 'O Senhor dará força ao seu povo; o Senhor abençoará o seu povo com paz.', 'reference' => 'Salmo 29:11', 'testament' => 'Velho Testamento'],
                ['text' => 'Que a paz de Cristo seja o árbitro em vosso coração.', 'reference' => 'Colossenses 3:15', 'testament' => 'Novo Testamento'],
                ['text' => 'Grande paz têm os que amam a tua lei.', 'reference' => 'Salmo 119:165', 'testament' => 'Velho Testamento'],
                ['text' => 'Em paz me deito e logo pego no sono, porque, Senhor, só tu me fazes repousar seguro.', 'reference' => 'Salmo 4:8', 'testament' => 'Velho Testamento'],
                ['text' => 'A paz de Deus, que excede todo entendimento, guardará o vosso coração.', 'reference' => 'Filipenses 4:7', 'testament' => 'Novo Testamento'],
                ['text' => 'Bem-aventurados os pacificadores, porque serão chamados filhos de Deus.', 'reference' => 'Mateus 5:9', 'testament' => 'Novo Testamento'],
                ['text' => 'O fruto da justiça semeia-se em paz.', 'reference' => 'Tiago 3:18', 'testament' => 'Novo Testamento']
            ],
            'Salvação' => [
                ['text' => 'Porque Deus amou o mundo de tal maneira que deu o seu Filho unigênito.', 'reference' => 'João 3:16', 'testament' => 'Novo Testamento'],
                ['text' => 'Crê no Senhor Jesus e serás salvo, tu e tua casa.', 'reference' => 'Atos 16:31', 'testament' => 'Novo Testamento'],
                ['text' => 'O Senhor é a minha luz e a minha salvação; de quem terei medo?', 'reference' => 'Salmo 27:1', 'testament' => 'Velho Testamento'],
                ['text' => 'Todo aquele que invocar o nome do Senhor será salvo.', 'reference' => 'Romanos 10:13', 'testament' => 'Novo Testamento'],
                ['text' => 'Em nenhum outro há salvação.', 'reference' => 'Atos 4:12', 'testament' => 'Novo Testamento'],
                ['text' => 'Com alegria tirareis água das fontes da salvação.', 'reference' => 'Isaías 12:3', 'testament' => 'Velho Testamento'],
                ['text' => 'Pela graça sois salvos, mediante a fé.', 'reference' => 'Efésios 2:8', 'testament' => 'Novo Testamento'],
                ['text' => 'O Senhor desnudou o seu santo braço perante todas as nações; e todos verão a salvação do nosso Deus.', 'reference' => 'Isaías 52:10', 'testament' => 'Velho Testamento'],
                ['text' => 'Eu sou o caminho, e a verdade, e a vida; ninguém vem ao Pai senão por mim.', 'reference' => 'João 14:6', 'testament' => 'Novo Testamento']
            ],
            'Oração' => [
                ['text' => 'Orai sem cessar.', 'reference' => '1 Tessalonicenses 5:17', 'testament' => 'Novo Testamento'],
                ['text' => 'A oração de um justo é poderosa e eficaz.', 'reference' => 'Tiago 5:16', 'testament' => 'Novo Testamento'],
                ['text' => 'Buscai o Senhor enquanto se pode achar; invocai-o enquanto está perto.', 'reference' => 'Isaías 55:6', 'testament' => 'Velho Testamento'],
                ['text' => 'Clama a mim, e responder-te-ei.', 'reference' => 'Jeremias 33:3', 'testament' => 'Velho Testamento'],
                ['text' => 'Se permanecerdes em mim, e as minhas palavras permanecerem em vós, pedireis o que quiserdes.', 'reference' => 'João 15:7', 'testament' => 'Novo Testamento'],
                ['text' => 'Invoca-me no dia da angústia; eu te livrarei, e tu me glorificarás.', 'reference' => 'Salmo 50:15', 'testament' => 'Velho Testamento'],
                ['text' => 'Tudo quanto pedirdes em oração, crendo, recebereis.', 'reference' => 'Mateus 21:22', 'testament' => 'Novo Testamento'],
                ['text' => 'Eu amo o Senhor, porque ele ouve a minha voz e as minhas súplicas.', 'reference' => 'Salmo 116:1', 'testament' => 'Velho Testamento'],
                ['text' => 'Antes de clamarem, eu responderei.', 'reference' => 'Isaías 65:24', 'testament' => 'Velho Testamento']
            ],
            'Sabedoria' => [
                ['text' => 'Se, porém, algum de vós necessita de sabedoria, peça-a a Deus.', 'reference' => 'Tiago 1:5', 'testament' => 'Novo Testamento'],
                ['text' => 'O temor do Senhor é o princípio da sabedoria.', 'reference' => 'Provérbios 9:10', 'testament' => 'Velho Testamento'],
                ['text' => 'Entrega o teu caminho ao Senhor; confia nele, e o mais ele fará.', 'reference' => 'Salmo 37:5', 'testament' => 'Velho Testamento'],
                ['text' => 'A sabedoria do alto é primeiramente pura, depois pacífica, indulgente.', 'reference' => 'Tiago 3:17', 'testament' => 'Novo Testamento'],
                ['text' => 'Ensina-nos a contar os nossos dias, para que alcancemos coração sábio.', 'reference' => 'Salmo 90:12', 'testament' => 'Velho Testamento'],
                ['text' => 'Reconhece-o em todos os teus caminhos, e ele endireitará as tuas veredas.', 'reference' => 'Provérbios 3:6', 'testament' => 'Velho Testamento'],
                ['text' => 'A tua palavra é lâmpada para os meus pés e luz para o meu caminho.', 'reference' => 'Salmo 119:105', 'testament' => 'Velho Testamento'],
                ['text' => 'Quem ouve estas minhas palavras e as pratica será comparado a um homem prudente.', 'reference' => 'Mateus 7:24', 'testament' => 'Novo Testamento'],
                ['text' => 'Cristo Jesus se nos tornou da parte de Deus sabedoria.', 'reference' => '1 Coríntios 1:30', 'testament' => 'Novo Testamento']
            ],
            'Gratidão' => [
                ['text' => 'Em tudo dai graças, porque esta é a vontade de Deus.', 'reference' => '1 Tessalonicenses 5:18', 'testament' => 'Novo Testamento'],
                ['text' => 'Rendei graças ao Senhor, porque ele é bom; porque a sua misericórdia dura para sempre.', 'reference' => 'Salmo 136:1', 'testament' => 'Velho Testamento'],
                ['text' => 'Bendize, ó minha alma, ao Senhor, e não te esqueças de nenhum de seus benefícios.', 'reference' => 'Salmo 103:2', 'testament' => 'Velho Testamento'],
                ['text' => 'Graças a Deus por seu dom indescritível.', 'reference' => '2 Coríntios 9:15', 'testament' => 'Novo Testamento'],
                ['text' => 'Celebrai com júbilo ao Senhor, todos os moradores da terra.', 'reference' => 'Salmo 100:1', 'testament' => 'Velho Testamento'],
                ['text' => 'Cantarei ao Senhor por tudo o que me tem feito.', 'reference' => 'Salmo 13:6', 'testament' => 'Velho Testamento'],
                ['text' => 'Louvando a Deus e contando com a simpatia de todo o povo.', 'reference' => 'Atos 2:47', 'testament' => 'Novo Testamento'],
                ['text' => 'Ofereçamos sempre, por meio de Jesus, a Deus sacrifício de louvor.', 'reference' => 'Hebreus 13:15', 'testament' => 'Novo Testamento'],
                ['text' => 'Entrai por suas portas com ações de graças.', 'reference' => 'Salmo 100:4', 'testament' => 'Velho Testamento']
            ],
            'Amor' => [
                ['text' => 'Acima de tudo, porém, revistam-se do amor, que é o elo perfeito.', 'reference' => 'Colossenses 3:14', 'testament' => 'Novo Testamento'],
                ['text' => 'Nós amamos porque ele nos amou primeiro.', 'reference' => '1 João 4:19', 'testament' => 'Novo Testamento'],
                ['text' => 'O amor é paciente, o amor é bondoso.', 'reference' => '1 Coríntios 13:4', 'testament' => 'Novo Testamento'],
                ['text' => 'Muitas águas não poderiam apagar o amor.', 'reference' => 'Cânticos 8:7', 'testament' => 'Velho Testamento'],
                ['text' => 'Amarás o teu próximo como a ti mesmo.', 'reference' => 'Mateus 22:39', 'testament' => 'Novo Testamento'],
                ['text' => 'O Senhor teu Deus está no meio de ti, poderoso para salvar; ele se deleitará em ti com alegria.', 'reference' => 'Sofonias 3:17', 'testament' => 'Velho Testamento'],
                ['text' => 'Nisto conhecerão todos que sois meus discípulos: se tiverdes amor uns aos outros.', 'reference' => 'João 13:35', 'testament' => 'Novo Testamento'],
                ['text' => 'O ódio excita contendas, mas o amor cobre todas as transgressões.', 'reference' => 'Provérbios 10:12', 'testament' => 'Velho Testamento'],
                ['text' => 'Acima de tudo, tende amor intenso uns para com os outros.', 'reference' => '1 Pedro 4:8', 'testament' => 'Novo Testamento']
            ],
            'Direção' => [
                ['text' => 'Eu o instruirei e o ensinarei no caminho que você deve seguir.', 'reference' => 'Salmo 32:8', 'testament' => 'Velho Testamento'],
                ['text' => 'O coração do homem pode fazer planos, mas a resposta certa vem do Senhor.', 'reference' => 'Provérbios 16:1', 'testament' => 'Velho Testamento'],
                ['text' => 'Mostra-me, Senhor, os teus caminhos, ensina-me as tuas veredas.', 'reference' => 'Salmo 25:4', 'testament' => 'Velho Testamento'],
                ['text' => 'Se alguém quer fazer a vontade dele, conhecerá a respeito da doutrina.', 'reference' => 'João 7:17', 'testament' => 'Novo Testamento'],
                ['text' => 'O homem faz planos, mas o Senhor dirige os seus passos.', 'reference' => 'Provérbios 16:9', 'testament' => 'Velho Testamento'],
                ['text' => 'As tuas ovelhas ouvem a minha voz; eu as conheço, e elas me seguem.', 'reference' => 'João 10:27', 'testament' => 'Novo Testamento'],
                ['text' => 'Guia-me pela vereda da justiça por amor do seu nome.', 'reference' => 'Salmo 23:3', 'testament' => 'Velho Testamento'],
                ['text' => 'Quando vier, porém, o Espírito da verdade, ele vos guiará a toda a verdade.', 'reference' => 'João 16:13', 'testament' => 'Novo Testamento'],
                ['text' => 'Confia no Senhor de todo o teu coração... e ele endireitará as tuas veredas.', 'reference' => 'Provérbios 3:5-6', 'testament' => 'Velho Testamento']
            ]
        ];
    $devotionalVerses = [];
    foreach ($devotionalVersesByTheme as $theme => $themeVerses) {
        foreach ($themeVerses as $verse) {
            $verse['theme'] = $theme;
            $devotionalVerses[] = $verse;
        }
    }
    return $devotionalVerses;
}
