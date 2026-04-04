<?php
// src/controllers/DeveloperController.php

class DeveloperController {
    
    private function requireDeveloper() {
        if (!isset($_SESSION['user_id'])) {
            redirect('/admin/login');
        }
        if (!hasPermission('developer.access')) {
            redirect('/admin/dashboard');
        }
    }

    private function tableHasColumn(PDO $db, $table, $column) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $stmt = $db->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
            $stmt->execute([$column]);
            return (bool)$stmt->fetch();
        }

        $stmt = $db->query("PRAGMA table_info($table)");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            $name = $col['name'] ?? ($col['Field'] ?? null);
            if ($name && strtolower($name) === strtolower($column)) {
                return true;
            }
        }
        return false;
    }
    
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = (float)$bytes;
        for ($i = 0; $i < count($units) - 1 && $bytes >= 1024; $i++) {
            $bytes /= 1024;
        }
        return number_format($bytes, $bytes >= 10 ? 0 : 2, ',', '.') . ' ' . $units[$i];
    }
    
    private function getRecentActivityWhere(PDO $db, $field = 'last_activity') {
        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            return "$field > NOW() - INTERVAL 5 MINUTE";
        }
        return "$field > datetime('now', '-5 minutes')";
    }

    private function buildOnlineSummary(array $rows) {
        $loggedUsers = [];
        $visitors = [];

        foreach ($rows as $row) {
            $userType = $row['user_type'] ?? 'visitor';
            if (in_array($userType, ['admin', 'member'], true) && !empty($row['user_id'])) {
                $actorKey = $userType . ':' . $row['user_id'];
                if (!isset($loggedUsers[$actorKey])) {
                    $loggedUsers[$actorKey] = $row;
                }
                continue;
            }

            $sessionKey = 'visitor:' . ($row['session_id'] ?? '');
            if (!isset($visitors[$sessionKey])) {
                $visitors[$sessionKey] = $row;
            }
        }

        return [
            'logged_users' => array_values($loggedUsers),
            'visitors' => array_values($visitors)
        ];
    }
    
    public function index() {
        $this->requireDeveloper();
        $db = (new Database())->connect();
        
        // System Payments (last 12)
        $payments = $db->query("SELECT * FROM system_payments ORDER BY reference_month DESC LIMIT 12")->fetchAll();
        
        // Count users
        $users_count = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        
        // Count online users (active in the last 5 minutes)
        $online_users = 0;
        try {
            $whereRecent = $this->getRecentActivityWhere($db);
            $recentLogs = $db->query("SELECT user_id, user_type, session_id, last_activity FROM access_logs WHERE $whereRecent ORDER BY last_activity DESC")->fetchAll(PDO::FETCH_ASSOC);
            $summary = $this->buildOnlineSummary($recentLogs);
            $online_users = count($summary['logged_users']);
        } catch (PDOException $e) {
            // Ignore if table doesn't exist
        }
        
        // Pass to view
        require_once __DIR__ . '/../views/developer/dashboard.php';
    }
    
    public function settings() {
        $this->requireDeveloper();
        $db = (new Database())->connect();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $churchName = $_POST['church_name'] ?? 'Igreja Vida Nova';
            $churchAlias = $_POST['church_alias'] ?? 'IVN';
            $churchPhone = trim($_POST['church_phone'] ?? '+55 (92) 99386-6290');
            $churchEmail = trim($_POST['church_email'] ?? 'contato@ivn.com.br');
            $churchAboutText = trim($_POST['church_about_text'] ?? '');
            $socialPlatforms = $_POST['social_platform'] ?? [];
            $socialUrls = $_POST['social_url'] ?? [];
            
            // Handle logo upload
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $fileExt = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                if ($fileExt === 'png') {
                    $targetPath = __DIR__ . '/../../public/assets/img/logo.png';
                    // Backup old just in case
                    if (file_exists($targetPath)) {
                        copy($targetPath, $targetPath . '.bak');
                    }
                    move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath);
                } else {
                    $_SESSION['error'] = "A logo deve ser um arquivo PNG.";
                    redirect('/developer/settings');
                    return;
                }
            }

            $iconOptions = getChurchSocialIconOptions();
            $socialLinks = [];
            $maxItems = max(count($socialPlatforms), count($socialUrls));
            for ($i = 0; $i < $maxItems; $i++) {
                $platform = $socialPlatforms[$i] ?? '';
                $url = trim($socialUrls[$i] ?? '');
                if ($platform === '' || $url === '' || !isset($iconOptions[$platform])) {
                    continue;
                }
                $socialLinks[] = [
                    'platform' => $platform,
                    'url' => $url
                ];
            }

            if ($churchAboutText === '') {
                $churchAboutText = getChurchSiteProfileSettings()['about_text'];
            }

            $this->saveSystemSetting($db, 'church_phone', $churchPhone);
            $this->saveSystemSetting($db, 'church_email', $churchEmail);
            $this->saveSystemSetting($db, 'church_about_text', $churchAboutText);
            $this->saveSystemSetting($db, 'church_social_links', json_encode($socialLinks, JSON_UNESCAPED_UNICODE));
            
            // Execute mass replace across codebase using the existing revert logic approach
            $this->applyWhiteLabel($churchAlias, $churchName);
            
            $_SESSION['success'] = "Configurações aplicadas com sucesso em todo o sistema!";
            redirect('/developer/settings');
            return;
        }
        
        view('developer/settings', [
            'siteProfile' => getChurchSiteProfileSettings(),
            'socialIconOptions' => getChurchSocialIconOptions()
        ]);
    }
    
    private function applyWhiteLabel($newAlias, $newName) {
        $directories = [
            __DIR__ . '/../', // src
            __DIR__ . '/../../public', // public
        ];

        // Mapeamento padrão que existe hoje no código
        // Como o script replace_text_v2 mudou tudo para IVN e "Igreja Vida Nova"
        // Precisamos primeiro ler o alias atual?
        // Para simplificar, como o dev vai usar isso ativamente, vamos substituir usando Regex flexível 
        // ou assumir que o sistema SEMPRE substitui o atual pelo novo.
        // Como não guardamos o "atual", vamos ter que assumir que o atual é o default, ou buscar no header.php.
        
        $headerPath = __DIR__ . '/../views/layout/header.php';
        $currentAlias = 'IVN'; // Default fallback
        $currentName = 'Igreja Vida Nova';
        
        if (file_exists($headerPath)) {
            $content = file_get_contents($headerPath);
            if (preg_match('/<title><\?= \$seo_title \?\? \'(.*?)\' \?><\/title>/', $content, $matches)) {
                $parts = explode(' - ', $matches[1]);
                if (count($parts) >= 2) {
                    $currentAlias = trim($parts[0]);
                    $currentName = trim($parts[1]);
                }
            }
        }
        
        // Se o novo for igual ao atual, pula
        if ($currentAlias === $newAlias && $currentName === $newName) {
            return;
        }

        $extensions = ['php', 'json', 'html'];
        $modifiedFiles = 0;

        foreach ($directories as $dir) {
            if (!is_dir($dir)) continue;

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isFile() && in_array($file->getExtension(), $extensions)) {
                    $path = $file->getPathname();
                    $content = file_get_contents($path);
                    $originalContent = $content;

                    // Regex bounds to avoid breaking code like variables (e.g. $currentAlias)
                    // We just do a simple str_replace since the names are usually specific enough
                    
                    // Substituir Nome Completo
                    if ($currentName !== $newName) {
                        $content = str_replace($currentName, $newName, $content);
                    }
                    
                    // Substituir Alias (Sigla)
                    if ($currentAlias !== $newAlias) {
                        // Substituição principal (case sensitive, com word boundaries)
                        $content = preg_replace('/\b' . preg_quote($currentAlias, '/') . '\b(?!(?:_MEMBER|_logo))/', $newAlias, $content);
                        
                        // Substituição para lowercase em keywords (manifest/seo)
                        $content = preg_replace('/\b' . preg_quote(strtolower($currentAlias), '/') . '\b(?!(?:_member))/', strtolower($newAlias), $content);
                        
                        // Substituição específica para emails (contato@SIGLAANTIGA.com.br)
                        // Como o @ e o . não são caracteres de palavra (\b pode falhar), fazemos um replace direto com ignorar case
                        $content = str_ireplace('contato@' . $currentAlias, 'contato@' . strtolower($newAlias), $content);
                    }

                    if ($content !== $originalContent) {
                        file_put_contents($path, $content);
                        $modifiedFiles++;
                    }
                }
            }
        }
    }

    private function saveSystemSetting($db, $key, $value) {
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        try {
            $stmt->execute([$key, $value]);
            return;
        } catch (PDOException $e) {
        }

        $checkStmt = $db->prepare("SELECT id FROM settings WHERE setting_key = ?");
        $checkStmt->execute([$key]);
        $exists = $checkStmt->fetchColumn();

        if ($exists) {
            $updateStmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $updateStmt->execute([$value, $key]);
            return;
        }

        $insertStmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $insertStmt->execute([$key, $value]);
    }
    
    public function payments() {
        $this->requireDeveloper();
        $db = (new Database())->connect();
        $hasDueDateColumn = $this->tableHasColumn($db, 'system_payments', 'due_date');

        if ($hasDueDateColumn) {
            $payments = $db->query("SELECT * FROM system_payments ORDER BY reference_month DESC")->fetchAll();
        } else {
            $payments = $db->query("SELECT *, payment_date AS due_date FROM system_payments ORDER BY reference_month DESC")->fetchAll();
        }
        
        require_once __DIR__ . '/../views/developer/payments.php';
    }
    
    public function import() {
        $this->requireDeveloper();
        $db = (new Database())->connect();
        
        // Mapeamento de Meses
        $months = [
            'jan' => '01', 'fev' => '02', 'mar' => '03', 'abr' => '04', 'mai' => '05', 'jun' => '06',
            'jul' => '07', 'ago' => '08', 'set' => '09', 'out' => '10', 'nov' => '11', 'dez' => '12'
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $raw_data = $_POST['raw_data'] ?? '';
            $lines = explode("\n", $raw_data);
            $count = 0;
            $updatedCount = 0;
            $errors = [];
            
            // Buscar ID da Congregação Padrão (assumindo a primeira ativa se não especificado)
            // Mas no caso específico do usuário, ele quer a congregação 6
            $congregation_id = $_POST['congregation_id'] ?? 6;

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Tentar usar Regex primeiro (formato: 07/jan ou 7-jan ou 7/01)
                // Resolve problema de tabs ausentes, múltiplos espaços no nome, ou falta de descrição
                if (preg_match('/^(\d{1,2}[\/-][a-zA-Z0-9]{2,3})\s+(OFERTA|DÍZIMO|DIZIMO|EBD)?\s*(.*?)\s*(ESPÉCIE|PIX|DINHEIRO|CARTÃO|TRANSFERÊNCIA)\s+((?:R\$)?\s*[\d\.,]+)$/ui', $line, $matches)) {
                    $parts = [
                        $matches[1], // Data
                        $matches[2], // Tipo
                        $matches[3], // Descrição
                        $matches[4], // Método
                        $matches[5]  // Valor
                    ];
                } else {
                    // Fallback para separação por TAB ou múltiplos espaços
                    $parts = preg_split('/\t+/', $line);
                    
                    if (count($parts) < 4) {
                        $parts = preg_split('/\s{2,}/', $line);
                    }
                }
                
                if (count($parts) >= 4) {
                    try {
                        // Parse Data
                        $datePart = str_replace('-', '/', trim($parts[0]));
                        if (strpos($datePart, '/') !== false) {
                            list($day, $monthName) = explode('/', $datePart);
                            // Se for numérico (ex: 01), usa direto, senão pega do array de meses (ex: jan)
                            if (is_numeric($monthName)) {
                                $month = str_pad(trim($monthName), 2, '0', STR_PAD_LEFT);
                            } else {
                                $month = $months[strtolower(trim($monthName))] ?? '01';
                            }
                            $year = $_POST['year'] ?? date('Y');
                            $date = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                        } else {
                            $date = date('Y-m-d'); // Fallback
                        }
                        
                        $type = 'Oferta';
                        $desc = '';
                        $met = '';
                        $valStr = '';

                        // Verifica se tem 5 colunas (Data, Tipo, Descrição, Método, Valor)
                        if (count($parts) >= 5) {
                            $typePart = mb_strtolower(trim($parts[1]), 'UTF-8');
                            if (strpos($typePart, 'dízimo') !== false || strpos($typePart, 'dizimo') !== false) {
                                $type = 'Dízimo';
                            } else if (strpos($typePart, 'ebd') !== false) {
                                $type = 'Oferta'; // EBD entra como Oferta
                                $desc = trim($parts[2]);
                                if (empty($desc)) {
                                    $desc = 'EBD';
                                } else {
                                    $desc = 'EBD ' . $desc;
                                }
                            } else {
                                $type = 'Oferta';
                            }
                            
                            if (empty($desc) && strpos($typePart, 'ebd') === false) {
                                $desc = trim($parts[2]);
                            }
                            
                            $met = strtoupper(trim($parts[3]));
                            $valStr = trim($parts[4]);
                        } else {
                            // Formato antigo: Data, Descrição, Método, Valor
                            $desc = trim($parts[1]);
                            $met = strtoupper(trim($parts[2]));
                            $valStr = trim($parts[3]);
                        }
                        
                        // Parse Método
                        $method = ($met === 'ESPÉCIE' || $met === 'DINHEIRO') ? 'Dinheiro' : $met;
                        
                        // Parse Valor
                        $val = str_replace(['R$', ' ', '.'], '', $valStr);
                        $val = str_replace(',', '.', $val);
                        $amount = (float)$val;
                        
                        // Check for duplicates (removido a checagem de "type" para permitir que o import corrija o tipo se estiver errado no banco)
                        $stmtCheck = $db->prepare("SELECT id FROM tithes WHERE payment_date = ? AND amount = ? AND giver_name = ? AND congregation_id = ?");
                        $stmtCheck->execute([$date, $amount, $desc, $congregation_id]);
                        $existingId = $stmtCheck->fetchColumn();
                        
                        if ($existingId) {
                            // Update existing record
                            $stmtUpdate = $db->prepare("UPDATE tithes SET type = ?, payment_method = ?, notes = ? WHERE id = ?");
                            $stmtUpdate->execute([$type, $method, $desc, $existingId]);
                            $updatedCount++;
                        } else {
                            // Try to find member by name to link automatically
                            $memberId = null;
                            if (!empty($desc)) {
                                $stmtMember = $db->prepare("SELECT id FROM members WHERE LOWER(name) = LOWER(?) LIMIT 1");
                                $stmtMember->execute([trim($desc)]);
                                $memberId = $stmtMember->fetchColumn() ?: null;
                            }

                            // Insert new record
                            $stmt = $db->prepare("INSERT INTO tithes (member_id, amount, payment_date, payment_method, type, notes, congregation_id, giver_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$memberId, $amount, $date, $method, $type, $desc, $congregation_id, $desc]);
                            $count++;
                        }
                        
                    } catch (Exception $e) {
                        $errors[] = "Erro na linha '$line': " . $e->getMessage();
                    }
                } else {
                    $errors[] = "Formato inválido na linha: '$line'";
                }
            }
            
            $_SESSION['import_result'] = [
                'success' => $count,
                'updated' => $updatedCount,
                'errors' => $errors
            ];
            
            redirect('/developer/import');
            return;
        }
        
        $congregations = $db->query("SELECT * FROM congregations ORDER BY name ASC")->fetchAll();
        require_once __DIR__ . '/../views/developer/import.php';
    }

    public function clearEntries() {
        $this->requireDeveloper();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = (new Database())->connect();
            $stmt = $db->query("DELETE FROM tithes");
            $count = $stmt->rowCount();
            
            // Reseta o auto_increment para o sqlite (sqlite não usa TRUNCATE ou auto_increment como mysql, ele recria ou vc pode fazer sqlite_sequence)
            // Para garantir que funciona em ambos, deletar todos é mais seguro e compatível
            try {
                $db->query("DELETE FROM sqlite_sequence WHERE name='tithes'");
            } catch (Exception $e) {}

            $_SESSION['import_result'] = [
                'success' => 0,
                'errors' => ["Todos os $count registros de Entradas foram apagados com sucesso!"] // Hack para mostrar mensagem customizada
            ];
        }
        redirect('/developer/import');
    }

    public function syncMembers() {
        $this->requireDeveloper();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = (new Database())->connect();
            
            // Buscar todos os membros
            $members = $db->query("SELECT id, name FROM members")->fetchAll();
            $count = 0;
            
            foreach ($members as $m) {
                // Atualizar tithes onde o nome é igual (case insensitive) e member_id é NULL
                // SQLite usa LIKE case-insensitive por padrão para ASCII, mas LOWER() é mais seguro
                $stmt = $db->prepare("UPDATE tithes SET member_id = ? WHERE member_id IS NULL AND LOWER(giver_name) = LOWER(?)");
                $stmt->execute([$m['id'], trim($m['name'])]);
                $count += $stmt->rowCount();
            }

            $_SESSION['import_result'] = [
                'success' => 0,
                'updated' => 0,
                'errors' => ["Sincronização concluída! $count registros antigos foram vinculados aos membros cadastrados."] 
            ];
        }
        redirect('/developer/import');
    }
    
    public function importExpenses() {
        $this->requireDeveloper();
        $db = (new Database())->connect();
        
        // Mapeamento de Meses
        $months = [
            'jan' => '01', 'fev' => '02', 'mar' => '03', 'abr' => '04', 'mai' => '05', 'jun' => '06',
            'jul' => '07', 'ago' => '08', 'set' => '09', 'out' => '10', 'nov' => '11', 'dez' => '12'
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $raw_data = $_POST['raw_data'] ?? '';
            $lines = explode("\n", $raw_data);
            $count = 0;
            $updatedCount = 0;
            $errors = [];
            
            $congregation_id = $_POST['congregation_id'] ?? 6;

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Formato esperado: DATA TAB DESCRIÇÃO TAB VALOR
                // Ex: 10/mar 	 Instalação do condicionador 	  R$ 450,00
                $parts = preg_split('/\t+/', $line);
                
                if (count($parts) < 3) {
                    $parts = preg_split('/\s{2,}/', $line);
                }
                
                if (count($parts) >= 3) {
                    try {
                        // Parse Data
                        $datePart = str_replace('-', '/', trim($parts[0]));
                        if (strpos($datePart, '/') !== false) {
                            list($day, $monthName) = explode('/', $datePart);
                            if (is_numeric($monthName)) {
                                $month = str_pad(trim($monthName), 2, '0', STR_PAD_LEFT);
                            } else {
                                $month = $months[strtolower(trim($monthName))] ?? '01';
                            }
                            $year = $_POST['year'] ?? date('Y');
                            $date = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                        } else {
                            $date = date('Y-m-d');
                        }
                        
                        // Parse Descrição
                        $desc = trim($parts[1]);
                        
                        // Parse Categoria
                        // Pode ter categoria no 3º e valor no 4º, ou só valor no 3º
                        $category = 'Outros';
                        $valStr = '';
                        if (count($parts) >= 4) {
                            $category = trim($parts[2]);
                            $valStr = trim($parts[3]);
                        } else {
                            $valStr = trim($parts[2]);
                        }
                        
                        // Parse Valor
                        $val = str_replace(['R$', ' ', '.'], '', $valStr);
                        $val = str_replace(',', '.', $val);
                        $amount = (float)$val;
                        
                        // Check for duplicates
                        $stmtCheck = $db->prepare("SELECT id FROM expenses WHERE expense_date = ? AND amount = ? AND description = ? AND congregation_id = ?");
                        $stmtCheck->execute([$date, $amount, $desc, $congregation_id]);
                        $existingId = $stmtCheck->fetchColumn();
                        
                        if ($existingId) {
                            // Update existing record
                            $stmtUpdate = $db->prepare("UPDATE expenses SET category = ?, notes = ? WHERE id = ?");
                            $stmtUpdate->execute([$category, 'Atualizado via Painel Dev', $existingId]);
                            $updatedCount++;
                        } else {
                            $stmt = $db->prepare("INSERT INTO expenses (description, amount, expense_date, category, congregation_id, notes) VALUES (?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$desc, $amount, $date, $category, $congregation_id, 'Importado via Painel Dev']);
                            $count++;
                        }
                        
                    } catch (Exception $e) {
                        $errors[] = "Erro na linha '$line': " . $e->getMessage();
                    }
                } else {
                    $errors[] = "Formato inválido na linha: '$line'";
                }
            }
            
            $_SESSION['import_result'] = [
                'success' => $count,
                'updated' => $updatedCount,
                'errors' => $errors
            ];
            
            redirect('/developer/import/expenses');
            return;
        }
        
        $congregations = $db->query("SELECT * FROM congregations ORDER BY name ASC")->fetchAll();
        require_once __DIR__ . '/../views/developer/import_expenses.php';
    }

    public function clearExpenses() {
        $this->requireDeveloper();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = (new Database())->connect();
            $stmt = $db->query("DELETE FROM expenses");
            $count = $stmt->rowCount();
            
            try {
                $db->query("DELETE FROM sqlite_sequence WHERE name='expenses'");
            } catch (Exception $e) {}

            $_SESSION['import_result'] = [
                'success' => 0,
                'errors' => ["Todos os $count registros de Saídas foram apagados com sucesso!"] // Hack para mostrar mensagem customizada
            ];
        }
        redirect('/developer/import/expenses');
    }

    public function generateCharge() {
        $this->requireDeveloper();
        $db = (new Database())->connect();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $month = $_POST['month']; // YYYY-MM
            $status = $_POST['status'] ?? 'pending';
            $amount = $_POST['amount'] ?? 59.99;
            $dueDay = intval($_POST['due_day'] ?? 5);
            $hasDueDateColumn = $this->tableHasColumn($db, 'system_payments', 'due_date');

            if ($dueDay < 1 || $dueDay > 31) {
                $dueDay = 5;
            }
            
            if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
                redirect('/developer/payments?error=invalid_date');
            }
            
            $daysInMonth = date('t', strtotime($month . '-01'));
            $actualDay = min($dueDay, $daysInMonth);
            $dueDate = $month . '-' . str_pad($actualDay, 2, '0', STR_PAD_LEFT) . ' 00:00:00';
            $paymentDate = $status === 'paid' ? date('Y-m-d H:i:s') : null;
            
            $stmt = $db->prepare("SELECT COUNT(*) FROM system_payments WHERE reference_month = ?");
            $stmt->execute([$month]);
            
            if ($stmt->fetchColumn() > 0) {
                 if ($hasDueDateColumn) {
                     $stmt = $db->prepare("UPDATE system_payments SET status = ?, amount = ?, due_date = ?, payment_date = ? WHERE reference_month = ?");
                     $stmt->execute([$status, $amount, $dueDate, $paymentDate, $month]);
                 } else {
                     $legacyDate = $status === 'paid' ? $paymentDate : $dueDate;
                     $stmt = $db->prepare("UPDATE system_payments SET status = ?, amount = ?, payment_date = ? WHERE reference_month = ?");
                     $stmt->execute([$status, $amount, $legacyDate, $month]);
                 }
            } else {
                 if ($hasDueDateColumn) {
                     $stmt = $db->prepare("INSERT INTO system_payments (reference_month, status, amount, due_date, payment_date) VALUES (?, ?, ?, ?, ?)");
                     $stmt->execute([$month, $status, $amount, $dueDate, $paymentDate]);
                 } else {
                     $legacyDate = $status === 'paid' ? $paymentDate : $dueDate;
                     $stmt = $db->prepare("INSERT INTO system_payments (reference_month, status, amount, payment_date) VALUES (?, ?, ?, ?)");
                     $stmt->execute([$month, $status, $amount, $legacyDate]);
                 }
            }
            
            if ($status === 'paid') {
                 // Register Expense
                 // Fetch the amount we just inserted or updated
                 $stmtAmt = $db->prepare("SELECT amount FROM system_payments WHERE reference_month = ?");
                 $stmtAmt->execute([$month]);
                 $actualAmount = $stmtAmt->fetchColumn();
                 
                 registerSystemPaymentExpense($month, $actualAmount);

                 $nextMonthDate = DateTime::createFromFormat('!Y-m-d', $month . '-01');
                 $nextMonthDate->modify('+1 month');
                 $nextMonth = $nextMonthDate->format('Y-m');
                 
                 $stmt = $db->prepare("SELECT COUNT(*) FROM system_payments WHERE reference_month = ?");
                 $stmt->execute([$nextMonth]);
                 if ($stmt->fetchColumn() == 0) {
                     // Use the same due day for next month
                     $daysInNextMonth = date('t', strtotime($nextMonth . '-01'));
                     $nextActualDay = min($dueDay, $daysInNextMonth);
                     $nextDueDate = $nextMonth . '-' . str_pad($nextActualDay, 2, '0', STR_PAD_LEFT) . ' 00:00:00';

                     if ($hasDueDateColumn) {
                         $stmt = $db->prepare("INSERT INTO system_payments (reference_month, status, amount, due_date, payment_date) VALUES (?, 'pending', 59.99, ?, NULL)");
                         $stmt->execute([$nextMonth, $nextDueDate]);
                     } else {
                         $stmt = $db->prepare("INSERT INTO system_payments (reference_month, status, amount, payment_date) VALUES (?, 'pending', 59.99, ?)");
                         $stmt->execute([$nextMonth, $nextDueDate]);
                     }
                 }
            }
            
            redirect('/developer/payments?success=1');
        }
    }
    
    public function deletePayment() {
        $this->requireDeveloper();
        $id = $_GET['id'] ?? null;
        if ($id) {
            $db = (new Database())->connect();
            $db->exec("DELETE FROM system_payments WHERE id = $id");
        }
        redirect('/developer/payments');
    }
    
    public function updateStatus() {
        $this->requireDeveloper();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $status = $_POST['status'];
            $referenceMonth = $_POST['reference_month'] ?? '';
            $dueDate = $_POST['due_date'] ?? '';
            $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : null;
            
            if ($id && $status && preg_match('/^\d{4}-\d{2}$/', $referenceMonth)) {
                $db = (new Database())->connect();
                $hasDueDateColumn = $this->tableHasColumn($db, 'system_payments', 'due_date');
                
                if ($hasDueDateColumn) {
                    $stmt = $db->prepare("SELECT id, reference_month, due_date, payment_date, amount FROM system_payments WHERE id = ?");
                } else {
                    $stmt = $db->prepare("SELECT id, reference_month, payment_date, amount FROM system_payments WHERE id = ?");
                }
                $stmt->execute([$id]);
                $payment = $stmt->fetch();
                
                if ($payment) {
                    $originalMonth = $payment['reference_month'];
                    $month = $referenceMonth;
                    $paymentDate = null;
                    $storedDueDate = $payment['due_date'] ?? ($payment['payment_date'] ?? null);
                    $resolvedDueDate = null;
                    $resolvedAmount = ($amount !== null && $amount > 0) ? $amount : (float)($payment['amount'] ?? 59.99);

                    $stmtDuplicate = $db->prepare("SELECT COUNT(*) FROM system_payments WHERE reference_month = ? AND id <> ?");
                    $stmtDuplicate->execute([$referenceMonth, $id]);
                    if ((int)$stmtDuplicate->fetchColumn() > 0) {
                        redirect('/developer/payments?error=duplicate_reference_month');
                    }
                    
                    if ($status === 'paid') {
                        $paymentDate = date('Y-m-d H:i:s');
                    } else {
                        if (!empty($dueDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
                            $resolvedDueDate = $dueDate . ' 00:00:00';
                        } elseif (!empty($storedDueDate)) {
                            $resolvedDueDate = date('Y-m-d 00:00:00', strtotime($storedDueDate));
                        } else {
                            $resolvedDueDate = $month . '-05 00:00:00';
                        }
                    }

                    if ($hasDueDateColumn) {
                        $stmt = $db->prepare("UPDATE system_payments SET reference_month = ?, status = ?, amount = ?, due_date = ?, payment_date = ? WHERE id = ?");
                        $stmt->execute([
                            $referenceMonth,
                            $status,
                            $resolvedAmount,
                            $status === 'paid' ? ($storedDueDate ?: $resolvedDueDate ?: ($month . '-05 00:00:00')) : $resolvedDueDate,
                            $paymentDate,
                            $id
                        ]);
                    } else {
                        $legacyDate = $status === 'paid' ? $paymentDate : $resolvedDueDate;
                        $stmt = $db->prepare("UPDATE system_payments SET reference_month = ?, status = ?, amount = ?, payment_date = ? WHERE id = ?");
                        $stmt->execute([$referenceMonth, $status, $resolvedAmount, $legacyDate, $id]);
                    }
                    
                    // Register Expense if Paid
                    if ($status === 'paid') {
                        // Fetch amount
                        $stmtAmt = $db->prepare("SELECT amount FROM system_payments WHERE id = ?");
                        $stmtAmt->execute([$id]);
                        $actualAmount = $stmtAmt->fetchColumn();
                        
                        registerSystemPaymentExpense($referenceMonth, $actualAmount);

                        $baseDueDate = $storedDueDate ?: ($month . '-05 00:00:00');
                        $baseDueDay = (int)date('d', strtotime($baseDueDate));
                        if ($baseDueDay < 1 || $baseDueDay > 31) {
                            $baseDueDay = 5;
                        }

                        $stmtLast = $db->query("SELECT MAX(reference_month) FROM system_payments");
                        $lastReferenceMonth = $stmtLast->fetchColumn();

                        if ($lastReferenceMonth === $referenceMonth) {
                            $nextMonthDate = DateTime::createFromFormat('!Y-m-d', $referenceMonth . '-01');
                            if ($nextMonthDate) {
                                $nextMonthDate->modify('+1 month');
                                $nextMonth = $nextMonthDate->format('Y-m');

                                $stmtNext = $db->prepare("SELECT COUNT(*) FROM system_payments WHERE reference_month = ?");
                                $stmtNext->execute([$nextMonth]);

                                if ((int)$stmtNext->fetchColumn() === 0) {
                                    $daysInNextMonth = (int)date('t', strtotime($nextMonth . '-01'));
                                    $nextActualDay = min($baseDueDay, $daysInNextMonth);
                                    $nextDueDate = $nextMonth . '-' . str_pad($nextActualDay, 2, '0', STR_PAD_LEFT) . ' 00:00:00';
                                    $nextAmount = $actualAmount !== false && $actualAmount !== null ? $actualAmount : ($payment['amount'] ?? 59.99);

                                    if ($hasDueDateColumn) {
                                        $stmtInsert = $db->prepare("INSERT INTO system_payments (reference_month, status, amount, due_date, payment_date) VALUES (?, 'pending', ?, ?, NULL)");
                                        $stmtInsert->execute([$nextMonth, $nextAmount, $nextDueDate]);
                                    } else {
                                        $stmtInsert = $db->prepare("INSERT INTO system_payments (reference_month, status, amount, payment_date) VALUES (?, 'pending', ?, ?)");
                                        $stmtInsert->execute([$nextMonth, $nextAmount, $nextDueDate]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        redirect('/developer/payments');
    }

    public function logs() {
        $this->requireDeveloper();
        $db = (new Database())->connect();
        
        try {
            $whereRecent = $this->getRecentActivityWhere($db);
            $activeRows = $db->query("
                SELECT user_id, user_name, user_type, ip_address, last_activity, requested_url, session_id
                FROM access_logs
                WHERE $whereRecent
                ORDER BY last_activity DESC
            ")->fetchAll(PDO::FETCH_ASSOC);
            $summary = $this->buildOnlineSummary($activeRows);
            $onlineUsers = $summary['logged_users'];
            $activeVisitors = $summary['visitors'];
            
            // Get all logs (limit to 1000 for performance)
            $logsQuery = "SELECT * FROM access_logs ORDER BY last_activity DESC LIMIT 1000";
            $logs = $db->query($logsQuery)->fetchAll();
            
        } catch (PDOException $e) {
            $onlineUsers = [];
            $activeVisitors = [];
            $logs = [];
            $error = "Tabela de logs não encontrada. Execute as migrações.";
        }
        
        require_once __DIR__ . '/../views/developer/access_logs.php';
    }


    public function backups() {
        $this->requireDeveloper();
        $manager = new DatabaseBackupManager();
        $generated = null;

        try {
            $generated = $manager->ensureWeeklyBackup();
            if ($generated) {
                $_SESSION['success'] = 'Backup semanal automático gerado com sucesso.';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Não foi possível gerar o backup automático: ' . $e->getMessage();
        }

        $backups = array_map(function ($backup) {
            $backup['size_label'] = $this->formatBytes($backup['size']);
            $backup['created_at_label'] = date('d/m/Y H:i', $backup['created_at']);
            return $backup;
        }, $manager->listBackups());

        require_once __DIR__ . '/../views/developer/backups.php';
    }

    public function generateBackup() {
        $this->requireDeveloper();

        try {
            $manager = new DatabaseBackupManager();
            $manager->createBackup('manual');
            $_SESSION['success'] = 'Backup manual gerado com sucesso.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Falha ao gerar backup: ' . $e->getMessage();
        }

        redirect('/developer/backups');
    }

    public function downloadBackup() {
        $this->requireDeveloper();

        $filename = $_GET['file'] ?? '';
        $manager = new DatabaseBackupManager();
        $path = $manager->getBackupPath($filename);
        if (!$path) {
            redirect('/developer/backups');
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: public');
        readfile($path);
        exit;
    }
    public function users() {
        $this->requireDeveloper();
        require_once __DIR__ . '/../views/developer/users.php';
        require_once __DIR__ . '/../views/developer/users.php';
    }

    public function editRole($roleKey) {
        $this->requireDeveloper();
        $db = (new Database())->connect();
        
        $rbac = require __DIR__ . '/../../config/rbac.php';
        $roles = $rbac['roles'];

        if (!isset($roles[$roleKey])) {
            redirect('/developer/users');
        }

        $roleData = $roles[$roleKey];
        // Seed missing catalog permissions for Grupos/Células
        try {
            $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
            $ignore = $driver === 'sqlite' ? 'OR IGNORE' : 'IGNORE';
            $db->exec("INSERT $ignore INTO permissions (slug, label, description) VALUES 
                ('groups.view', 'Ver Grupos/Células', 'Visualizar grupos/células'),
                ('groups.manage', 'Gerenciar Grupos/Células', 'Criar, editar e excluir grupos/células')
            ");
        } catch (Exception $e) {}
        $permissions = $db->query("SELECT * FROM permissions ORDER BY label ASC")->fetchAll();
        $permissionGroups = buildPermissionGroups($permissions);
        
        // As permissões base do papel, nós vamos permitir editá-las via um arquivo de configuração modificado (ou apenas listamos aqui e avisamos que ainda não temos backend de escrita no config file)
        // Precisaremos de uma tabela de customizações de roles no BD ou modificar o arquivo rbac.php dinamicamente.
        // Já que o usuário pediu "atribuir aos papéis", a forma mais limpa em PHP é usar uma tabela de banco para papéis ou regravar o array no `config/rbac.php`.
        // Mas como o sistema atual usa o `config/rbac.php`, vamos ler dele.
        $rolePermissions = $roleData['permissions'] ?? [];

        require_once __DIR__ . '/../views/developer/role_edit.php';
    }

    public function updateRole($roleKey) {
        $this->requireDeveloper();
        $custom_permissions = normalizePermissionSelection(isset($_POST['permissions']) ? $_POST['permissions'] : []);
        $db = (new Database())->connect();

        $rbacFile = __DIR__ . '/../../config/rbac.php';
        $rbac = require $rbacFile;
        
        if (!isset($rbac['roles'][$roleKey])) {
            redirect('/developer/users?error=1');
        }

        $previousRolePermissions = $rbac['roles'][$roleKey]['permissions'] ?? [];
        $removedPermissions = array_values(array_diff($previousRolePermissions, $custom_permissions));
        $removedPermissions = array_values(array_diff($removedPermissions, getAdminEditablePermissionSlugs()));

        // Modifica as permissões no array
        $rbac['roles'][$roleKey]['permissions'] = $custom_permissions;

        // Regrava o arquivo config/rbac.php
        $content = "<?php\n// config/rbac.php\n\nreturn " . var_export($rbac, true) . ";\n";
        
        // O var_export formata o array, mas fica feio. É melhor do que nada, e funciona perfeitamente para arquivos de configuração.
        file_put_contents($rbacFile, $content);

        if (!empty($removedPermissions)) {
            $stmtUsers = $db->prepare("SELECT id FROM users WHERE role = ?");
            $stmtUsers->execute([$roleKey]);
            $users = $stmtUsers->fetchAll(PDO::FETCH_COLUMN);

            foreach ($users as $userId) {
                $stmtCurrent = $db->prepare("SELECT permission_slug FROM user_permissions WHERE user_id = ?");
                $stmtCurrent->execute([$userId]);
                $storedPermissions = $stmtCurrent->fetchAll(PDO::FETCH_COLUMN);

                if (in_array('__override__', $storedPermissions, true)) {
                    continue;
                }

                $placeholders = implode(',', array_fill(0, count($removedPermissions), '?'));
                $params = array_merge([$userId], $removedPermissions);
                $stmtDelete = $db->prepare("DELETE FROM user_permissions WHERE user_id = ? AND permission_slug IN ($placeholders)");
                $stmtDelete->execute($params);
            }
        }

        redirect('/developer/users?success=1');
    }

    public function changeUserPassword() {
        $this->requireDeveloper();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'];
            $newPassword = $_POST['new_password'];
            
            if ($userId && $newPassword) {
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                
                $db = (new Database())->connect();
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, $userId]);
                
                redirect('/developer/users?success=1');
            } else {
                redirect('/developer/users?error=missing_fields');
            }
        } else {
            redirect('/developer/users');
        }
    }
}
