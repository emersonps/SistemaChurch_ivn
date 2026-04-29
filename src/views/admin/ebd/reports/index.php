<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Relatórios da EBD</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/ebd/classes" class="btn btn-sm btn-outline-secondary me-2">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4 shadow-sm">
    <div class="card-body">
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
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i> Filtrar
                </button>
            </div>
            <div class="col-md-3">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown">
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
<div class="ebd-summary-cards-carousel d-lg-none mb-2">
    <div class="px-2 pt-2">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted small">Resumo</span>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-dark" id="ebd-summary-counter">1/4</span>
                <span class="text-muted small"><i class="fas fa-arrows-left-right me-1"></i>Deslize para o lado</span>
            </div>
        </div>
    </div>
    <div class="ebd-summary-cards-track" id="ebdSummaryCardsTrack">
        <div class="ebd-summary-cards-slide">
            <div class="card text-white bg-primary shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Presenças</h6>
                            <h2 class="mt-2 mb-0"><?= $period_stats['total_attendance'] ?: 0 ?></h2>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                    <small>Alunos Presentes</small>
                </div>
            </div>
        </div>
        <div class="ebd-summary-cards-slide">
            <div class="card text-white bg-success shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Ofertas</h6>
                            <h2 class="mt-2 mb-0">R$ <?= number_format($period_stats['total_offerings'] ?: 0, 2, ',', '.') ?></h2>
                        </div>
                        <i class="fas fa-hand-holding-usd fa-2x opacity-50"></i>
                    </div>
                    <small>Total Arrecadado</small>
                </div>
            </div>
        </div>
        <div class="ebd-summary-cards-slide">
            <div class="card text-white bg-info shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Visitantes</h6>
                            <h2 class="mt-2 mb-0"><?= $period_stats['total_visitors'] ?: 0 ?></h2>
                        </div>
                        <i class="fas fa-user-friends fa-2x opacity-50"></i>
                    </div>
                    <small>Total Visitantes</small>
                </div>
            </div>
        </div>
        <div class="ebd-summary-cards-slide">
            <div class="card text-white bg-warning shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Bíblias / Revistas</h6>
                            <h4 class="mt-2 mb-0">
                                <i class="fas fa-book me-1"></i> <?= $period_stats['total_bibles'] ?: 0 ?>
                                <span class="mx-1">|</span>
                                <i class="fas fa-book-open me-1"></i> <?= $period_stats['total_magazines'] ?: 0 ?>
                            </h4>
                        </div>
                    </div>
                    <small>Material Trazido</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="ebd-summary-cards-grid row mb-4 d-none d-lg-flex">
    <div class="col-md-3">
        <div class="card text-white bg-primary shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Presenças</h6>
                        <h2 class="mt-2 mb-0"><?= $period_stats['total_attendance'] ?: 0 ?></h2>
                    </div>
                    <i class="fas fa-users fa-2x opacity-50"></i>
                </div>
                <small>Alunos Presentes</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Ofertas</h6>
                        <h2 class="mt-2 mb-0">R$ <?= number_format($period_stats['total_offerings'] ?: 0, 2, ',', '.') ?></h2>
                    </div>
                    <i class="fas fa-hand-holding-usd fa-2x opacity-50"></i>
                </div>
                <small>Total Arrecadado</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Visitantes</h6>
                        <h2 class="mt-2 mb-0"><?= $period_stats['total_visitors'] ?: 0 ?></h2>
                    </div>
                    <i class="fas fa-user-friends fa-2x opacity-50"></i>
                </div>
                <small>Total Visitantes</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Bíblias / Revistas</h6>
                        <h4 class="mt-2 mb-0">
                            <i class="fas fa-book me-1"></i> <?= $period_stats['total_bibles'] ?: 0 ?>
                            <span class="mx-1">|</span>
                            <i class="fas fa-book-open me-1"></i> <?= $period_stats['total_magazines'] ?: 0 ?>
                        </h4>
                    </div>
                </div>
                <small>Material Trazido</small>
            </div>
        </div>
    </div>
</div>

