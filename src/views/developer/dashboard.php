<?php include __DIR__ . '/layout_developer.php'; ?>

<h1 class="h2 mb-4">Painel do Desenvolvedor</h1>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header">Usuários Admin</div>
            <div class="card-body">
                <h5 class="card-title display-4"><?= $users_count ?></h5>
                <p class="card-text">Usuários cadastrados no sistema.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Status do Sistema</div>
            <div class="card-body">
                <h5 class="card-title">Online</h5>
                <p class="card-text">O sistema está operando normalmente.</p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Últimos Pagamentos do Sistema</h5>
        <a href="/developer/payments" class="btn btn-sm btn-primary">Gerenciar Pagamentos</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Mês</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Data Pagamento</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="4" class="text-center">Nenhum registro.</td></tr>
                    <?php else: ?>
                        <?php foreach($payments as $p): ?>
                        <tr>
                            <td><?= $p['reference_month'] ?></td>
                            <td>R$ <?= number_format($p['amount'] ?? 59.99, 2, ',', '.') ?></td>
                            <td>
                                <span class="badge bg-<?= $p['status'] == 'paid' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($p['status']) ?>
                                </span>
                            </td>
                            <td><?= $p['payment_date'] ? date('d/m/Y H:i', strtotime($p['payment_date'])) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout_footer.php'; ?>
