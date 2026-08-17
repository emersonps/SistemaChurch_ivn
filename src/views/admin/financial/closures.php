<?php $suppressMobileTopbar = true; include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-none d-lg-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Fechamentos Financeiros</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-success rounded-pill fw-semibold px-3" data-bs-toggle="modal" data-bs-target="#newClosureModal">
            <i class="fas fa-lock me-1"></i> Novo Fechamento
        </button>
    </div>
</div>

<style>
    .closure-pane-card {
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,0.08);
        overflow: hidden;
        background: #fff;
    }
    .type-pill {
        display: inline-block;
        padding: .2rem .6rem;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 700;
    }
    .type-pill.type-mensal { background: rgba(13,202,240,0.14); color: #087990; }
    .type-pill.type-anual { background: rgba(13,110,253,0.10); color: #0d6efd; }
    .icon-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        padding: 0;
    }
    .modal-content { border-radius: 16px; border: none; overflow: hidden; }
    .modal-header { background: #fafafa; }
    #newClosureModal .form-control, #newClosureModal .form-select {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
    }
    #newClosureModal .form-control:focus, #newClosureModal .form-select:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
</style>

<?php
// Agrupar fechamentos por congregação
$groupedClosures = [];
foreach ($closures as $fc) {
    $congregationName = $fc['congregation_name'] ?? 'Sem Congregação';
    if (!isset($groupedClosures[$congregationName])) {
        $groupedClosures[$congregationName] = [];
    }
    $groupedClosures[$congregationName][] = $fc;
}
ksort($groupedClosures);
$tabTotal = count($groupedClosures);
$hasMultipleCongregations = $tabTotal > 1;
?>

<?php include __DIR__ . '/_mobile_list.php'; ?>

<style>
    #closureTabs.nav-tabs {
        border-bottom: none;
        gap: .4rem;
    }
    #closureTabs.nav-tabs .nav-link {
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 999px;
        padding: .45rem 1rem;
        font-weight: 700;
        font-size: .85rem;
        color: #495057;
        background: #fff;
    }
    #closureTabs.nav-tabs .nav-link:hover {
        border-color: rgba(179,0,0,0.3);
        color: #b30000;
        isolation: isolate;
    }
    #closureTabs.nav-tabs .nav-link.active {
        background: #b30000;
        border-color: #b30000;
        color: #fff;
    }
    #closureTabs.nav-tabs .nav-link .badge {
        font-weight: 700;
        background: #eef0f2;
        color: #495057;
    }
    #closureTabs.nav-tabs .nav-link.active .badge {
        background: rgba(255,255,255,0.25);
        color: #fff;
    }
    @media (max-width: 991.98px) {
        .closure-tabs-carousel {
            position: relative;
        }
        .closure-tabs-carousel.multi::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, #198754 0%, #0d6efd 55%, #d4af37 100%);
            z-index: 2;
        }
        .closure-tabs-carousel.multi #closureTabsContent {
            display: flex;
            gap: 0;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            scrollbar-width: none;
            padding: .25rem .25rem .35rem;
        }
        .closure-tabs-carousel.multi #closureTabsContent::-webkit-scrollbar { display: none; }
        .closure-tabs-carousel.multi #closureTabsContent > .tab-pane {
            display: block !important;
            flex: 0 0 100%;
            min-width: 100%;
            scroll-snap-align: center;
            opacity: 1 !important;
            padding: .35rem;
        }
        .closure-tabs-carousel.multi #closureTabsContent > .tab-pane.fade { transition: none; }
        .closure-pane-head {
            background: linear-gradient(135deg, rgba(25,135,84,0.14), rgba(13,110,253,0.10));
        }
        .closure-pane-title {
            font-weight: 900;
            font-size: 1.05rem;
            letter-spacing: .01em;
            color: #0b2a1b;
        }
        .closure-pane-hint {
            font-size: .72rem;
            letter-spacing: .08em;
            font-weight: 800;
            color: rgba(0,0,0,0.52);
            text-transform: uppercase;
        }
        .closure-pane-hint i {
            color: #198754;
        }
    }
</style>

<ul class="nav nav-tabs mb-3 d-none d-lg-flex" id="closureTabs" role="tablist">
    <?php $first = true; foreach ($groupedClosures as $congregationName => $items): 
        $tabId = 'tab-' . md5($congregationName);
    ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $first ? 'active' : '' ?>" id="<?= $tabId ?>-tab" data-bs-toggle="tab" data-bs-target="#<?= $tabId ?>" type="button" role="tab" aria-controls="<?= $tabId ?>" aria-selected="<?= $first ? 'true' : 'false' ?>">
                <?= htmlspecialchars($congregationName) ?> 
                <span class="badge bg-secondary ms-1"><?= count($items) ?></span>
            </button>
        </li>
    <?php $first = false; endforeach; ?>
</ul>

