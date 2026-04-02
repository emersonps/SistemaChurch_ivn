<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Contas e Caixas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/financial/bank-accounts/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Nova Conta/Caixa
        </a>
    </div>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['flash_success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Banco/Agência/Conta</th>
                        <th>Saldo Atual</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $acc): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($acc['name']) ?></td>
                            <td>
                                <?php 
                                    $types = [
                                        'caixa' => 'Caixa Físico', 
                                        'conta_corrente' => 'Conta Corrente', 
                                        'poupanca' => 'Poupança', 
                                        'investimento' => 'Investimento',
                                        'centro_custo' => 'Centro de Custo'
                                    ];
                                    echo $types[$acc['type']] ?? $acc['type'];
                                ?>
                            </td>
                            <td>
                                <?php if (!in_array($acc['type'], ['caixa', 'centro_custo'], true)): ?>
                                    <?= htmlspecialchars($acc['bank_name'] ?? '-') ?><br>
                                    <small class="text-muted">Ag: <?= htmlspecialchars($acc['agency'] ?? '-') ?> | CC: <?= htmlspecialchars($acc['account_number'] ?? '-') ?></small>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold <?= $acc['current_balance'] < 0 ? 'text-danger' : 'text-success' ?>">
                                R$ <?= number_format($acc['current_balance'], 2, ',', '.') ?>
                            </td>
                            <td>
                                <?php if ($acc['status'] === 'active'): ?>
                                    <span class="badge bg-success">Ativa</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inativa</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="/admin/financial/bank-accounts/edit/<?= $acc['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($accounts)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">Nenhuma conta cadastrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
