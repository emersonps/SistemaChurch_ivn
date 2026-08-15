<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fechamento Financeiro - <?= htmlspecialchars($closure['period']) ?></title>
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
        td.text-end, th.text-end { text-align: right; }
        tr.total-row td { font-weight: bold; background-color: #f0f0f0; }
        .note {
            font-size: 10px;
            color: #444;
            font-style: italic;
        }
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
        <h2>Fechamento Financeiro - <?= htmlspecialchars($closure['type']) ?> <?= htmlspecialchars($closure['period']) ?></h2>
    </div>

    <div class="info">
        <p><strong>Congregação:</strong> <?= htmlspecialchars($closure['congregation_name'] ?? 'Geral') ?></p>
        <p><strong>Período:</strong> <?= date('d/m/Y', strtotime($closure['start_date'])) ?> a <?= date('d/m/Y', strtotime($closure['end_date'])) ?></p>
        <p><strong>Gerado por:</strong> <?= htmlspecialchars($closure['creator_name'] ?? '-') ?> em <?= date('d/m/Y H:i', strtotime($closure['created_at'])) ?></p>
        <p><strong>Impresso em:</strong> <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <h3 class="section-title">Resumo do Período</h3>
    <table class="kpi-table">
        <tr>
            <td>
                <span class="kpi-value">R$ <?= number_format($closure['total_entries'], 2, ',', '.') ?></span>
                <span class="kpi-label">Entradas</span>
            </td>
            <td>
                <span class="kpi-value">R$ <?= number_format($closure['total_expenses'], 2, ',', '.') ?></span>
                <span class="kpi-label">Saídas</span>
            </td>
            <td>
                <span class="kpi-value">R$ <?= number_format($closure['balance'], 2, ',', '.') ?></span>
                <span class="kpi-label">Saldo do Período</span>
            </td>
            <td>
                <span class="kpi-value">R$ <?= number_format($closure['final_balance'], 2, ',', '.') ?></span>
                <span class="kpi-label">Saldo Final (Acumulado)</span>
            </td>
        </tr>
    </table>

    <h3 class="section-title">Detalhamento de Entradas</h3>
    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th class="text-end">Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Dízimos</td>
                <td class="text-end">R$ <?= number_format($closure['total_tithes'], 2, ',', '.') ?></td>
            </tr>
            <tr>
                <td>Ofertas</td>
                <td class="text-end">R$ <?= number_format($closure['total_offerings'], 2, ',', '.') ?></td>
            </tr>
            <tr class="total-row">
                <td>TOTAL ENTRADAS</td>
                <td class="text-end">R$ <?= number_format($closure['total_entries'], 2, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <h3 class="section-title">Detalhamento de Saídas</h3>
    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th class="text-end">Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr class="total-row">
                <td>TOTAL SAÍDAS</td>
                <td class="text-end">R$ <?= number_format($closure['total_expenses'], 2, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>
    <p class="note">Para ver os lançamentos individuais de saída, consulte o relatório financeiro do período.</p>

    <h3 class="section-title">Saldo Acumulado</h3>
    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th class="text-end">Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Saldo Anterior</td>
                <td class="text-end">R$ <?= number_format($closure['previous_balance'], 2, ',', '.') ?></td>
            </tr>
            <tr>
                <td>Saldo do Período</td>
                <td class="text-end">R$ <?= number_format($closure['balance'], 2, ',', '.') ?></td>
            </tr>
            <tr class="total-row">
                <td>SALDO FINAL</td>
                <td class="text-end">R$ <?= number_format($closure['final_balance'], 2, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Sistema de Gestão de Membros</p>
    </div>

</body>
</html>
