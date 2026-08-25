<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Geral de Estatísticas</title>
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
        <h2>Relatório Geral de Estatísticas</h2>
    </div>

    <?php
    $printCongName = 'Todas as congregações';
    if (!empty($filters['congregation_id'])) {
        foreach ($congregations as $cong) {
            if ($cong['id'] == $filters['congregation_id']) {
                $printCongName = $cong['name'];
                break;
            }
        }
    }

    $visitorsCount = 0;
    foreach ($peopleStats as $stat) {
        if (($stat['action_type'] ?? '') === 'Visitante') {
            $visitorsCount = (int)($stat['total'] ?? 0);
            break;
        }
    }

    $conversionsCount = 0;
    foreach ($peopleStats as $stat) {
        if (in_array(($stat['action_type'] ?? ''), ['Aceitou Jesus', 'Reconciliado', 'Conversão', 'Reconciliação'], true)) {
            $conversionsCount += (int)($stat['total'] ?? 0);
        }
    }

    $totalServices = (int)($attendanceStats['total_services'] ?? 0);
    $totalAttendance = (int)($attendanceStats['total_men'] ?? 0)
        + (int)($attendanceStats['total_women'] ?? 0)
        + (int)($attendanceStats['total_youth'] ?? 0)
        + (int)($attendanceStats['total_children'] ?? 0)
        + (int)($attendanceStats['total_visitors'] ?? 0);
    $avgAttendance = $totalServices > 0 ? (int)round($totalAttendance / $totalServices) : 0;
    ?>

    <div class="info">
        <p><strong>Período:</strong> <?= date('d/m/Y', strtotime($filters['start_date'])) ?> a <?= date('d/m/Y', strtotime($filters['end_date'])) ?></p>
        <p><strong>Congregação:</strong> <?= htmlspecialchars($printCongName) ?></p>
        <p><strong>Gerado em:</strong> <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <h3 class="section-title">Resumo Geral</h3>
    <table class="kpi-table">
        <tr>
            <td>
                <span class="kpi-value"><?= $totalServices ?></span>
                <span class="kpi-label">Cultos Realizados</span>
            </td>
            <td>
                <span class="kpi-value"><?= $visitorsCount ?></span>
                <span class="kpi-label">Visitantes (Únicos)</span>
            </td>
            <td>
                <span class="kpi-value"><?= $conversionsCount ?></span>
                <span class="kpi-label">Decisões / Conversões</span>
            </td>
            <td>
                <span class="kpi-value"><?= $avgAttendance ?></span>
                <span class="kpi-label">Média de Público</span>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Homens</th>
                <th>Mulheres</th>
                <th>Jovens</th>
                <th>Crianças</th>
                <th>Visitantes</th>
                <th>Total de Presença</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= (int)($attendanceStats['total_men'] ?? 0) ?></td>
                <td><?= (int)($attendanceStats['total_women'] ?? 0) ?></td>
                <td><?= (int)($attendanceStats['total_youth'] ?? 0) ?></td>
                <td><?= (int)($attendanceStats['total_children'] ?? 0) ?></td>
                <td><?= (int)($attendanceStats['total_visitors'] ?? 0) ?></td>
                <td><strong><?= $totalAttendance ?></strong></td>
            </tr>
        </tbody>
    </table>

    <h3 class="section-title">Movimentação de Pessoas</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Tipo de Ação</th>
                <th style="width: 10%;">Quantidade</th>
                <th>Nomes</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($peopleStats)): ?>
                <tr>
                    <td colspan="3" style="text-align: center;">Nenhum registro encontrado no período.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($peopleStats as $stat): ?>
                    <tr>
                        <td><?= htmlspecialchars($stat['action_type']) ?></td>
                        <td style="text-align: center;"><?= $stat['total'] ?></td>
                        <td><?= htmlspecialchars($stat['names']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <h3 class="section-title">Escola Bíblica Dominical (Atual)</h3>
    <table>
        <thead>
            <tr>
                <th>Classes Ativas</th>
                <th>Alunos Matriculados</th>
                <th>Professores</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= $ebdStats['total_classes'] ?></td>
                <td><?= $ebdStats['total_students'] ?></td>
                <td><?= $ebdStats['total_teachers'] ?></td>
            </tr>
        </tbody>
    </table>

    <h3 class="section-title">Grupos e Células (Atual)</h3>
    <table>
        <thead>
            <tr>
                <th>Grupos Ativos</th>
                <th>Total de Participantes</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= $groupStats['total_groups'] ?></td>
                <td><?= $groupStats['total_members'] ?? 0 ?></td>
            </tr>
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
