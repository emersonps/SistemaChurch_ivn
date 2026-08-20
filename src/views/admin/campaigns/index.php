<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Campanhas</h1>
    <a href="/admin/campaigns/create" class="btn btn-sm btn-primary rounded-pill fw-semibold px-3">
        <i class="fas fa-plus me-1"></i> Nova Campanha
    </a>
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
    .cp-stat-card {
        background: #fff; border: 1px solid rgba(0,0,0,0.08); border-radius: 14px;
        padding: 1rem 1.1rem; height: 100%;
    }
    .cp-stat-label { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: #868e96; font-weight: 700; }
    .cp-stat-value { font-size: 1.5rem; font-weight: 800; color: #212529; }
    .cp-card {
        background: #fff; border: 1px solid rgba(0,0,0,0.08); border-radius: 16px;
        padding: 1.2rem 1.3rem; height: 100%;
    }
    .cp-progress { background: #eef0f2; border-radius: 999px; height: 10px; overflow: hidden; }
    .cp-progress-bar { height: 100%; border-radius: 999px; background: #198754; transition: width .3s ease; }
    .cp-badge {
        display: inline-flex; align-items: center; padding: .25rem .6rem; border-radius: 999px;
        font-size: .7rem; font-weight: 700;
    }
    .cp-badge.status-active { background: rgba(25,135,84,0.12); color: #198754; }
    .cp-badge.status-completed { background: rgba(13,110,253,0.12); color: #0d6efd; }
    .cp-badge.status-cancelled { background: rgba(108,117,125,0.15); color: #6c757d; }
    .icon-btn {
        width: 32px; height: 32px; display: inline-flex; align-items: center;
        justify-content: center; border-radius: 50%; padding: 0;
    }
</style>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="cp-stat-card">
            <div class="cp-stat-label">Campanhas</div>
            <div class="cp-stat-value"><?= (int)$stats['total'] ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="cp-stat-card">
            <div class="cp-stat-label">Ativas</div>
            <div class="cp-stat-value"><?= (int)$stats['active'] ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="cp-stat-card">
            <div class="cp-stat-label">Meta Total</div>
            <div class="cp-stat-value">R$ <?= number_format((float)$stats['goal_total'], 2, ',', '.') ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="cp-stat-card">
            <div class="cp-stat-label">Arrecadado</div>
            <div class="cp-stat-value">R$ <?= number_format((float)$stats['raised_total'], 2, ',', '.') ?></div>
        </div>
    </div>
</div>

<?php if (empty($campaigns)): ?>
    <div class="text-center text-muted py-5">Nenhuma campanha cadastrada ainda.</div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($campaigns as $campaign): ?>
            <div class="col-md-6 col-lg-4">
                <div class="cp-card">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="mb-0"><?= htmlspecialchars($campaign['title']) ?></h5>
                        <span class="cp-badge status-<?= htmlspecialchars($campaign['status']) ?>">
                            <?= $campaign['status'] === 'active' ? 'Ativa' : ($campaign['status'] === 'completed' ? 'Concluída' : 'Cancelada') ?>
                        </span>
                    </div>
                    <div class="text-muted small mb-2">
                        Meta: R$ <?= number_format((float)$campaign['progress']['goal'], 2, ',', '.') ?>
                        · <?= htmlspecialchars($campaign['commitment_type'] === 'fixed' ? 'Valor fixo' : 'Valor livre') ?>
                    </div>
                    <div class="cp-progress mb-1">
                        <div class="cp-progress-bar" style="width: <?= (float)$campaign['progress']['percent_display'] ?>%;"></div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-3">
                        <span>R$ <?= number_format((float)$campaign['progress']['raised'], 2, ',', '.') ?> arrecadado</span>
                        <span><?= (int)round($campaign['progress']['percent']) ?>%</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="/admin/campaigns/<?= (int)$campaign['id'] ?>" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="fas fa-users me-1"></i> Participantes
                        </a>
                        <a href="/admin/campaigns/edit/<?= (int)$campaign['id'] ?>" class="btn btn-sm btn-outline-secondary icon-btn" title="Editar">
                            <i class="fas fa-pen"></i>
                        </a>
                        <form action="/admin/campaigns/delete/<?= (int)$campaign['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Excluir esta campanha? As parcelas e participantes também serão removidos.');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger icon-btn" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
