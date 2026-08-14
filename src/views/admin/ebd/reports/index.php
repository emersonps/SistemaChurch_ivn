<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Relatórios da EBD</h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="/admin/ebd/classes" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
        <a href="/admin/ebd/reports/print?start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold px-3">
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
    .filter-card .form-control,
    .filter-card .form-select {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
    }
    .filter-card .form-control:focus {
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
        font-size: 1.5rem;
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
    .kpi-tile.kpi-info .kpi-icon { background: rgba(13,110,253,0.10); color: #0d6efd; }
    .kpi-tile.kpi-warning .kpi-icon { background: rgba(212,175,55,0.18); color: #a6790a; }

    #ebdReportTabs.nav-tabs {
        border-bottom: none;
        gap: .4rem;
    }
    #ebdReportTabs.nav-tabs .nav-link {
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 999px;
        padding: .45rem 1rem;
        font-weight: 700;
        font-size: .85rem;
        color: #495057;
        background: #fff;
    }
    #ebdReportTabs.nav-tabs .nav-link.active {
        background: #b30000;
        border-color: #b30000;
        color: #fff;
    }

    .reports-table thead th {
        font-size: .74rem;
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
    .count-pill {
        display: inline-flex;
        align-items: center;
        padding: .25rem .6rem;
        border-radius: 999px;
        font-size: .74rem;
        font-weight: 700;
        background: #eef0f2;
        color: #495057;
    }
    .count-pill.pill-danger { background: rgba(220,53,69,0.10); color: #dc3545; }
</style>

<!-- Filtros -->
<div class="member-form-card filter-card mb-4">
    <div class="p-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label">Data Início</label>
                <input type="date" class="form-control" name="start_date" value="<?= $start_date ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">Data Fim</label>
                <input type="date" class="form-control" name="end_date" value="<?= $end_date ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary rounded-pill fw-semibold w-100">
                    <i class="fas fa-filter me-1"></i> Filtrar
                </button>
            </div>
            <div class="col-md-3">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary rounded-pill fw-semibold w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Períodos Rápidos
                    </button>
                    <ul class="dropdown-menu w-100">
                        <li><a class="dropdown-item" href="?date=<?= date('Y-m-d') ?>">Hoje</a></li>
                        <li><a class="dropdown-item" href="?start_date=<?= date('Y-m-01') ?>&end_date=<?= date('Y-m-t') ?>">Este Mês</a></li>
                        <li><a class="dropdown-item" href="?start_date=<?= date('Y-01-01') ?>&end_date=<?= date('Y-12-31') ?>">Este Ano</a></li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resumo do Período -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-tile kpi-primary">
            <div class="kpi-icon"><i class="fas fa-users"></i></div>
            <div>
                <div class="kpi-value"><?= $period_stats['total_attendance'] ?: 0 ?></div>
                <div class="kpi-label">Total Presenças</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-tile kpi-success">
            <div class="kpi-icon"><i class="fas fa-hand-holding-usd"></i></div>
            <div>
                <div class="kpi-value">R$ <?= number_format($period_stats['total_offerings'] ?: 0, 2, ',', '.') ?></div>
                <div class="kpi-label">Ofertas</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-tile kpi-info">
            <div class="kpi-icon"><i class="fas fa-user-friends"></i></div>
            <div>
                <div class="kpi-value"><?= $period_stats['total_visitors'] ?: 0 ?></div>
                <div class="kpi-label">Visitantes</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-tile kpi-warning">
            <div class="kpi-icon"><i class="fas fa-book"></i></div>
            <div>
                <div class="kpi-value"><?= $period_stats['total_bibles'] ?: 0 ?> / <?= $period_stats['total_magazines'] ?: 0 ?></div>
                <div class="kpi-label">Bíblias / Revistas</div>
            </div>
        </div>
    </div>
</div>

<!-- Abas de Detalhamento -->
<ul class="nav nav-tabs mb-3" id="ebdReportTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" id="daily-tab" data-bs-toggle="tab" data-bs-target="#daily" type="button">Por Dia (Aulas)</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="classes-tab" data-bs-toggle="tab" data-bs-target="#classes" type="button">Por Classe</button>
    </li>
</ul>

<div class="tab-content" id="ebdReportTabsContent">
    <!-- Aba Diária -->
    <div class="tab-pane fade show active" id="daily" role="tabpanel">
        <div class="member-form-card">
            <div class="table-responsive">
                <table class="table table-hover reports-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th class="text-center">Turmas</th>
                            <th class="text-center">Presenças</th>
                            <th class="text-center">Visitantes</th>
                            <th class="text-center">Bíblias</th>
                            <th class="text-center">Revistas</th>
                            <th class="text-end">Oferta Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daily_stats as $day): ?>
                        <tr>
                            <td class="fw-bold"><?= date('d/m/Y', strtotime($day['lesson_date'])) ?></td>
                            <td class="text-center"><?= $day['classes_count'] ?></td>
                            <td class="text-center">
                                <span class="count-pill"><?= $day['total_attendance'] ?></span>
                            </td>
                            <td class="text-center"><?= $day['total_visitors'] ?></td>
                            <td class="text-center"><?= $day['total_bibles'] ?></td>
                            <td class="text-center"><?= $day['total_magazines'] ?></td>
                            <td class="text-end fw-bold text-success">
                                R$ <?= number_format($day['total_offerings'] ?: 0, 2, ',', '.') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($daily_stats)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Nenhuma aula registrada neste período.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Aba Por Classe -->
    <div class="tab-pane fade" id="classes" role="tabpanel">
        <div class="member-form-card">
            <div class="table-responsive">
                <table class="table table-hover reports-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Classe</th>
                            <th class="text-center">Aulas</th>
                            <th class="text-center">Matriculados</th>
                            <th class="text-center">Presenças (Total)</th>
                            <th class="text-center">Faltas (Total)</th>
                            <th class="text-center">Visitantes</th>
                            <th class="text-end">Ofertas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes_stats as $cls): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($cls['name']) ?></td>
                            <td class="text-center"><?= $cls['lessons_given'] ?></td>
                            <td class="text-center"><?= $cls['current_students'] ?></td>
                            <td class="text-center"><?= $cls['total_presence'] ?></td>
                            <td class="text-center">
                                <span class="count-pill pill-danger"><?= $cls['total_absences'] ?></span>
                            </td>
                            <td class="text-center"><?= $cls['total_visitors'] ?></td>
                            <td class="text-end fw-bold text-success">
                                R$ <?= number_format($cls['total_offerings'] ?: 0, 2, ',', '.') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($classes_stats)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Nenhuma classe encontrada.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
