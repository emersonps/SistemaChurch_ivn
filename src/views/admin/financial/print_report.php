<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Financeiro</title>
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
        <h2>Relatório Financeiro</h2>
    </div>

    <div class="info">
        <p><strong>Período:</strong> <?= date('d/m/Y', strtotime($filters['start_date'])) ?> a <?= date('d/m/Y', strtotime($filters['end_date'])) ?></p>
        <p><strong>Congregação:</strong> <?= $filters['congregation_name'] ? htmlspecialchars($filters['congregation_name']) : 'Todas (Geral)' ?></p>
        <p><strong>Gerado em:</strong> <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <h3 class="section-title">Resumo do Período</h3>
    <table class="kpi-table">
        <tr>
            <td>
                <span class="kpi-value">R$ <?= number_format($total_entries, 2, ',', '.') ?></span>
                <span class="kpi-label">Entradas</span>
            </td>
            <td>
                <span class="kpi-value">R$ <?= number_format($total_expenses, 2, ',', '.') ?></span>
                <span class="kpi-label">Saídas</span>
            </td>
            <td>
                <span class="kpi-value">R$ <?= number_format($balance, 2, ',', '.') ?></span>
                <span class="kpi-label">Saldo (<?= $balance >= 0 ? 'Positivo' : 'Negativo' ?>)</span>
            </td>
        </tr>
    </table>

    <h3 class="section-title">Detalhamento de Saídas</h3>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Congregação</th>
                <th class="text-end">Valor</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($expenses)): ?>
                <tr><td colspan="5" style="text-align: center;">Nenhuma saída registrada neste período.</td></tr>
            <?php else: ?>
                <?php foreach ($expenses as $e): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($e['expense_date'])) ?></td>
                    <td><?= htmlspecialchars($e['description']) ?></td>
                    <td><?= htmlspecialchars($e['category']) ?></td>
                    <td><?= htmlspecialchars($e['congregation_name'] ?? 'Geral') ?></td>
                    <td class="text-end">- R$ <?= number_format($e['amount'], 2, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="4" class="text-end">TOTAL SAÍDAS:</td>
                    <td class="text-end">R$ <?= number_format($total_expenses, 2, ',', '.') ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (!empty($expenses_by_category)): ?>
    <h3 class="section-title">Resumo por Categoria de Despesa</h3>
    <table>
        <thead>
            <tr>
                <th>Categoria</th>
                <th class="text-end">Valor</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($expenses_by_category as $cat => $amount): ?>
            <tr>
                <td><?= htmlspecialchars($cat) ?></td>
                <td class="text-end">R$ <?= number_format($amount, 2, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <h3 class="section-title">Detalhamento de Entradas</h3>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Tipo</th>
                <th>Nome (Membro/Doador)</th>
                <th>Congregação</th>
                <th class="text-end">Valor</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($entries)): ?>
                <tr><td colspan="5" style="text-align: center;">Nenhuma entrada registrada neste período.</td></tr>
            <?php else: ?>
                <?php foreach ($entries as $en):
                    $displayName = $en['member_name'] ?? $en['giver_name'];
                    if (empty($displayName)) {
                        if ($en['payment_method'] === 'Transferência/OFX' && !empty($en['notes'])) {
                            $displayName = 'OFX: ' . $en['notes'];
                        } elseif (!empty($en['notes'])) {
                            $displayName = 'Obs: ' . mb_strimwidth($en['notes'], 0, 30, '...');
                        } else {
                            $displayName = 'Não Identificado (' . $en['payment_method'] . ')';
                        }
                    }
                ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($en['payment_date'])) ?></td>
                    <td><?= htmlspecialchars((string)$en['type']) ?></td>
                    <td><?= htmlspecialchars((string)$displayName) ?></td>
                    <td><?= htmlspecialchars((string)($en['congregation_name'] ?? 'Geral')) ?></td>
                    <td class="text-end">+ R$ <?= number_format($en['amount'], 2, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="4" class="text-end">TOTAL ENTRADAS:</td>
                    <td class="text-end">R$ <?= number_format($total_entries, 2, ',', '.') ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="signature-block" style="margin-top: 30px;">
        <?php if (!empty($reportSignatures)): ?>
            <div style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 15px;">
                <?php foreach ($reportSignatures as $sig): ?>
                    <div style="text-align: center;">
                        <?php if (!empty($sig['image_path'])): ?>
                            <img src="/uploads/signatures/<?= htmlspecialchars($sig['image_path']) ?>" style="max-height: 45px; max-width: 140px;" alt="Assinatura"><br>
                        <?php endif; ?>
                        <span style="display:inline-block; border-top: 1px solid #000; padding-top: 3px; font-size: 11px; min-width: 160px;">
                            <?= htmlspecialchars($sig['name'] ?? $sig['role_label']) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p>Sistema de Gestão de Membros</p>
    </div>

</body>
</html>
