<?php include __DIR__ . '/layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Estudos Bíblicos e Esboços</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/portal" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <?php if (empty($studies)): ?>
        <div class="col-12 text-center py-5">
            <i class="fas fa-book-open fa-4x text-muted mb-3"></i>
            <p class="text-muted">Nenhum estudo disponível no momento.</p>
        </div>
    <?php else: ?>
        <?php foreach ($studies as $s): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-danger"><?= htmlspecialchars($s['title']) ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            <i class="far fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($s['created_at'])) ?>
                        </h6>
                        <?php if (!empty($s['description'])): ?>
                            <p class="card-text"><?= nl2br(htmlspecialchars($s['description'])) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <a href="/uploads/studies/<?= $s['file_path'] ?>" target="_blank" class="btn btn-danger w-100">
                            <i class="fas fa-file-pdf me-2"></i> Baixar PDF
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
