<?php include __DIR__ . '/layout/header.php'; ?>

<style>
    .portal-study-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.06);
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(17,17,17,0.03);
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .portal-study-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: rgba(179,0,0,.08);
        color: var(--portal-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
    }
</style>

<div class="portal-page-title">Estudos e Esboços</div>
<p class="text-muted mb-3">Materiais em PDF disponíveis para você.</p>

<div class="row g-3">
    <?php if (empty($studies)): ?>
        <div class="col-12">
            <div class="portal-card text-center py-5 text-muted">
                <i class="fas fa-book-open fa-2x mb-3 opacity-50"></i>
                <p class="mb-0">Nenhum estudo disponível no momento.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($studies as $s): ?>
            <div class="col-md-6 col-lg-4">
                <div class="portal-study-card p-3">
                    <div class="d-flex align-items-start gap-3 mb-2">
                        <span class="portal-study-icon"><i class="fas fa-book-bible"></i></span>
                        <div>
                            <div class="fw-bold"><?= htmlspecialchars($s['title']) ?></div>
                            <div class="text-muted small"><i class="far fa-calendar-alt me-1"></i> <?= date('d/m/Y', strtotime($s['created_at'])) ?></div>
                        </div>
                    </div>
                    <?php if (!empty($s['description'])): ?>
                        <p class="text-muted small flex-grow-1"><?= nl2br(htmlspecialchars($s['description'])) ?></p>
                    <?php endif; ?>
                    <a href="/portal/studies/view/<?= $s['id'] ?>" target="_blank" class="btn btn-outline-danger rounded-pill fw-semibold mt-2">
                        <i class="fas fa-file-pdf me-2"></i> Abrir PDF
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
