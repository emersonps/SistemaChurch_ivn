<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório - <?= htmlspecialchars($group['name']) ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }
        * {
            box-sizing: border-box;
        }
        html, body {
            width: 100%;
            max-width: 100%;
        }
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
        table.kpi-table td {
            text-align: center;
        }
        table.kpi-table .kpi-value {
            font-size: 18px;
            font-weight: bold;
            display: block;
        }
        table.kpi-table .kpi-label {
            font-size: 10px;
            color: #444;
            text-transform: uppercase;
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
        <h2>Relatório: <?= htmlspecialchars($group['name']) ?></h2>
    </div>

    <div class="info">
        <p><strong>Congregação:</strong> <?= htmlspecialchars($group['congregation_name'] ?? 'Não definida') ?></p>
        <p><strong>Líder:</strong> <?= htmlspecialchars($group['leader_name'] ?? 'Não definido') ?></p>
        <p><strong>Anfitrião:</strong> <?= htmlspecialchars($group['host_name'] ?? 'Não definido') ?></p>
        <p><strong>Dia/Horário:</strong> <?= htmlspecialchars((string)$group['meeting_day']) ?><?= !empty($group['meeting_time']) ? ' às ' . substr($group['meeting_time'], 0, 5) : '' ?></p>
        <p><strong>Endereço:</strong> <?= htmlspecialchars((string)$group['address']) ?></p>
        <p><strong>Gerado em:</strong> <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <h3 class="section-title">Resumo</h3>
    <table class="kpi-table">
        <tr>
            <td>
                <span class="kpi-value"><?= (int)$stats['total'] ?></span>
                <span class="kpi-label">Total de Membros</span>
            </td>
            <td>
                <span class="kpi-value"><?= (int)$stats['new_converts'] ?></span>
                <span class="kpi-label">Novos Convertidos</span>
            </td>
            <td>
                <span class="kpi-value"><?= (int)$stats['accepted_jesus'] ?></span>
                <span class="kpi-label">Aceitaram a Jesus</span>
            </td>
            <td>
                <span class="kpi-value"><?= (int)$stats['reconciled'] ?></span>
                <span class="kpi-label">Reconciliados</span>
            </td>
        </tr>
    </table>

    <h3 class="section-title">Lista de Participantes</h3>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Função</th>
                <th>Status Espiritual</th>
                <th>Telefone</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($members)): ?>
                <tr><td colspan="4" style="text-align: center;">Nenhum participante.</td></tr>
            <?php else: ?>
                <?php
                    $roleLabel = [
                        'leader' => 'Líder',
                        'host' => 'Anfitrião',
                        'assistant' => 'Auxiliar',
                        'member' => 'Membro',
                        'visitor' => 'Convidado'
                    ];
                ?>
                <?php foreach ($members as $m): ?>
                    <?php
                        $statusParts = [];
                        if (!empty($m['is_new_convert'])) $statusParts[] = 'Novo Convertido';
                        if (!empty($m['accepted_jesus_at'])) $statusParts[] = 'Aceitou Jesus (' . date('d/m/Y', strtotime($m['accepted_jesus_at'])) . ')';
                        if (!empty($m['reconciled_at'])) $statusParts[] = 'Reconciliado (' . date('d/m/Y', strtotime($m['reconciled_at'])) . ')';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($m['name']) ?></td>
                        <td><?= htmlspecialchars($roleLabel[$m['role']] ?? ucfirst($m['role'])) ?></td>
                        <td><?= $statusParts ? htmlspecialchars(implode(' | ', $statusParts)) : '—' ?></td>
                        <td><?= htmlspecialchars($m['phone'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
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
