<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Relatório Geral de Estatísticas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/reports/general/print?start_date=<?= urlencode($filters['start_date']) ?>&end_date=<?= urlencode($filters['end_date']) ?>&congregation_id=<?= urlencode((string)$filters['congregation_id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
            <i class="fas fa-print me-1"></i> Visualizar / Imprimir
        </a>
    </div>
</div>

<style>
    .member-form-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
    }
    .member-form-card-header {
        display: flex;
        align-items: flex-start;
        gap: .85rem;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.07);
        background: #fafafa;
    }
    .member-form-badge {
        flex: 0 0 auto;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #eef0f2;
        color: #212529;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: .95rem;
    }
    .member-form-card-title {
        font-weight: 800;
        font-size: 1.05rem;
        color: #1a1a1a;
        line-height: 1.2;
    }
    .member-form-card-subtitle {
        font-size: .82rem;
        color: #868e96;
        margin-top: .1rem;
    }
    .member-form-card-body { padding: 1.25rem; }
    .member-form-card-body .form-label {
        font-weight: 600;
        font-size: .88rem;
        color: #343a40;
    }
    .member-form-card-body .form-control,
    .member-form-card-body .form-select {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
        padding: .55rem .8rem;
    }
    .member-form-card-body .form-control:focus,
    .member-form-card-body .form-select:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }

    .kpi-tile {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        padding: 1.1rem 1.25rem;
        height: 100%;
        display: flex;
        align-items: center;
        gap: .9rem;
    }
    .kpi-tile .kpi-icon {
        flex: 0 0 auto;
        width: 46px;
        height: 46px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }
    .kpi-tile .kpi-value {
        font-weight: 800;
        font-size: 1.6rem;
        color: #1a1a1a;
        line-height: 1.1;
    }
    .kpi-tile .kpi-label {
        font-size: .78rem;
        color: #868e96;
        font-weight: 600;
    }
    .kpi-tile.kpi-primary .kpi-icon { background: rgba(179,0,0,0.10); color: #b30000; }
    .kpi-tile.kpi-success .kpi-icon { background: rgba(25,135,84,0.12); color: #198754; }
    .kpi-tile.kpi-warning .kpi-icon { background: rgba(212,175,55,0.18); color: #a6790a; }
    .kpi-tile.kpi-info .kpi-icon { background: rgba(13,110,253,0.10); color: #0d6efd; }

    .reports-table thead th {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .reports-table td {
        vertical-align: middle;
        padding-top: .6rem;
        padding-bottom: .6rem;
    }
    .action-pill {
        display: inline-block;
        padding: .25rem .6rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
    }
    .action-pill.pill-success { background: rgba(25,135,84,0.12); color: #198754; }
    .action-pill.pill-warning { background: rgba(212,175,55,0.18); color: #a6790a; }
    .action-pill.pill-info { background: rgba(13,110,253,0.10); color: #0d6efd; }
    .action-pill.pill-primary { background: rgba(179,0,0,0.10); color: #b30000; }
    .action-pill.pill-danger { background: rgba(220,53,69,0.10); color: #dc3545; }
    .action-pill.pill-dark { background: rgba(0,0,0,0.08); color: #343a40; }

    .stat-list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: .85rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.06);
        font-size: .92rem;
    }
    .stat-list-item:last-child { border-bottom: none; }
    .stat-list-item .stat-count-pill {
        display: inline-flex;
        align-items: center;
        padding: .3rem .7rem;
        border-radius: 999px;
        font-size: .8rem;
        font-weight: 700;
        background: #eef0f2;
        color: #495057;
    }
</style>

<!-- Filtros -->
<div class="member-form-card mb-4">
    <div class="member-form-card-body">
        <form method="GET" class="row g-3">
            <div class="col-6 col-md-3">
                <label for="start_date" class="form-label">Data Início</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $filters['start_date'] ?>">
            </div>
            <div class="col-6 col-md-3">
                <label for="end_date" class="form-label">Data Fim</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $filters['end_date'] ?>">
            </div>
            <div class="col-md-3">
                <label for="congregation_id" class="form-label">Congregação</label>
                <select class="form-select" id="congregation_id" name="congregation_id">
                    <option value="">Todas</option>
                    <?php foreach ($congregations as $cong): ?>
                        <option value="<?= $cong['id'] ?>" <?= $filters['congregation_id'] == $cong['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cong['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary rounded-pill fw-semibold w-100">
                    <i class="fas fa-filter me-2"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<?php
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

function reportActionPillClass($actionType) {
    switch ($actionType) {
        case 'Visitante': return 'pill-success';
        case 'Aceitou Jesus':
        case 'Conversão': return 'pill-warning';
        case 'Reconciliado':
        case 'Reconciliação': return 'pill-info';
        case 'Batismo': return 'pill-primary';
        case 'Desligamento': return 'pill-danger';
        case 'Disciplinado': return 'pill-dark';
        default: return 'pill-dark';
    }
}
?>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-tile kpi-primary">
            <div class="kpi-icon"><i class="fas fa-church"></i></div>
            <div>
                <div class="kpi-value"><?= $attendanceStats['total_services'] ?? 0 ?></div>
                <div class="kpi-label">Cultos Realizados</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-tile kpi-success">
            <div class="kpi-icon"><i class="fas fa-user-friends"></i></div>
            <div>
                <div class="kpi-value"><?= $visitorsCount ?></div>
                <div class="kpi-label">Visitantes (Únicos)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-tile kpi-warning">
            <div class="kpi-icon"><i class="fas fa-hands-praying"></i></div>
            <div>
                <div class="kpi-value"><?= $conversionsCount ?></div>
                <div class="kpi-label">Decisões / Conversões</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-tile kpi-info">
            <div class="kpi-icon"><i class="fas fa-chart-line"></i></div>
            <div>
                <div class="kpi-value"><?= $avgAttendance ?></div>
                <div class="kpi-label">Média de Público</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Movimentação de Pessoas -->
    <div class="col-lg-8">
        <div class="member-form-card h-100">
            <div class="member-form-card-header">
                <div class="member-form-badge"><i class="fas fa-users"></i></div>
                <div>
                    <div class="member-form-card-title">Movimentação de Pessoas</div>
                    <div class="member-form-card-subtitle">Detalhado por tipo de ação, no período selecionado.</div>
                </div>
            </div>
            <div class="member-form-card-body">
                <?php if (empty($peopleStats)): ?>
                    <p class="text-center text-muted py-4 mb-0">Nenhum registro encontrado no período.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover reports-table mb-0">
                            <thead>
                                <tr>
                                    <th>Tipo de Ação</th>
                                    <th class="text-center">Quantidade</th>
                                    <th>Nomes (Resumo)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($peopleStats as $stat): ?>
                                <tr>
                                    <td>
                                        <span class="action-pill <?= reportActionPillClass($stat['action_type']) ?>"><?= htmlspecialchars($stat['action_type']) ?></span>
                                    </td>
                                    <td class="text-center fw-bold fs-5"><?= $stat['total'] ?></td>
                                    <td class="small text-muted text-truncate" style="max-width: 300px;" title="<?= htmlspecialchars($stat['names']) ?>">
                                        <?= htmlspecialchars(mb_strimwidth($stat['names'], 0, 100, "...")) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Cards Laterais: EBD e Grupos -->
    <div class="col-lg-4">
        <div class="member-form-card mb-3">
            <div class="member-form-card-header">
                <div class="member-form-badge"><i class="fas fa-book-open"></i></div>
                <div>
                    <div class="member-form-card-title">Escola Bíblica</div>
                    <div class="member-form-card-subtitle">Situação atual.</div>
                </div>
            </div>
            <div>
                <div class="stat-list-item">
                    <span>Classes Ativas</span>
                    <span class="stat-count-pill"><?= $ebdStats['total_classes'] ?></span>
                </div>
                <div class="stat-list-item">
                    <span>Alunos Matriculados</span>
                    <span class="stat-count-pill"><?= $ebdStats['total_students'] ?></span>
                </div>
                <div class="stat-list-item">
                    <span>Professores</span>
                    <span class="stat-count-pill"><?= $ebdStats['total_teachers'] ?></span>
                </div>
            </div>
        </div>

        <div class="member-form-card">
            <div class="member-form-card-header">
                <div class="member-form-badge"><i class="fas fa-users-cog"></i></div>
                <div>
                    <div class="member-form-card-title">Grupos e Células</div>
                    <div class="member-form-card-subtitle">Situação atual.</div>
                </div>
            </div>
            <div>
                <div class="stat-list-item">
                    <span>Grupos Ativos</span>
                    <span class="stat-count-pill"><?= $groupStats['total_groups'] ?></span>
                </div>
                <div class="stat-list-item">
                    <span>Total de Participantes</span>
                    <span class="stat-count-pill"><?= $groupStats['total_members'] ?? 0 ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
