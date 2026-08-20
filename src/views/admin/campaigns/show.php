<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0"><?= htmlspecialchars($campaign['title']) ?></h1>
        <?php if (!empty($campaign['description'])): ?>
            <p class="text-muted mb-0"><?= htmlspecialchars($campaign['description']) ?></p>
        <?php endif; ?>
    </div>
    <a href="/admin/campaigns" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-arrow-left me-1"></i> Voltar</a>
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
    .cp-progress { background: #eef0f2; border-radius: 999px; height: 14px; overflow: hidden; }
    .cp-progress-bar { height: 100%; border-radius: 999px; background: #198754; transition: width .3s ease; }
    .icon-btn {
        width: 32px; height: 32px; display: inline-flex; align-items: center;
        justify-content: center; border-radius: 50%; padding: 0;
    }
</style>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-end mb-2">
            <div>
                <div class="text-muted small">Meta: R$ <?= number_format((float)$progress['goal'], 2, ',', '.') ?></div>
                <div class="h4 mb-0">R$ <?= number_format((float)$progress['raised'], 2, ',', '.') ?> arrecadado</div>
            </div>
            <div class="h3 mb-0"><?= (int)round($progress['percent']) ?>%</div>
        </div>
        <div class="cp-progress">
            <div class="cp-progress-bar" style="width: <?= (float)$progress['percent_display'] ?>%;"></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><i class="fas fa-users me-1"></i> Participantes</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Membro</th>
                            <th>Compromisso</th>
                            <th>Progresso</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($participants)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Nenhum participante ainda.</td></tr>
                        <?php else: ?>
                            <?php foreach ($participants as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['member_name']) ?></td>
                                    <td>
                                        <?php if (!empty($p['monthly_amount'])): ?>
                                            R$ <?= number_format((float)$p['monthly_amount'], 2, ',', '.') ?> × <?= (int)$p['months_committed'] ?> meses
                                        <?php else: ?>
                                            <span class="text-muted">Valor livre</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= (int)$p['paid_installments'] ?>/<?= (int)$p['total_installments'] ?> parcelas
                                        · R$ <?= number_format((float)$p['total_paid'], 2, ',', '.') ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="/admin/campaigns/participants/<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver parcelas">
                                            <i class="fas fa-list-check"></i>
                                        </a>
                                        <form action="/admin/campaigns/participants/<?= (int)$p['id'] ?>/remove" method="POST" class="d-inline" onsubmit="return confirm('Remover este participante da campanha?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger icon-btn" title="Remover">
                                                <i class="fas fa-user-minus"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><i class="fas fa-user-plus me-1"></i> Adicionar Participante</div>
            <div class="card-body">
                <?php if (empty($availableMembers)): ?>
                    <div class="text-muted small">Todos os membros disponíveis já participam desta campanha.</div>
                <?php else: ?>
                    <form action="/admin/campaigns/<?= (int)$campaign['id'] ?>/participants" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Membro</label>
                            <select name="member_id" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($availableMembers as $m): ?>
                                    <option value="<?= (int)$m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if ($campaign['commitment_type'] === 'fixed'): ?>
                            <div class="mb-3">
                                <label class="form-label">Valor mensal (R$)</label>
                                <input type="text" name="monthly_amount" class="form-control" placeholder="Ex: 100.00" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Quantidade de meses</label>
                                <input type="number" name="months_committed" class="form-control" min="1" placeholder="Ex: 6" required>
                            </div>
                            <div class="form-text mb-3">As parcelas mensais são geradas automaticamente a partir do mês atual (ou do início da campanha, o que for mais tarde).</div>
                        <?php else: ?>
                            <div class="form-text mb-3">Essa campanha é de valor livre — depois de adicionar o participante, cadastre cada mês e valor na tela dele.</div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i> Adicionar</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
