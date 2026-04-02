<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = (new Database())->connect();
    
    echo "--- Inserindo Eventos de Teste (Seeds) ---\n";
    
    // Buscar uma congregação existente para vincular
    $congName = $db->query("SELECT name FROM congregations LIMIT 1")->fetchColumn();
    if (!$congName) {
        $congName = 'IMPVC Sede';
        $db->query("INSERT INTO congregations (name, type) VALUES ('$congName', 'Sede')");
    }

    $stmt = $db->prepare("INSERT INTO events (
        title, description, event_date, end_time, location, address, 
        recurring_days, type, status, contact_email, contact_phone
    ) VALUES (
        ?, ?, ?, ?, ?, ?, 
        ?, ?, ?, ?, ?
    )");

    $categories = [
        'culto' => [
            ['Culto de Doutrina', 'Estudo bíblico profundo', 'Terça', '19:30'],
            ['Culto da Família', 'Venha com sua família', 'Domingo', '18:00'],
            ['Círculo de Oração', 'Manhã de clamor', 'Quinta', '09:00']
        ],
        'congresso' => [
            ['Congresso de Jovens 2026', 'Dias de muito louvor', '2026-07-15 19:00'],
            ['Congresso de Senhoras', 'Mulheres de oração', '2026-09-20 18:30']
        ],
        'aniversario' => [
            ['Aniversário do Pastor', 'Culto em ação de graças', '2026-05-10 19:00'],
            ['Aniversário da Igreja', '60 anos de história', '2026-11-12 18:00']
        ],
        'convite' => [
            ['Cruzada Evangelística', 'Grande culto ao ar livre', '2026-04-20 19:00'],
            ['Jantar de Casais', 'Noite especial para casais', '2026-06-12 20:00']
        ],
        'evento' => [
            ['Bazar Beneficente', 'Roupas e calçados', '2026-08-05 09:00'],
            ['Retiro Espiritual', 'Fim de semana na chácara', '2026-10-30 18:00']
        ]
    ];

    foreach ($categories as $type => $events) {
        foreach ($events as $e) {
            $title = $e[0];
            $desc = $e[1];
            $dateInfo = $e[2]; // Pode ser data ou dia da semana
            $time = isset($e[3]) ? $e[3] : null; // Horário recorrente
            
            $eventDate = null;
            $recurringDays = null;
            
            // Verifica se é dia da semana (Recorrente) ou Data Específica
            if (in_array($dateInfo, ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'])) {
                $recurringDays = json_encode([$dateInfo]);
                if ($time) {
                     // Formato fictício para banco, apenas hora importa
                     $eventDate = '1970-01-01 ' . $time; 
                }
            } else {
                $eventDate = $dateInfo; // Já vem com hora no array acima
            }
            
            $stmt->execute([
                $title,
                $desc,
                $eventDate,
                null, // end_time
                $congName,
                'Rua da Igreja, 123',
                $recurringDays,
                $type,
                'active',
                'contato@igreja.com',
                '(11) 99999-9999'
            ]);
            
            echo "✅ Evento inserido: $title ($type)\n";
        }
    }
    
    echo "\n--- Concluído! Eventos adicionados. ---\n";

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
