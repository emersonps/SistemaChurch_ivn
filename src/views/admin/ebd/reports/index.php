<?php $suppressMobileTopbar = true; include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-none d-lg-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
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

<?php
$rpmMobileMenuItems = [
    ['icon' => 'fa-print', 'label' => 'Visualizar / Imprimir', 'href' => '/admin/ebd/reports/print?start_date=' . urlencode($start_date) . '&end_date=' . urlencode($end_date), 'target' => '_blank'],
];
?>
<div class="d-lg-none">
    <?php
    $mobilePageCategory = 'Ensino';
    $mobilePageTitle = 'Relatórios';
    $mobilePageMenuItems = $rpmMobileMenuItems;
    include __DIR__ . '/../../../layout/mobile_page_header.php';
    ?>
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

    /* ---------- Mobile (Relatórios) — EBD Mobile design tokens ---------- */
    .rpm-wrap { padding-bottom: 40px; }
    .rpm-pill { display: flex; align-items: center; justify-content: center; gap: .4rem; width: 100%; background: #fff; border: 1px solid #eef1f5; color: #c2790a; font-weight: 700; font-size: .8rem; padding: .65rem .9rem; border-radius: 12px; text-align: center; margin-bottom: 1rem; }
    .rpm-pill .rpm-pill-sep { color: #c2c8d2; font-weight: 400; }
    .rpm-pill .rpm-pill-link { color: #3b6fef; font-weight: 600; }
    .rpm-stat-grid-top { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 1rem; }
    .rpm-stat-tile { background: #fff; border: 1px solid #eef1f5; border-radius: 14px; padding: 14px; }
    .rpm-stat-tile-label { font-size: 12px; font-weight: 600; margin-bottom: 6px; }
    .rpm-stat-tile-value { color: #101828; font-size: 20px; font-weight: 800; }
    .rpm-stat-tile.is-amber .rpm-stat-tile-label { color: #c2790a; }
    .rpm-stat-tile.is-green .rpm-stat-tile-label { color: #18a558; }
    .rpm-stat-tile.is-blue .rpm-stat-tile-label { color: #3b6fef; }
    .rpm-stat-tile.is-purple .rpm-stat-tile-label { color: #7c4fd1; }
    .rpm-segmented { display: flex; background: #e7e9ee; border-radius: 12px; padding: 4px; margin-bottom: 1rem; }
    .rpm-seg-btn { flex: 1 1 0; border: none; background: transparent; color: #8b93a3; font-weight: 600; font-size: .82rem; padding: 9px 0; border-radius: 9px; }
    .rpm-seg-btn.active { background: #fff; color: #101828; font-weight: 700; box-shadow: 0 2px 6px rgba(0,0,0,.06); }
    .rpm-panel.d-none { display: none !important; }
    .rpm-empty { padding: 2.4rem 0; text-align: center; color: #adb5bd; font-size: .84rem; }
    .rpm-empty i { font-size: 2.1rem; margin-bottom: .6rem; display: block; color: #ced4da; }

    .rpm-day-card, .rpm-cls-card { background: #fff; border: 1px solid #eef1f5; border-radius: 14px; padding: .8rem .9rem; margin-bottom: .55rem; }
    .rpm-day-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: .55rem; }
    .rpm-day-date, .rpm-cls-name { font-weight: 800; font-size: .86rem; color: #101828; }
    .rpm-day-classes { font-size: .68rem; font-weight: 700; color: #8b93a3; background: #f1f2f5; padding: .18rem .55rem; border-radius: 999px; }
    .rpm-stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: .5rem; margin-bottom: .55rem; }
    .rpm-cls-card .rpm-stat-grid { grid-template-columns: repeat(3, 1fr); }
    .rpm-stat-label { font-size: .58rem; font-weight: 700; text-transform: uppercase; color: #adb5bd; }
    .rpm-stat-value { font-size: .8rem; font-weight: 800; color: #101828; }
    .rpm-offering { text-align: right; font-size: .8rem; font-weight: 800; color: #18a558; }
    .rpm-absences { color: #dc3545; }

    .rpm-sheet.offcanvas-bottom { border-top-left-radius: 20px; border-top-right-radius: 20px; height: auto; max-height: 92vh; }
    .rpm-chip-row { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .6rem; margin-bottom: 1rem; }
    .rpm-chip { border: 1px solid #e3e7ee; background: #fff; color: #101828; font-size: .78rem; font-weight: 700; padding: .45rem .9rem; border-radius: 999px; text-decoration: none; }
</style>

<div class="d-lg-none rpm-wrap">
    <button type="button" class="rpm-pill" data-bs-toggle="offcanvas" data-bs-target="#rpmFilterSheet">
        <i class="far fa-calendar"></i> <?= date('d/m', strtotime($start_date)) ?> - <?= date('d/m', strtotime($end_date)) ?>
        <span class="rpm-pill-sep">•</span>
        <span class="rpm-pill-link">Períodos Rápidos</span>
    </button>

    <div class="rpm-stat-grid-top">
        <div class="rpm-stat-tile is-amber">
            <div class="rpm-stat-tile-label">Total Presenças</div>
            <div class="rpm-stat-tile-value"><?= $period_stats['total_attendance'] ?: 0 ?></div>
        </div>
        <div class="rpm-stat-tile is-green">
            <div class="rpm-stat-tile-label">Ofertas</div>
            <div class="rpm-stat-tile-value">R$ <?= number_format($period_stats['total_offerings'] ?: 0, 2, ',', '.') ?></div>
        </div>
        <div class="rpm-stat-tile is-blue">
            <div class="rpm-stat-tile-label">Visitantes</div>
            <div class="rpm-stat-tile-value"><?= $period_stats['total_visitors'] ?: 0 ?></div>
        </div>
        <div class="rpm-stat-tile is-purple">
            <div class="rpm-stat-tile-label">Bíblias/Revistas</div>
            <div class="rpm-stat-tile-value"><?= $period_stats['total_bibles'] ?: 0 ?>/<?= $period_stats['total_magazines'] ?: 0 ?></div>
        </div>
    </div>

    <div class="rpm-segmented" id="rpmSegmented">
        <button type="button" class="rpm-seg-btn active" data-panel="rpmDaily">Por Dia</button>
        <button type="button" class="rpm-seg-btn" data-panel="rpmClasses">Por Classe</button>
    </div>

    <div class="rpm-panel" id="rpmDaily">
        <?php if (empty($daily_stats)): ?>
            <div class="rpm-empty"><i class="fas fa-calendar-day"></i>Nenhuma aula registrada neste período.</div>
        <?php else: ?>
            <?php foreach ($daily_stats as $day): ?>
                <div class="rpm-day-card">
                    <div class="rpm-day-top">
                        <span class="rpm-day-date"><?= date('d/m/Y', strtotime($day['lesson_date'])) ?></span>
                        <span class="rpm-day-classes"><?= $day['classes_count'] ?> turma<?= $day['classes_count'] == 1 ? '' : 's' ?></span>
                    </div>
                    <div class="rpm-stat-grid">
                        <div><div class="rpm-stat-label">Presenças</div><div class="rpm-stat-value"><?= $day['total_attendance'] ?: 0 ?></div></div>
                        <div><div class="rpm-stat-label">Visitantes</div><div class="rpm-stat-value"><?= $day['total_visitors'] ?: 0 ?></div></div>
                        <div><div class="rpm-stat-label">Bíblias</div><div class="rpm-stat-value"><?= $day['total_bibles'] ?: 0 ?></div></div>
                        <div><div class="rpm-stat-label">Revistas</div><div class="rpm-stat-value"><?= $day['total_magazines'] ?: 0 ?></div></div>
                    </div>
                    <div class="rpm-offering">R$ <?= number_format($day['total_offerings'] ?: 0, 2, ',', '.') ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="rpm-panel d-none" id="rpmClasses">
        <?php if (empty($classes_stats)): ?>
            <div class="rpm-empty"><i class="fas fa-calendar-day"></i>Nenhuma classe encontrada.</div>
        <?php else: ?>
            <?php foreach ($classes_stats as $cls): ?>
                <div class="rpm-cls-card">
                    <div class="rpm-day-top">
                        <span class="rpm-cls-name"><?= htmlspecialchars($cls['name']) ?></span>
                        <span class="rpm-day-classes"><?= $cls['lessons_given'] ?> aula<?= $cls['lessons_given'] == 1 ? '' : 's' ?></span>
                    </div>
                    <div class="rpm-stat-grid">
                        <div><div class="rpm-stat-label">Matriculados</div><div class="rpm-stat-value"><?= $cls['current_students'] ?></div></div>
                        <div><div class="rpm-stat-label">Presenças</div><div class="rpm-stat-value"><?= $cls['total_presence'] ?></div></div>
                        <div><div class="rpm-stat-label">Faltas</div><div class="rpm-stat-value rpm-absences"><?= $cls['total_absences'] ?></div></div>
                    </div>
                    <div class="rpm-day-top mb-0">
                        <span class="rpm-stat-label">Visitantes: <b class="rpm-stat-value"><?= $cls['total_visitors'] ?: 0 ?></b></span>
                        <span class="rpm-offering">R$ <?= number_format($cls['total_offerings'] ?: 0, 2, ',', '.') ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php
    $mobilePageFooterLabel = 'Relatórios EBD';
    include __DIR__ . '/../../../layout/mobile_page_footer.php';
    ?>
</div>

<div class="offcanvas offcanvas-bottom rpm-sheet" tabindex="-1" id="rpmFilterSheet">
    <div class="offcanvas-header">
        <h6 class="offcanvas-title fw-bold">Período</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body">
        <div class="small fw-semibold mb-1">Períodos rápidos</div>
        <div class="rpm-chip-row">
            <a href="?date=<?= date('Y-m-d') ?>" class="rpm-chip">Hoje</a>
            <a href="?start_date=<?= date('Y-m-01') ?>&end_date=<?= date('Y-m-t') ?>" class="rpm-chip">Este Mês</a>
            <a href="?start_date=<?= date('Y-01-01') ?>&end_date=<?= date('Y-12-31') ?>" class="rpm-chip">Este Ano</a>
        </div>
        <form method="GET">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Data Início</label>
                <input type="date" class="form-control" name="start_date" value="<?= $start_date ?>">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Data Fim</label>
                <input type="date" class="form-control" name="end_date" value="<?= $end_date ?>">
            </div>
            <button type="submit" class="btn btn-dark w-100 rounded-pill">Aplicar</button>
        </form>
    </div>
</div>

<script>
(function () {
    var segmented = document.getElementById('rpmSegmented');
    if (!segmented) return;
    segmented.querySelectorAll('.rpm-seg-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            segmented.querySelectorAll('.rpm-seg-btn').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            document.querySelectorAll('.rpm-panel').forEach(function (p) { p.classList.add('d-none'); });
            document.getElementById(btn.getAttribute('data-panel')).classList.remove('d-none');
        });
    });
})();
</script>

<!-- Filtros -->
<div class="member-form-card filter-card mb-4 d-none d-lg-block">
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
<div class="row g-3 mb-4 d-none d-lg-flex">
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
<ul class="nav nav-tabs mb-3 d-none d-lg-flex" id="ebdReportTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" id="daily-tab" data-bs-toggle="tab" data-bs-target="#daily" type="button">Por Dia (Aulas)</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="classes-tab" data-bs-toggle="tab" data-bs-target="#classes" type="button">Por Classe</button>
    </li>
</ul>

<div class="tab-content d-none d-lg-block" id="ebdReportTabsContent">
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
