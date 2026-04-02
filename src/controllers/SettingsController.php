<?php
// src/controllers/SettingsController.php

class SettingsController {
    public function index() {
        requirePermission('settings.view');
        $db = (new Database())->connect();
        
        $settingsDb = $db->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_ASSOC);
        $settings = [];
        foreach ($settingsDb as $s) {
            $settings[$s['setting_key']] = $s['setting_value'];
        }
        
        view('admin/settings/index', ['settings' => $settings]);
    }

    public function store() {
        requirePermission('settings.view'); // You might want a settings.manage permission, but using .view for now
        $db = (new Database())->connect();
        
        $keys = ['whatsapp_api_url', 'whatsapp_api_instance', 'whatsapp_api_token'];
        
        $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        
        foreach ($keys as $key) {
            if (isset($_POST[$key])) {
                $stmt->execute([$_POST[$key], $key]);
            }
        }
        
        redirect('/admin/settings?success=1');
    }

    public function cardLayout() {
        requirePermission('settings.view');
        $db = (new Database())->connect();
        
        $layout = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'card_layout'")->fetchColumn();
        if (!$layout) $layout = 'model_1';
        
        view('admin/settings/card_layout', ['current_layout' => $layout, 'models' => getCardLayouts()]);
    }

    public function storeCardLayout() {
        requirePermission('settings.view');
        $db = (new Database())->connect();
        $layout = $_POST['card_layout'] ?? 'model_1';
        $siglaColor = $_POST['card_sigla_color'] ?? '#0d6efd';
        
        // Save layout
        $exists = $db->query("SELECT COUNT(*) FROM settings WHERE setting_key = 'card_layout'")->fetchColumn();
        if ($exists) {
            $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'card_layout'");
            $stmt->execute([$layout]);
        } else {
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('card_layout', ?)");
            $stmt->execute([$layout]);
        }

        // Save sigla color
        $existsColor = $db->query("SELECT COUNT(*) FROM settings WHERE setting_key = 'card_sigla_color'")->fetchColumn();
        if ($existsColor) {
            $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'card_sigla_color'");
            $stmt->execute([$siglaColor]);
        } else {
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('card_sigla_color', ?)");
            $stmt->execute([$siglaColor]);
        }
        
        redirect('/admin/settings/card-layout?success=1');
    }

    public function connect() {
        requirePermission('settings.view');
        header('Content-Type: application/json');

        $db = (new Database())->connect();
        $settingsDb = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('whatsapp_api_url', 'whatsapp_api_instance', 'whatsapp_api_token')")->fetchAll(PDO::FETCH_KEY_PAIR);

        $url = rtrim($settingsDb['whatsapp_api_url'] ?? '', '/');
        $instance = $settingsDb['whatsapp_api_instance'] ?? '';
        $token = $settingsDb['whatsapp_api_token'] ?? '';

        if (empty($url) || empty($instance) || empty($token)) {
            echo json_encode(['error' => 'Configurações incompletas. Salve URL, Instância e Token primeiro.']);
            exit;
        }

        // 1. Tentar criar a instância (caso não exista)
        $createEndpoint = "{$url}/instance/create";
        $createData = [
            'instanceName' => $instance,
            'token' => $token,
            'qrcode' => true
        ];

        $ch = curl_init($createEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($createData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . $token
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Se criou ou já existe (403 ou 201), tenta conectar para pegar o QR
        
        // 2. Pegar o QR Code
        $connectEndpoint = "{$url}/instance/connect/{$instance}";
        $ch = curl_init($connectEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $token
        ]);
        $responseQR = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($responseQR, true);

        if (isset($data['base64'])) {
            echo json_encode(['qrcode' => $data['base64']]);
        } elseif (isset($data['instance']) && $data['instance']['state'] === 'open') {
            echo json_encode(['status' => 'connected', 'message' => 'Instância já conectada!']);
        } else {
            // Fallback: se a resposta for diferente, repassa
            echo $responseQR;
        }
    }

    public function testBirthdays() {
        requirePermission('settings.view');
        header('Content-Type: application/json');

        // Capture output buffer to prevent random echoes
        ob_start();
        
        // Include the cron script logic
        // We need to adjust the path because cron_birthdays.php is in public/ folder
        // and expects to be run from there or CLI
        
        $cronPath = __DIR__ . '/../../public/cron_birthdays.php';
        
        if (file_exists($cronPath)) {
            // Simulate CLI environment to avoid path issues if possible, 
            // but since we are including, we need to be careful about require_once paths in cron file
            
            // To make it simpler and safer, we will just replicate the core logic here for the test
            // or we can use curl to call the public URL if available, but internal logic is better for debugging
            
            try {
                // Reusing the logic from cron_birthdays.php but capturing output
                $db = (new Database())->connect();
                $today = date('d/m');
                
                // 1. Get Settings
                $settingsDb = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('whatsapp_api_url', 'whatsapp_api_instance', 'whatsapp_api_token')")->fetchAll(PDO::FETCH_KEY_PAIR);
                $apiUrl = rtrim($settingsDb['whatsapp_api_url'] ?? '', '/');
                $instance = $settingsDb['whatsapp_api_instance'] ?? '';
                $token = $settingsDb['whatsapp_api_token'] ?? '';
                
                if (empty($apiUrl) || empty($instance) || empty($token)) {
                    throw new Exception("API do WhatsApp não configurada.");
                }

                // 2. Get Birthdays
                // Fix for SQLite/MySQL compatibility with datetime format (YYYY-MM-DD HH:MM:SS)
                // We use substr/strftime to extract day and month
                
                // Status check: Accept 'active' OR 'Congregando' (common in church systems)
                $statusCondition = "AND (status = 'active' OR status = 'Congregando')";
                
                // Get birthdays grouped by congregation
                // We need to fetch congregation_id to segment the messages
                $sql = "SELECT name, phone, congregation_id FROM members WHERE strftime('%d/%m', birth_date) = ? $statusCondition";
                
                // If MySQL, use DATE_FORMAT
                if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
                     $sql = "SELECT name, phone, congregation_id FROM members WHERE DATE_FORMAT(birth_date, '%d/%m') = ? $statusCondition";
                }
                
                $stmt = $db->prepare($sql);
                $stmt->execute([$today]);
                $birthdays = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Debug: If no results, try fallback query for full datetime string 'YYYY-MM-DD HH:MM:SS'
                $monthDay = ''; // Initialize variable to avoid warning
                
                if (empty($birthdays)) {
                    // Try matching string directly (substr for SQLite)
                    // Let's ensure month and day are zero-padded
                    list($day, $month) = explode('/', $today);
                    
                    if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
                        // Try SUBSTR: YYYY-MM-DD -> start at 6, length 5 => MM-DD
                        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
                        $day = str_pad($day, 2, '0', STR_PAD_LEFT);
                        $monthDay = "{$month}-{$day}";
                        
                        $stmt = $db->prepare("SELECT name, phone, congregation_id FROM members WHERE substr(birth_date, 6, 5) = ? $statusCondition");
                        $stmt->execute([$monthDay]);
                        $birthdays = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                }

                if (empty($birthdays)) {
                    ob_end_clean();
                    echo json_encode([
                        'status' => 'warning', 
                        'message' => 'Nenhum aniversariante encontrado para hoje (' . $today . '). Verifique se a data de nascimento está correta no cadastro.',
                        'debug_date' => $today,
                        'db_driver' => $db->getAttribute(PDO::ATTR_DRIVER_NAME),
                        'debug_query' => "SELECT name, phone, congregation_id FROM members WHERE substr(birth_date, 6, 5) = '$monthDay' $statusCondition"
                    ]);
                    return;
                }
                
                // 3. Segment Birthdays by Congregation
                $birthdaysByCongregation = [];
                foreach ($birthdays as $b) {
                    $congId = $b['congregation_id'] ?? '0'; // '0' or 'NULL' for Headquarters/Unknown
                    if (!isset($birthdaysByCongregation[$congId])) {
                        $birthdaysByCongregation[$congId] = [];
                    }
                    $birthdaysByCongregation[$congId][] = $b;
                }

                // 4. Send Messages per Congregation
                $endpoint = "$apiUrl/message/sendText/$instance";
                $sentCount = 0;
                $logMessages = [];

                foreach ($birthdaysByCongregation as $congId => $congBirthdays) {
                    // Fetch Congregation Name for logging
                    $congName = "Sede / Desconhecida";
                    if ($congId > 0) {
                        $cStmt = $db->prepare("SELECT name FROM congregations WHERE id = ?");
                        $cStmt->execute([$congId]);
                        $congName = $cStmt->fetchColumn() ?: "Congregação #$congId";
                    } else {
                        $congName = "Sede";
                    }

                    // Find Recipients (Secretary of THIS congregation)
                    $recipients = [];
                    
                    // Search for secretaries linked to this congregation
                    // Assuming users table has NO congregation_id directly, but linked member HAS.
                    $secSql = "SELECT m.phone, m.name, u.role FROM users u 
                               JOIN members m ON u.member_id = m.id 
                               WHERE u.role = 'secretary' 
                               AND m.congregation_id = ? 
                               AND m.phone IS NOT NULL AND m.phone != ''";
                    
                    $secStmt = $db->prepare($secSql);
                    $secStmt->execute([$congId]);
                    $secretaries = $secStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // DEBUG: Log all found secretaries to help user diagnose why someone is missing
                    if (empty($secretaries)) {
                        $logMessages[] = "⚠️ DEBUG: Nenhuma secretária encontrada no banco para a Congregação #$congId ($congName). Verifique se o usuário tem papel 'secretary', se o membro tem telefone e se o membro está na congregação #$congId.";
                    } else {
                        $foundNames = array_column($secretaries, 'name');
                        $logMessages[] = "ℹ️ DEBUG: Secretárias encontradas para $congName: " . implode(', ', $foundNames);
                    }
                    
                    foreach ($secretaries as $s) { 
                        // Store full object for better logging
                        $recipients[] = [
                            'phone' => $s['phone'],
                            'name' => $s['name'],
                            'role' => 'Secretária'
                        ]; 
                    }
                    
                    // Fallback removed as requested: Admins do NOT receive notifications.
                    // Only Secretaries of the specific congregation receive them.

                    if (empty($recipients)) {
                        $logMessages[] = "$congName (ID $congId): Sem secretária configurada com telefone. Nenhuma mensagem enviada.";
                        continue;
                    }

                    // Build Message for this congregation
                    $message = "*Aniversariantes do Dia ($today) - $congName*\n\n";
                    foreach ($congBirthdays as $b) {
                        $message .= "🎂 {$b['name']}";
                        if (!empty($b['phone'])) {
                            $phone = preg_replace('/\D/', '', $b['phone']);
                            $message .= " - https://wa.me/55{$phone}";
                        }
                        $message .= "\n";
                    }
                    $message .= "\n_Não esqueça de parabenizá-los!_\n";
                    $message .= "Acesse o sistema da igreja para enviar o cartão de aniversário: ivn.com.br";

                    // Send loop
                    foreach ($recipients as $recipientData) {
                        $recipientPhone = $recipientData['phone'];
                        $recipientName = $recipientData['name'];
                        $recipientRole = $recipientData['role'];
                        
                        // Clean phone
                        $cleanPhone = preg_replace('/\D/', '', $recipientPhone);
                        if (strlen($cleanPhone) <= 11) {
                            $cleanPhone = '55' . $cleanPhone;
                        }
                        
                        // Send ONLY to the cleaned number (no double send)
                        $data = [
                            'number' => $cleanPhone,
                            'options' => [
                                'delay' => 1200,
                                'presence' => 'composing'
                            ],
                            'textMessage' => [
                                'text' => $message
                            ]
                        ];

                        $ch = curl_init($endpoint);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'Content-Type: application/json',
                            'apikey: ' . $token
                        ]);
                        $response = curl_exec($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);
                        
                        // Add delay to prevent block/rate limit
                        sleep(5);
                        
                        if ($httpCode === 200 || $httpCode === 201) {
                            $sentCount++;
                            $logMessages[] = "✅ Enviado para $recipientName ($recipientRole - $congName) | Fone: $cleanPhone | Status: $httpCode | Resp: $response";
                        } else {
                            $logMessages[] = "❌ Erro API ($httpCode) para $recipientName ($cleanPhone): " . $response;
                        }
                    }
                }
                
                ob_end_clean();
                
                if ($sentCount > 0) {
                     $msg = "Mensagens enviadas com sucesso!";
                     if (!empty($logMessages)) {
                         $msg .= " Detalhes:<br>" . implode('<br>', $logMessages);
                     }
                     echo json_encode(['status' => 'success', 'message' => $msg]);
                } else {
                     echo json_encode(['status' => 'error', 'message' => 'Erro ao enviar.<br>' . (empty($logMessages) ? 'Nenhuma resposta da API ou falha desconhecida.' : implode('<br>', $logMessages))]);
                }

            } catch (Exception $e) {
                ob_end_clean();
                echo json_encode(['status' => 'error', 'message' => 'Erro interno: ' . $e->getMessage()]);
            }
        } else {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'Script de cron não encontrado.']);
        }
    }
}
