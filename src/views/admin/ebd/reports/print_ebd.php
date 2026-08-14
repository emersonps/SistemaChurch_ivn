<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório da EBD</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
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
            margin-bottom: 16px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 5px 0 0;
            font-size: 14px;
            font-weight: normal;
        }
        .info {
            margin-bottom: 16px;
        }
        .info p {
            margin: 2px 0;
        }
        h3.section-title {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .02em;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
            margin: 20px 0 8px;
        }
        table {
            width: 100%;
            max-width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #999;
            padding: 5px 7px;
            text-align: left;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        table.kpi-table td { text-align: center; }
        table.kpi-table .kpi-value { font-size: 18px; font-weight: bold; display: block; }
        table.kpi-table .kpi-label { font-size: 10px; color: #444; text-transform: uppercase; }
        .footer {
            margin-top: 24px;
            text-align: center;
            font-size: 10px;
            color: #555;
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
        <h1><?= htmlspecialchars($siteProfile['name'] ?? 'Igreja Vida Nova') ?></h1>
        <h2>Relatório da Escola Bíblica Dominical</h2>
    </div>

    <div class="info">
        <p><strong>Período:</strong> <?= date('d/m/Y', strtotime($start_date)) ?> a <?= date('d/m/Y', strtotime($end_date)) ?></p>
        <p><strong>Gerado em:</strong> <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <h3 class="section-title">Resumo do Período</h3>
    <table class="kpi-table">
        <tr>
            <td>
                <span class="kpi-value"><?= $period_stats['total_attendance'] ?: 0 ?></span>
                <span class="kpi-label">Total Presenças</span>
            </td>
            <td>
                <span class="kpi-value">R$ <?= number_format($period_stats['total_offerings'] ?: 0, 2, ',', '.') ?></span>
                <span class="kpi-label">Ofertas</span>
            </td>
            <td>
                <span class="kpi-value"><?= $period_stats['total_visitors'] ?: 0 ?></span>
                <span class="kpi-label">Visitantes</span>
            </td>
            <td>
                <span class="kpi-value"><?= $period_stats['total_bibles'] ?: 0 ?> / <?= $period_stats['total_magazines'] ?: 0 ?></span>
                <span class="kpi-label">Bíblias / Revistas</span>
            </td>
        </tr>
    </table>

    <h3 class="section-title">Por Dia (Aulas)</h3>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Turmas</th>
                <th>Presenças</th>
                <th>Visitantes</th>
                <th>Bíblias</th>
                <th>Revistas</th>
                <th>Oferta Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($daily_stats)): ?>
                <tr><td colspan="7" style="text-align: center;">Nenhuma aula registrada neste período.</td></tr>
            <?php else: ?>
                <?php foreach ($daily_stats as $day): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($day['lesson_date'])) ?></td>
                    <td><?= $day['classes_count'] ?></td>
                    <td><?= $day['total_attendance'] ?></td>
                    <td><?= $day['total_visitors'] ?></td>
                    <td><?= $day['total_bibles'] ?></td>
                    <td><?= $day['total_magazines'] ?></td>
                    <td>R$ <?= number_format($day['total_offerings'] ?: 0, 2, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <h3 class="section-title">Por Classe</h3>
    <table>
        <thead>
            <tr>
                <th>Classe</th>
                <th>Aulas</th>
                <th>Matriculados</th>
                <th>Presenças</th>
                <th>Faltas</th>
                <th>Visitantes</th>
                <th>Ofertas</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($classes_stats)): ?>
                <tr><td colspan="7" style="text-align: center;">Nenhuma classe encontrada.</td></tr>
            <?php else: ?>
                <?php foreach ($classes_stats as $cls): ?>
                <tr>
                    <td><?= htmlspecialchars($cls['name']) ?></td>
                    <td><?= $cls['lessons_given'] ?></td>
                    <td><?= $cls['current_students'] ?></td>
                    <td><?= $cls['total_presence'] ?></td>
                    <td><?= $cls['total_absences'] ?></td>
                    <td><?= $cls['total_visitors'] ?></td>
                    <td>R$ <?= number_format($cls['total_offerings'] ?: 0, 2, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Sistema de Gestão de Membros</p>
    </div>

</body>
</html>
