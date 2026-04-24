<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Presença - <?= htmlspecialchars($event['title']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            font-weight: normal;
        }
        .info {
            margin-bottom: 15px;
        }
        .info p {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <?php $siteProfile = getChurchSiteProfileSettings(); ?>

    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">Imprimir</button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">Fechar</button>
    </div>

    <div class="header">
        <h1><?= htmlspecialchars($siteProfile['name'] ?? 'Igreja Vida Nova') ?></h1>
        <h2>Relatório de Lista de Presença</h2>
    </div>

    <div class="info">
        <p><strong>Evento:</strong> <?= htmlspecialchars($event['title']) ?></p>
        <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($event['event_date'])) ?> às <?= date('H:i', strtotime($event['event_date'])) ?></p>
        <p><strong>Local:</strong> <?= htmlspecialchars($event['location']) ?></p>
        <p><strong>Total de Presentes:</strong> <?= count($attendees) ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 45%;">Nome do Membro</th>
                <th style="width: 20%;">Cargo/Função</th>
                <th style="width: 20%;">Congregação</th>
                <th style="width: 10%;">Horário</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($attendees)): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Nenhuma presença registrada.</td>
                </tr>
            <?php else: ?>
                <?php $i = 1; foreach ($attendees as $att): ?>
                    <tr>
                        <td style="text-align: center;"><?= $i++ ?></td>
                        <td><?= mb_convert_case($att['name'], MB_CASE_TITLE, "UTF-8") ?></td>
                        <td><?= htmlspecialchars($att['role'] ?? 'Membro') ?></td>
                        <td><?= htmlspecialchars($att['congregation_name'] ?? '-') ?></td>
                        <td style="text-align: center;"><?= date('H:i', strtotime($att['scanned_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Relatório gerado em <?= date('d/m/Y H:i:s') ?></p>
        <p>Sistema de Gestão de Membros</p>
    </div>

</body>
</html>
