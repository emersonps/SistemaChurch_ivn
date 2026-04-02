<?php include __DIR__ . '/layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= htmlspecialchars($title ?? 'Documento indisponível') ?></h1>
</div>

<div class="alert alert-warning d-flex align-items-center" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <div><?= htmlspecialchars($message ?? 'O arquivo solicitado não foi encontrado ou está inacessível.') ?></div>
</div>

<div class="mt-3">
    <a href="/portal/documents" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Voltar para Meus Documentos
    </a>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