<!-- Abas de Detalhamento -->
<?php $tabTotal = 2; ?>
<style>
    @media (max-width: 991.98px) {
        .ebd-summary-cards-carousel {
            position: relative;
        }
        .ebd-summary-cards-carousel::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, #0d6efd 0%, #198754 40%, #0dcaf0 70%, #ffc107 100%);
            z-index: 2;
        }
        .ebd-summary-cards-track {
            display: flex;
            gap: 0;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            scrollbar-width: none;
            padding: .25rem .25rem .35rem;
        }
        .ebd-summary-cards-track::-webkit-scrollbar { display: none; }
        .ebd-summary-cards-slide {
            flex: 0 0 100%;
            min-width: 100%;
            scroll-snap-align: center;
            padding: .35rem;
        }
        .ebd-summary-cards-slide .card {
            border-radius: 16px;
        }
        .ebd-summary-cards-grid {
            display: none;
        }

        .ebd-report-tabs-carousel {
            position: relative;
        }
        .ebd-report-tabs-carousel.multi::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, #0d6efd 0%, #0dcaf0 55%, #d4af37 100%);
            z-index: 2;
        }
        .ebd-report-tabs-carousel.multi #reportTabsContent {
            display: flex;
            gap: 0;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            scrollbar-width: none;
            padding: .25rem .25rem .35rem;
        }
        .ebd-report-tabs-carousel.multi #reportTabsContent::-webkit-scrollbar { display: none; }
        .ebd-report-tabs-carousel.multi #reportTabsContent > .tab-pane {
            display: block !important;
            flex: 0 0 100%;
            min-width: 100%;
            scroll-snap-align: center;
            opacity: 1 !important;
            padding: .35rem;
        }
        .ebd-report-tabs-carousel.multi #reportTabsContent > .tab-pane.fade { transition: none; }
        .ebd-report-pane-head {
            border-radius: 16px 16px 0 0;
            border: 1px solid rgba(0,0,0,0.08);
            border-bottom: 0;
            background: linear-gradient(135deg, rgba(13,110,253,0.10), rgba(13,202,240,0.12));
            padding: .9rem 1rem;
        }
        .ebd-report-pane-title {
            font-weight: 900;
            font-size: 1.05rem;
            letter-spacing: .01em;
            color: #0d2b3a;
        }
        .ebd-report-pane-hint {
            font-size: .72rem;
            letter-spacing: .08em;
            font-weight: 800;
            color: rgba(0,0,0,0.52);
            text-transform: uppercase;
        }
        .ebd-report-pane-hint i {
            color: #0dcaf0;
        }
        .ebd-report-pane-body {
            border-radius: 0 0 16px 16px;
            border: 1px solid rgba(0,0,0,0.08);
            overflow: hidden;
            background: #fff;
        }
    }
</style>

<ul class="nav nav-tabs mb-3 d-none d-lg-flex" id="reportTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" id="daily-tab" data-bs-toggle="tab" data-bs-target="#daily" type="button">Por Dia (Aulas)</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="classes-tab" data-bs-toggle="tab" data-bs-target="#classes" type="button">Por Classe</button>
    </li>
</ul>

<div class="ebd-report-tabs-carousel multi">
<div class="tab-content" id="reportTabsContent">
    <!-- Aba Diária -->
    <div class="tab-pane fade show active" id="daily" role="tabpanel">
        <div class="d-lg-none ebd-report-pane-head">
            <div class="d-flex justify-content-between align-items-start">
                <div class="me-3">
                    <div class="ebd-report-pane-title"><i class="fas fa-calendar-day me-2"></i>Por Dia (Aulas)</div>
                    <div class="ebd-report-pane-hint mt-1"><i class="fas fa-arrows-left-right me-2"></i>Deslize para mudar (1/<?= $tabTotal ?>)</div>
                </div>
                <span class="badge bg-dark">1/<?= $tabTotal ?></span>
            </div>
        </div>
        <div class="ebd-report-pane-body">
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
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
                            <td><strong><?= date('d/m/Y', strtotime($day['lesson_date'])) ?></strong></td>
                            <td class="text-center"><?= $day['classes_count'] ?></td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?= $day['total_attendance'] ?></span>
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
    </div>

    <!-- Aba Por Classe -->
    <div class="tab-pane fade" id="classes" role="tabpanel">
        <div class="d-lg-none ebd-report-pane-head">
            <div class="d-flex justify-content-between align-items-start">
                <div class="me-3">
                    <div class="ebd-report-pane-title"><i class="fas fa-chalkboard-teacher me-2"></i>Por Classe</div>
                    <div class="ebd-report-pane-hint mt-1"><i class="fas fa-arrows-left-right me-2"></i>Deslize para mudar (2/<?= $tabTotal ?>)</div>
                </div>
                <span class="badge bg-dark">2/<?= $tabTotal ?></span>
            </div>
        </div>
        <div class="ebd-report-pane-body">
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
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
                            <td><strong><?= htmlspecialchars($cls['name']) ?></strong></td>
                            <td class="text-center"><?= $cls['lessons_given'] ?></td>
                            <td class="text-center"><?= $cls['current_students'] ?></td>
                            <td class="text-center"><?= $cls['total_presence'] ?></td>
                            <td class="text-center">
                                <span class="badge bg-danger"><?= $cls['total_absences'] ?></span>
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
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var track = document.getElementById('ebdSummaryCardsTrack');
    var counter = document.getElementById('ebd-summary-counter');
    if (!track || !counter) return;

    var slides = track.querySelectorAll('.ebd-summary-cards-slide');
    var total = slides.length || 1;
    counter.textContent = '1/' + total;

    var scheduled = false;
    function update() {
        scheduled = false;
        var width = track.clientWidth || 1;
        var idx = Math.round(track.scrollLeft / width) + 1;
        if (idx < 1) idx = 1;
        if (idx > total) idx = total;
        counter.textContent = idx + '/' + total;
    }

    function scheduleUpdate() {
        if (scheduled) return;
        scheduled = true;
        window.requestAnimationFrame(update);
    }

    track.addEventListener('scroll', scheduleUpdate, { passive: true });
    window.addEventListener('resize', scheduleUpdate);
    scheduleUpdate();
});
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
