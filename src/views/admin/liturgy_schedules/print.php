<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($schedule['title']) ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        * { box-sizing: border-box; }
        html, body { width: 100%; max-width: 100%; }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #1a1a1a;
        }
        .header {
            text-align: center;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 3px solid #C89A3D;
        }
        .header-logo {
            display: block;
            margin: 0 auto 8px;
            max-height: 70px;
            max-width: 220px;
            object-fit: contain;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
            color: #252525;
        }
        .header h2 {
            margin: 4px 0 0;
            font-size: 13px;
            font-weight: normal;
            color: #666;
        }
        table {
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        th, td {
            border: 1px solid #C89A3D;
            padding: 7px 8px;
            text-align: center;
            word-wrap: break-word;
        }
        th {
            background-color: #252525;
            color: #fff;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .02em;
        }
        td.ls-date { font-weight: bold; color: #C89A3D; white-space: nowrap; }
        td.ls-weekday { font-size: 10px; color: #666; white-space: nowrap; }
        tbody tr:nth-child(odd) td { background-color: #F4E9CF; }
        tbody tr:nth-child(even) td { background-color: #ffffff; }
        .notes-box {
            background: #F5F5F5;
            border: 1px solid #d9d9d9;
            border-radius: 4px;
            padding: 10px 14px;
            font-size: 11px;
            color: #444;
            margin-top: 6px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
        .no-print { margin-bottom: 20px; text-align: center; }
        .no-print button {
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            margin: 0 4px;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    <?php $siteProfile = getChurchSiteProfileSettings(); ?>

    <div class="no-print">
        <button onclick="window.print()">Imprimir</button>
        <button onclick="window.close()">Fechar</button>
    </div>

    <div class="header">
        <img class="header-logo" src="<?= htmlspecialchars(getChurchLogoUrl($siteProfile, true)) ?>" alt="" onerror="this.style.display='none'">
        <h1><?= htmlspecialchars($schedule['title']) ?></h1>
        <h2><?= htmlspecialchars($schedule['congregation_name'] ?? 'Todas as congregações') ?></h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Culto</th>
                <?php foreach ($rolesConfig as $role): ?>
                    <th><?= htmlspecialchars($role['label']) ?></th>
                <?php endforeach; ?>
                <th>Observações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($entries)): ?>
                <tr><td colspan="<?= 3 + count($rolesConfig) ?>">Nenhum culto nesta escala.</td></tr>
            <?php else: ?>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td class="ls-date">
                            <?= date('d/m', strtotime($entry['service_date'])) ?>
                            <div class="ls-weekday"><?= htmlspecialchars($entry['weekday']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($entry['service_label'] ?? '') ?></td>
                        <?php foreach ($rolesConfig as $role): ?>
                            <td><?= htmlspecialchars($entry['values'][$role['key']] ?? '') ?></td>
                        <?php endforeach; ?>
                        <td><?= htmlspecialchars($entry['values']['observacoes'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (!empty($schedule['notes'])): ?>
        <div class="notes-box"><?= nl2br(htmlspecialchars($schedule['notes'])) ?></div>
    <?php endif; ?>

    <div class="footer">
        <p>Gerado em <?= date('d/m/Y H:i') ?></p>
    </div>

</body>
</html>
