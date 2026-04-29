<?php include __DIR__ . '/layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Histórico Financeiro</h1>
</div>

<div class="card mb-4 bg-light">
    <div class="card-body py-2">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-6 col-md-3">
                <label class="form-label small mb-0">Data Início</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="<?= $_GET['start_date'] ?? '' ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-0">Data Fim</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="<?= $_GET['end_date'] ?? '' ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0">Tipo</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="Dízimo" <?= (isset($_GET['type']) && $_GET['type'] == 'Dízimo') ? 'selected' : '' ?>>Dízimo</option>
                    <option value="Oferta" <?= (isset($_GET['type']) && $_GET['type'] == 'Oferta') ? 'selected' : '' ?>>Oferta</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary w-100 mb-1">Filtrar</button>
                <a href="/portal/financial" class="btn btn-sm btn-outline-secondary w-100">Limpar</a>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Data</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Método</th>
                <th>Recibo</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tithes)): ?>
                <tr><td colspan="5" class="text-center text-muted">Nenhum registro encontrado.</td></tr>
            <?php else: ?>
                <?php foreach ($tithes as $t): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($t['payment_date'])) ?></td>
                        <td><span class="badge bg-<?= ($t['type'] ?? 'Dízimo') == 'Dízimo' ? 'primary' : 'success' ?>"><?= $t['type'] ?? 'Dízimo' ?></span></td>
                        <td>R$ <?= number_format($t['amount'], 2, ',', '.') ?></td>
                        <td><?= ucfirst($t['payment_method']) ?></td>
                        <td>
                            <a href="/admin/tithes/receipt/<?= $t['id'] ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-file-invoice"></i> Ver
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
