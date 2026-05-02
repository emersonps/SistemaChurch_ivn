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
        'phone' => getSystemSetting('church_phone', '+55 (92) 99386-6290'),
        'email' => getSystemSetting('church_email', 'contato@ivn.com.br'),
        'about_text' => getSystemSetting(
            'church_about_text',
            'É uma comunidade cristã comprometida com a centralidade das Escrituras, a proclamação do Evangelho e a edificação de famílias firmadas na fé. Somos apaixonados por Jesus e pelas pessoas. Cremos no poder transformador da Palavra de Deus e trabalhamos para levar o evangelho a toda criatura, vivendo em comunhão, promovendo discipulado e servindo ao próximo com amor, responsabilidade e propósito.'
        ),
        'social_links' => $normalizedSocials,
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
