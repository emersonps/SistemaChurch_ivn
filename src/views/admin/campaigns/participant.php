<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0"><?= htmlspecialchars($participant['member_name']) ?></h1>
        <p class="text-muted mb-0">Campanha: <?= htmlspecialchars($participant['campaign_title']) ?></p>
    </div>
    <a href="/admin/campaigns/<?= (int)$participant['campaign_id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-arrow-left me-1"></i> Voltar</a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<style>
    .cp-status { padding: .25rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 700; }
    .cp-status.pending { background: rgba(255,193,7,0.18); color: #997404; }
    .cp-status.paid { background: rgba(25,135,84,0.15); color: #198754; }
</style>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><i class="fas fa-calendar-check me-1"></i> Parcelas</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Mês</th>
                            <th>Comprometido</th>
                            <th>Status</th>
                            <th>Pago</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($installments)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Nenhuma parcela cadastrada ainda.</td></tr>
                        <?php else: ?>
                            <?php foreach ($installments as $inst): ?>
                                <tr>
                                    <td><?= htmlspecialchars(formatReferenceMonth($inst['reference_month'])) ?></td>
                                    <td>R$ <?= number_format((float)$inst['committed_amount'], 2, ',', '.') ?></td>
                                    <td><span class="cp-status <?= $inst['status'] ?>"><?= $inst['status'] === 'paid' ? 'Pago' : 'Pendente' ?></span></td>
                                    <td>
                                        <?php if ($inst['status'] === 'paid'): ?>
                                            R$ <?= number_format((float)$inst['paid_amount'], 2, ',', '.') ?><br>
                                            <span class="text-muted small"><?= date('d/m/Y', strtotime($inst['paid_date'])) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end" style="min-width: 180px;">
                                        <?php if ($inst['status'] === 'paid'): ?>
                                            <form action="/admin/campaigns/installments/<?= (int)$inst['id'] ?>/unpay" method="POST" class="d-inline" onsubmit="return confirm('Desfazer este pagamento? O lançamento correspondente em Dízimos/Ofertas também será removido.');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-rotate-left me-1"></i> Desfazer
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form action="/admin/campaigns/installments/<?= (int)$inst['id'] ?>/pay" method="POST" class="d-flex gap-1 justify-content-end align-items-center">
                                                <?= csrf_field() ?>
                                                <input type="text" name="paid_amount" class="form-control form-control-sm" style="width: 90px;" value="<?= htmlspecialchars((string)$inst['committed_amount']) ?>" required>
                                                <input type="date" name="paid_date" class="form-control form-control-sm" style="width: 140px;" value="<?= date('Y-m-d') ?>" required>
                                                <button type="submit" class="btn btn-sm btn-success" title="Marcar como pago">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($participant['commitment_type'] === 'flexible'): ?>
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white"><i class="fas fa-plus me-1"></i> Adicionar Mês</div>
                <div class="card-body">
                    <form action="/admin/campaigns/participants/<?= (int)$participant['id'] ?>/installments" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Mês</label>
                            <input type="month" name="reference_month" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Valor (R$)</label>
                            <input type="text" name="committed_amount" class="form-control" placeholder="Ex: 100.00" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i> Adicionar</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
