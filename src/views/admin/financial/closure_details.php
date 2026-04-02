<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Detalhes do Fechamento</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/financial/closures" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Resumo: <?= htmlspecialchars($closure['type']) ?> - <?= htmlspecialchars($closure['period']) ?></h5>
        <small class="text-muted"><?= htmlspecialchars($closure['congregation_name']) ?></small>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-3 border-end">
                <h6 class="text-muted">Entradas</h6>
                <h4 class="text-success">R$ <?= number_format($closure['total_entries'], 2, ',', '.') ?></h4>
            </div>
            <div class="col-md-3 border-end">
                <h6 class="text-muted">Saídas</h6>
                <h4 class="text-danger">R$ <?= number_format($closure['total_expenses'], 2, ',', '.') ?></h4>
            </div>
            <div class="col-md-3 border-end">
                <h6 class="text-muted">Saldo do Período</h6>
                <h4 class="<?= $closure['balance'] >= 0 ? 'text-success' : 'text-danger' ?>">
                    R$ <?= number_format($closure['balance'], 2, ',', '.') ?>
                </h4>
            </div>
            <div class="col-md-3 bg-light rounded py-2">
                <h6 class="text-muted">Saldo Final (Acumulado)</h6>
                <h3 class="text-primary">R$ <?= number_format($closure['final_balance'], 2, ',', '.') ?></h3>
                <small>Anterior: R$ <?= number_format($closure['previous_balance'], 2, ',', '.') ?></small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">Detalhamento Entradas</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Dízimos
                    <span>R$ <?= number_format($closure['total_tithes'], 2, ',', '.') ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Ofertas
                    <span>R$ <?= number_format($closure['total_offerings'], 2, ',', '.') ?></span>
                </li>
                <li class="list-group-item list-group-item-secondary d-flex justify-content-between align-items-center fw-bold">
                    Total
                    <span>R$ <?= number_format($closure['total_entries'], 2, ',', '.') ?></span>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">Detalhamento Saídas</div>
            <div class="card-body">
                <p class="card-text text-center">
                    Total de Saídas: <strong class="text-danger">R$ <?= number_format($closure['total_expenses'], 2, ',', '.') ?></strong>
                </p>
                <!-- If we stored categorized expenses in JSON, we could list them here. 
                     For now, it's just the total as per migration. -->
                <div class="alert alert-info btn-sm">
                    Para ver os lançamentos individuais, consulte o relatório financeiro do período <?= date('d/m/Y', strtotime($closure['start_date'])) ?> a <?= date('d/m/Y', strtotime($closure['end_date'])) ?>.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 text-muted text-end">
    <small>Gerado por <?= htmlspecialchars($closure['creator_name']) ?> em <?= date('d/m/Y H:i', strtotime($closure['created_at'])) ?></small>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>