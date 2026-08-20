<?php include __DIR__ . '/layout/header.php'; ?>

<style>
    .cp-progress { background: #eef0f2; border-radius: 999px; height: 10px; overflow: hidden; }
    .cp-progress-bar { height: 100%; border-radius: 999px; background: #198754; transition: width .3s ease; }
    .portal-tithe-row {
        display: flex; align-items: center; justify-content: space-between;
        gap: .75rem; padding: .85rem 1.25rem; border-top: 1px solid rgba(0,0,0,0.05);
    }
    .cp-status { padding: .25rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 700; }
    .cp-status.pending { background: rgba(255,193,7,0.18); color: #997404; }
    .cp-status.paid { background: rgba(25,135,84,0.15); color: #198754; }
</style>

<div class="portal-page-title"><?= htmlspecialchars($participant['title']) ?></div>
<?php if (!empty($participant['description'])): ?>
    <p class="text-muted mb-3"><?= htmlspecialchars($participant['description']) ?></p>
<?php endif; ?>

<div class="portal-card mb-3">
    <div class="p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-muted small">Progresso da meta da campanha</div>
            <div class="h5 mb-0"><?= (int)round($progress['percent']) ?>%</div>
        </div>
        <div class="cp-progress">
            <div class="cp-progress-bar" style="width: <?= (float)$progress['percent_display'] ?>%;"></div>
        </div>
    </div>
</div>

<div class="mb-2 fw-bold">Meu compromisso mensal</div>

<div class="portal-card">
    <?php if (empty($installments)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-calendar fa-2x mb-3 opacity-50"></i>
            <p class="mb-0">Nenhuma parcela cadastrada ainda.</p>
        </div>
    <?php else: ?>
        <?php foreach ($installments as $inst): ?>
            <div class="portal-tithe-row">
                <div>
                    <div class="fw-bold small"><?= htmlspecialchars(formatReferenceMonth($inst['reference_month'])) ?></div>
                    <div class="text-muted" style="font-size:.75rem;">
                        <span class="cp-status <?= $inst['status'] ?>"><?= $inst['status'] === 'paid' ? 'Pago' : 'Pendente' ?></span>
                        <?php if ($inst['status'] === 'paid'): ?>
                            · <?= date('d/m/Y', strtotime($inst['paid_date'])) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <strong>R$ <?= number_format((float)($inst['status'] === 'paid' ? $inst['paid_amount'] : $inst['committed_amount']), 2, ',', '.') ?></strong>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
