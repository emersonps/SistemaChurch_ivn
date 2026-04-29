<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Relatório Geral de Estatísticas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Imprimir
        </button>
    </div>
</div>

<form method="GET" class="row g-3 mb-4 border p-3 rounded shadow-sm bg-light no-print">
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
        <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-filter me-2"></i> Filtrar
        </button>
    </div>
</form>

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
?>

<div class="stats-cards-carousel d-lg-none mb-3 no-print">
    <div class="px-2 pt-2">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted small">Estatísticas</span>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-dark" id="stats-cards-counter">1/4</span>
                <span class="text-muted small"><i class="fas fa-arrows-left-right me-1"></i>Deslize para o lado</span>
            </div>
        </div>
    </div>
    <div class="stats-cards-track" id="statsCardsTrack">
        <div class="stats-cards-slide">
            <div class="card text-white bg-primary shadow-sm h-100">
                <div class="card-header">Cultos Realizados</div>
                <div class="card-body">
                    <h2 class="card-title text-center"><?= $attendanceStats['total_services'] ?? 0 ?></h2>
                    <p class="card-text small text-center mb-0">No período selecionado</p>
                </div>
            </div>
        </div>
        <div class="stats-cards-slide">
            <div class="card text-white bg-success shadow-sm h-100">
                <div class="card-header">Visitantes (Únicos)</div>
                <div class="card-body">
                    <h2 class="card-title text-center"><?= $visitorsCount ?></h2>
                    <p class="card-text small text-center mb-0">Cadastrados nos relatórios</p>
                </div>
            </div>
        </div>
        <div class="stats-cards-slide">
            <div class="card text-white bg-warning text-dark shadow-sm h-100">
                <div class="card-header">Decisões / Conversões</div>
                <div class="card-body">
                    <h2 class="card-title text-center"><?= $conversionsCount ?></h2>
                    <p class="card-text small text-center mb-0">Aceitou Jesus + Reconciliados</p>
                </div>
            </div>
        </div>
        <div class="stats-cards-slide">
            <div class="card text-white bg-info text-dark shadow-sm h-100">
                <div class="card-header">Média de Público</div>
                <div class="card-body">
                    <h2 class="card-title text-center"><?= $avgAttendance ?></h2>
                    <p class="card-text small text-center mb-0">Pessoas por culto (aprox.)</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="stats-cards-grid row mb-4">
    <!-- Card: Total Cultos -->
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3 shadow-sm">
            <div class="card-header">Cultos Realizados</div>
            <div class="card-body">
                <h2 class="card-title text-center"><?= $attendanceStats['total_services'] ?? 0 ?></h2>
                <p class="card-text small text-center">No período selecionado</p>
            </div>
        </div>
    </div>
    <!-- Card: Total Visitantes (Pessoas) -->
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3 shadow-sm">
            <div class="card-header">Visitantes (Únicos)</div>
            <div class="card-body">
                <h2 class="card-title text-center"><?= $visitorsCount ?></h2>
                <p class="card-text small text-center">Cadastrados nos relatórios</p>
            </div>
        </div>
    </div>
    <!-- Card: Total Decisões -->
    <div class="col-md-3">
        <div class="card text-white bg-warning text-dark mb-3 shadow-sm">
            <div class="card-header">Decisões / Conversões</div>
            <div class="card-body">
                <h2 class="card-title text-center"><?= $conversionsCount ?></h2>
                <p class="card-text small text-center">Aceitou Jesus + Reconciliados</p>
            </div>
        </div>
    </div>
    <!-- Card: Média Frequência -->
    <div class="col-md-3">
        <div class="card text-white bg-info text-dark mb-3 shadow-sm">
            <div class="card-header">Média de Público</div>
            <div class="card-body">
                <h2 class="card-title text-center"><?= $avgAttendance ?></h2>
                <p class="card-text small text-center">Pessoas por culto (aprox.)</p>
            </div>
        </div>
    </div>
</div>

