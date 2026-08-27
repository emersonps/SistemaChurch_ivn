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
            $stmt = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
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

    public function demoAccess() {
        $this->requireDeveloper();
        $demoService = new DemoLandingService();
        $demoConfig = $demoService->getConfig();
        $demo = $demoConfig['enabled'] ? $demoService->getDisplayCredentials() : null;
        $siteProfile = getChurchSiteProfileSettings();
        require_once __DIR__ . '/../views/developer/demo_access.php';
    }

    public function demoRegenerate() {
        $this->requireDeveloper();
        $demoService = new DemoLandingService();
        if ($demoService->getConfig()['enabled']) {
            $demoService->forceRotateNow();
            $_SESSION['success'] = 'Novas senhas geradas. Elas serão renovadas novamente em 2 dias, como sempre.';
        }
        redirect('/developer/demo-access');
    }
}
