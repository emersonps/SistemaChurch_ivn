<?php include __DIR__ . '/layout/header.php'; ?>

<style>
    .cp-progress { background: #eef0f2; border-radius: 999px; height: 8px; overflow: hidden; }
    .cp-progress-bar { height: 100%; border-radius: 999px; background: #198754; transition: width .3s ease; }
</style>

<div class="portal-page-title">Campanhas</div>
<p class="text-muted mb-3">Campanhas de arrecadação que você está participando.</p>

<?php if (empty($campaigns)): ?>
    <div class="portal-card">
        <div class="text-center text-muted py-5">
            <i class="fas fa-bullseye fa-2x mb-3 opacity-50"></i>
            <p class="mb-0">Você ainda não participa de nenhuma campanha.</p>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($campaigns as $c): ?>
        <div class="portal-card mb-3">
            <div class="p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="fw-bold"><?= htmlspecialchars($c['title']) ?></div>
                        <?php if (!empty($c['description'])): ?>
                            <div class="text-muted small"><?= htmlspecialchars($c['description']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="h5 mb-0"><?= (int)round($c['progress']['percent']) ?>%</div>
                </div>
                <div class="cp-progress mb-3">
                    <div class="cp-progress-bar" style="width: <?= (float)$c['progress']['percent_display'] ?>%;"></div>
                </div>
                <a href="/portal/campaigns/<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold">Ver meu compromisso</a>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include __DIR__ . '/layout/footer.php'; ?>