<div class="closure-tabs-carousel d-none d-lg-block <?= $hasMultipleCongregations ? 'multi' : '' ?>">
<div class="tab-content" id="closureTabsContent">
    <?php $first = true; $tabStep = 1; foreach ($groupedClosures as $congregationName => $items): 
        $tabId = 'tab-' . md5($congregationName);
    ?>
        <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="<?= $tabId ?>" role="tabpanel" aria-labelledby="<?= $tabId ?>-tab">
            <div class="closure-pane-card">
                <div class="d-lg-none px-3 py-3 border-bottom closure-pane-head">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="me-3">
                            <div class="closure-pane-title">
                                <i class="fas fa-church me-2"></i><?= htmlspecialchars($congregationName) ?>
                            </div>
                            <?php if ($hasMultipleCongregations): ?>
                                <div class="closure-pane-hint mt-1">
                                    <i class="fas fa-arrows-left-right me-2"></i>Deslize para mudar (<?= $tabStep ?>/<?= $tabTotal ?>)
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($hasMultipleCongregations): ?>
                            <span class="badge bg-dark"><?= $tabStep ?>/<?= $tabTotal ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Período</th>
                            <th>Tipo</th>
                            <th>Entradas</th>
                            <th>Saídas</th>
                            <th>Saldo Período</th>
                            <th>Saldo Final</th>
                            <th>Gerado em</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $fc): ?>
                            <tr>
                                <td class="align-middle fw-bold"><?= htmlspecialchars($fc['period']) ?></td>
                                <td class="align-middle"><span class="type-pill type-<?= $fc['type'] == 'Mensal' ? 'mensal' : 'anual' ?>"><?= $fc['type'] ?></span></td>
                                <td class="align-middle text-success fw-bold">R$ <?= number_format($fc['total_entries'], 2, ',', '.') ?></td>
                                <td class="align-middle text-danger fw-bold">R$ <?= number_format($fc['total_expenses'], 2, ',', '.') ?></td>
                                <td class="align-middle fw-bold <?= $fc['balance'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    R$ <?= number_format($fc['balance'], 2, ',', '.') ?>
                                </td>
                                <td class="align-middle fw-bold bg-light text-dark">R$ <?= number_format($fc['final_balance'], 2, ',', '.') ?></td>
                                <td class="align-middle text-muted small"><?= date('d/m/Y H:i', strtotime($fc['created_at'])) ?></td>
                                <td class="align-middle text-end">
                                    <a href="/admin/financial/closures/show/<?= $fc['id'] ?>" class="btn btn-sm btn-outline-primary icon-btn" title="Ver Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/admin/financial/closures/delete/<?= $fc['id'] ?>" class="btn btn-sm btn-outline-danger icon-btn btn-delete-closure" data-period="<?= htmlspecialchars($fc['period']) ?>" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    <?php $first = false; $tabStep++; endforeach; ?>
</div>
</div>

<?php if (empty($groupedClosures)): ?>
    <div class="alert alert-info text-center d-none d-lg-block">
        <i class="fas fa-info-circle me-2"></i> Nenhum fechamento financeiro registrado.
    </div>
<?php endif; ?>

<!-- Modal Novo Fechamento -->
<div class="modal fade" id="newClosureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="/admin/financial/closures/store" method="POST">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-lock me-2"></i>Novo Fechamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Congregação <span class="text-danger">*</span></label>
                        <select name="congregation_id" class="form-select" required>
                            <?php foreach ($congregations as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" id="closureType" onchange="togglePeriodInput()" required>
                            <option value="Mensal">Mensal</option>
                            <option value="Anual">Anual</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Período <span class="text-danger">*</span></label>
                        <input type="month" name="period" id="periodMonthly" class="form-control" required>
                        <select name="period" id="periodAnnual" class="form-select d-none" disabled>
                            <?php for($i = date('Y'); $i >= 2020; $i--): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="alert alert-warning mb-0">
                        <small><i class="fas fa-exclamation-triangle me-1"></i> O fechamento consolidará todas as entradas e saídas do período selecionado e calculará o saldo final acumulado.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill fw-semibold px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-pill fw-semibold px-3">Gerar Fechamento</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function togglePeriodInput() {
    const type = document.getElementById('closureType').value;
    const monthly = document.getElementById('periodMonthly');
    const annual = document.getElementById('periodAnnual');
    
    if (type === 'Mensal') {
        monthly.classList.remove('d-none');
        monthly.disabled = false;
        annual.classList.add('d-none');
        annual.disabled = true;
    } else {
        monthly.classList.add('d-none');
        monthly.disabled = true;
        annual.classList.remove('d-none');
        annual.disabled = false;
    }
}

document.querySelectorAll('.btn-delete-closure').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const href = btn.getAttribute('href');
        const period = btn.getAttribute('data-period');
        Swal.fire({
            title: 'Excluir fechamento?',
            text: `Tem certeza que deseja excluir o fechamento de "${period}"? Isso reabrirá o período.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.replace(href);
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
