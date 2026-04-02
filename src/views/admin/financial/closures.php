<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Fechamentos Financeiros</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#newClosureModal">
            <i class="fas fa-lock"></i> Novo Fechamento
        </button>
    </div>
</div>

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
?>

<ul class="nav nav-tabs mb-3" id="closureTabs" role="tablist">
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

<div class="tab-content" id="closureTabsContent">
    <?php $first = true; foreach ($groupedClosures as $congregationName => $items): 
        $tabId = 'tab-' . md5($congregationName);
    ?>
        <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="<?= $tabId ?>" role="tabpanel" aria-labelledby="<?= $tabId ?>-tab">
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
                                <td class="align-middle"><span class="badge bg-<?= $fc['type'] == 'Mensal' ? 'info' : 'primary' ?>"><?= $fc['type'] ?></span></td>
                                <td class="align-middle text-success">R$ <?= number_format($fc['total_entries'], 2, ',', '.') ?></td>
                                <td class="align-middle text-danger">R$ <?= number_format($fc['total_expenses'], 2, ',', '.') ?></td>
                                <td class="align-middle fw-bold <?= $fc['balance'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    R$ <?= number_format($fc['balance'], 2, ',', '.') ?>
                                </td>
                                <td class="align-middle fw-bold bg-light text-dark">R$ <?= number_format($fc['final_balance'], 2, ',', '.') ?></td>
                                <td class="align-middle text-muted small"><?= date('d/m/Y H:i', strtotime($fc['created_at'])) ?></td>
                                <td class="align-middle text-end">
                                    <div class="btn-group">
                                        <a href="/admin/financial/closures/show/<?= $fc['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/admin/financial/closures/delete/<?= $fc['id'] ?>" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="return confirm('Tem certeza? Isso reabrirá o período.')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php $first = false; endforeach; ?>
</div>

<?php if (empty($groupedClosures)): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle me-2"></i> Nenhum fechamento financeiro registrado.
    </div>
<?php endif; ?>

<!-- Modal Novo Fechamento -->
<div class="modal fade" id="newClosureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="/admin/financial/closures/store" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Fechamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Congregação</label>
                        <select name="congregation_id" class="form-select" required>
                            <?php foreach ($congregations as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <select name="type" class="form-select" id="closureType" onchange="togglePeriodInput()" required>
                            <option value="Mensal">Mensal</option>
                            <option value="Anual">Anual</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Período</label>
                        <input type="month" name="period" id="periodMonthly" class="form-control" required>
                        <select name="period" id="periodAnnual" class="form-select d-none" disabled>
                            <?php for($i = date('Y'); $i >= 2020; $i--): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="alert alert-warning">
                        <small><i class="fas fa-exclamation-triangle"></i> O fechamento consolidará todas as entradas e saídas do período selecionado e calculará o saldo final acumulado.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Gerar Fechamento</button>
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
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>