<div class="stats-panels-carousel d-lg-none mb-3 no-print">
    <div class="px-2 pt-2">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted small">Detalhes</span>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-dark" id="stats-panels-counter">1/3</span>
                <span class="text-muted small"><i class="fas fa-arrows-left-right me-1"></i>Deslize para o lado</span>
            </div>
        </div>
    </div>
    <div class="stats-panels-track" id="statsPanelsTrack">
        <div class="stats-panels-slide">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-secondary"><i class="fas fa-users me-2"></i> Movimentação de Pessoas (Detalhado)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($peopleStats)): ?>
                        <p class="text-center text-muted py-4">Nenhum registro encontrado no período.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
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
                                            <?php
                                            $badgeClass = 'bg-secondary';
                                            switch($stat['action_type']) {
                                                case 'Visitante': $badgeClass = 'bg-success'; break;
                                                case 'Aceitou Jesus':
                                                case 'Conversão': $badgeClass = 'bg-warning text-dark'; break;
                                                case 'Reconciliado':
                                                case 'Reconciliação': $badgeClass = 'bg-info text-dark'; break;
                                                case 'Batismo': $badgeClass = 'bg-primary'; break;
                                                case 'Desligamento': $badgeClass = 'bg-danger'; break;
                                                case 'Disciplinado': $badgeClass = 'bg-dark'; break;
                                            }
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($stat['action_type']) ?></span>
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
        <div class="stats-panels-slide">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-secondary"><i class="fas fa-book-open me-2"></i> Escola Bíblica (Atual)</h5>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Classes Ativas
                        <span class="badge bg-primary rounded-pill"><?= $ebdStats['total_classes'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Alunos Matriculados
                        <span class="badge bg-primary rounded-pill"><?= $ebdStats['total_students'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Professores
                        <span class="badge bg-primary rounded-pill"><?= $ebdStats['total_teachers'] ?></span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="stats-panels-slide">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-secondary"><i class="fas fa-users-cog me-2"></i> Grupos e Células</h5>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Grupos Ativos
                        <span class="badge bg-info text-dark rounded-pill"><?= $groupStats['total_groups'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Total de Participantes
                        <span class="badge bg-info text-dark rounded-pill"><?= $groupStats['total_members'] ?? 0 ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="stats-panels-grid row">
    <!-- Tabela: Detalhamento de Pessoas -->
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-secondary"><i class="fas fa-users me-2"></i> Movimentação de Pessoas (Detalhado)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($peopleStats)): ?>
                    <p class="text-center text-muted py-4">Nenhum registro encontrado no período.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
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
                                        <?php
                                        $badgeClass = 'bg-secondary';
                                        switch($stat['action_type']) {
                                            case 'Visitante': $badgeClass = 'bg-success'; break;
                                            case 'Aceitou Jesus': 
                                            case 'Conversão': $badgeClass = 'bg-warning text-dark'; break;
                                            case 'Reconciliado': 
                                            case 'Reconciliação': $badgeClass = 'bg-info text-dark'; break;
                                            case 'Batismo': $badgeClass = 'bg-primary'; break;
                                            case 'Desligamento': $badgeClass = 'bg-danger'; break;
                                            case 'Disciplinado': $badgeClass = 'bg-dark'; break;
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($stat['action_type']) ?></span>
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
    <div class="col-md-4">
        <!-- EBD Stats -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-secondary"><i class="fas fa-book-open me-2"></i> Escola Bíblica (Atual)</h5>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Classes Ativas
                    <span class="badge bg-primary rounded-pill"><?= $ebdStats['total_classes'] ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Alunos Matriculados
                    <span class="badge bg-primary rounded-pill"><?= $ebdStats['total_students'] ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Professores
                    <span class="badge bg-primary rounded-pill"><?= $ebdStats['total_teachers'] ?></span>
                </li>
            </ul>
        </div>

        <!-- Groups Stats -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-secondary"><i class="fas fa-users-cog me-2"></i> Grupos e Células</h5>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Grupos Ativos
                    <span class="badge bg-info text-dark rounded-pill"><?= $groupStats['total_groups'] ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Total de Participantes
                    <span class="badge bg-info text-dark rounded-pill"><?= $groupStats['total_members'] ?? 0 ?></span>
                </li>
            </ul>
        </div>
    </div>
</div>

<style>
@media (max-width: 991.98px) {
    .stats-cards-carousel {
        position: relative;
    }
    .stats-cards-carousel::before {
        content: '';
        position: absolute;
        inset: 0 0 auto 0;
        height: 4px;
        background: linear-gradient(90deg, #0d6efd 0%, #198754 45%, #ffc107 70%, #0dcaf0 100%);
        z-index: 2;
    }
    .stats-cards-track {
        display: flex;
        gap: 0;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        scrollbar-width: none;
        padding: .25rem .25rem .35rem;
    }
    .stats-cards-track::-webkit-scrollbar { display: none; }
    .stats-cards-slide {
        flex: 0 0 100%;
        min-width: 100%;
        scroll-snap-align: center;
        padding: .35rem;
    }
    .stats-cards-slide .card {
        border-radius: 16px;
    }
    .stats-cards-grid {
        display: none;
    }

    .stats-panels-carousel {
        position: relative;
    }
    .stats-panels-carousel::before {
        content: '';
        position: absolute;
        inset: 0 0 auto 0;
        height: 4px;
        background: linear-gradient(90deg, #0d6efd 0%, #6c757d 55%, #0dcaf0 100%);
        z-index: 2;
    }
    .stats-panels-track {
        display: flex;
        gap: 0;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        scrollbar-width: none;
        padding: .25rem .25rem .35rem;
    }
    .stats-panels-track::-webkit-scrollbar { display: none; }
    .stats-panels-slide {
        flex: 0 0 100%;
        min-width: 100%;
        scroll-snap-align: center;
        padding: .35rem;
    }
    .stats-panels-slide .card {
        border-radius: 16px;
    }
    .stats-panels-grid {
        display: none;
    }
}

@media print {
    .no-print { display: none !important; }
    .stats-cards-carousel { display: none !important; }
    .stats-cards-grid { display: flex !important; }
    .stats-panels-carousel { display: none !important; }
    .stats-panels-grid { display: flex !important; }
    .card { border: 1px solid #ddd !important; box-shadow: none !important; }
    .badge { border: 1px solid #000; color: #000 !important; background: none !important; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var track = document.getElementById('statsCardsTrack');
    var counter = document.getElementById('stats-cards-counter');
    if (!track || !counter) return;

    var slides = track.querySelectorAll('.stats-cards-slide');
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    var track = document.getElementById('statsPanelsTrack');
    var counter = document.getElementById('stats-panels-counter');
    if (!track || !counter) return;

    var slides = track.querySelectorAll('.stats-panels-slide');
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

<?php include __DIR__ . '/../../layout/footer.php'; ?>
