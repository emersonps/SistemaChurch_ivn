<?php
// cron_birthdays.php
// Este script deve ser configurado no Cron Job do cPanel para rodar 1x ao dia (ex: 08:00 AM)
// Comando: php /caminho/do/sistema/cron_birthdays.php
//

require_once __DIR__ . '/../config/database.php';

echo "Iniciando verificação de aniversariantes do dia...\n";

$db = (new Database())->connect();

// 1. Pegar configurações da API
$settingsDb = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('whatsapp_api_url', 'whatsapp_api_instance', 'whatsapp_api_token')")->fetchAll(PDO::FETCH_KEY_PAIR);

$apiUrl = rtrim($settingsDb['whatsapp_api_url'] ?? '', '/');
$apiInstance = $settingsDb['whatsapp_api_instance'] ?? '';
$apiToken = $settingsDb['whatsapp_api_token'] ?? '';

if (empty($apiUrl) || empty($apiInstance) || empty($apiToken)) {
    die("Erro: Configurações da API de WhatsApp incompletas no painel administrativo.\n");
}

// 2. Buscar aniversariantes do dia
$today = date('d/m');
list($day, $month) = explode('/', $today);

// SQLite vs MySQL adjustment
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

// Status check: Accept 'active' OR 'Congregando'
$statusCondition = "AND (status = 'active' OR status = 'Congregando')";

if ($driver === 'sqlite') {
    // Use SUBSTR logic as in SettingsController for better compatibility
    $month = str_pad($month, 2, '0', STR_PAD_LEFT);
    $day = str_pad($day, 2, '0', STR_PAD_LEFT);
    $monthDay = "{$month}-{$day}";
    
    $sqlBirthdays = "SELECT name, phone FROM members WHERE substr(birth_date, 6, 5) = ? $statusCondition";
    $stmt = $db->prepare($sqlBirthdays);
    $stmt->execute([$monthDay]);
} else {
    // MySQL
    $sqlBirthdays = "SELECT name, phone FROM members WHERE DATE_FORMAT(birth_date, '%d/%m') = ? $statusCondition";
    $stmt = $db->prepare($sqlBirthdays);
    $stmt->execute([$today]);
}

$birthdays = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($birthdays)) {
    die("Nenhum aniversariante hoje ($today).\n");
}

echo "Encontrados " . count($birthdays) . " aniversariantes.\n";

// 3. Segment Birthdays by Congregation
$birthdaysByCongregation = [];
foreach ($birthdays as $b) {
    $congId = $b['congregation_id'] ?? '0'; // '0' or 'NULL' for Headquarters/Unknown
    if (!isset($birthdaysByCongregation[$congId])) {
        $birthdaysByCongregation[$congId] = [];
    }
    $birthdaysByCongregation[$congId][] = $b;
}

echo "Enviando mensagens segmentadas por congregação...\n";

foreach ($birthdaysByCongregation as $congId => $congBirthdays) {
    // Find Recipients (Secretary of THIS congregation)
    $recipients = [];
    
    // Search for secretaries linked to this congregation
    $secSql = "SELECT m.phone FROM users u 
               JOIN members m ON u.member_id = m.id 
               WHERE u.role = 'secretary' 
               AND m.congregation_id = ? 
               AND m.phone IS NOT NULL AND m.phone != ''";
    
    $secStmt = $db->prepare($secSql);
    $secStmt->execute([$congId]);
    $secretaries = $secStmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($secretaries as $s) { $recipients[] = $s; }
    
    // Fallback REMOVED: Admins do NOT receive notifications.
    
    if (empty($recipients)) {
        echo "Aviso: Congregação $congId sem secretária configurada. Nenhuma mensagem enviada.\n";
        continue;
    }

    // Build Message for this congregation
    // Fetch Congregation Name
    $congName = "Sede";
    if ($congId > 0) {
        $cStmt = $db->prepare("SELECT name FROM congregations WHERE id = ?");
        $cStmt->execute([$congId]);
        $congName = $cStmt->fetchColumn() ?: "Congregação #$congId";
    }

    $message = "*Aniversariantes do Dia ($today) - $congName*\n\n";
    foreach ($congBirthdays as $b) {
        $message .= "🎂 " . $b['name'];
        if (!empty($b['phone'])) {
            $phone = preg_replace('/\D/', '', $b['phone']);
            $message .= " - https://wa.me/55{$phone}";
        }
        $message .= "\n";
    }
    $message .= "\n_Não esqueça de parabenizá-los!_\n";
    $message .= "Acesse o sistema da igreja para enviar o cartão de aniversário: impvc.com.br";

    // Send loop
    foreach ($recipients as $phone) {
        $number = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($number) <= 11) $number = '55' . $number;
        
        // Single send logic (Double send removed as requested)
        $endpoint = "{$apiUrl}/message/sendText/{$apiInstance}";
        $data = [
            'number' => $number,
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
            'apikey: ' . $apiToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        sleep(5); // Increased Delay
        
        echo "Enviado para $number (Cong. $congName): Código $httpCode\n";
    }
}

echo "Processo finalizado.\n";
