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
            <div class="col-md-3">
                <label class="form-label">Data Início</label>
                <input type="date" class="form-control" name="start_date" value="<?= $start_date ?>">
            </div>
            <div class="col-md-3">
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
<div class="row mb-4">
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
<ul class="nav nav-tabs mb-3" id="reportTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" id="daily-tab" data-bs-toggle="tab" data-bs-target="#daily" type="button">Por Dia (Aulas)</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="classes-tab" data-bs-toggle="tab" data-bs-target="#classes" type="button">Por Classe</button>
    </li>
</ul>

<div class="tab-content" id="reportTabsContent">
    <!-- Aba Diária -->
    <div class="tab-pane fade show active" id="daily" role="tabpanel">
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

    <!-- Aba Por Classe -->
    <div class="tab-pane fade" id="classes" role="tabpanel">
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

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
