<?php include __DIR__ . '/layout/header.php'; ?>

<div class="portal-card text-center py-5 px-4">
    <div class="mx-auto mb-3" style="width: 64px; height: 64px; border-radius: 50%; background: rgba(255,193,7,0.14); color: #997404; display: flex; align-items: center; justify-content: center; font-size: 1.6rem;">
        <i class="fas fa-triangle-exclamation"></i>
    </div>
    <h5 class="fw-bold mb-2"><?= htmlspecialchars($title ?? 'Documento indisponível') ?></h5>
    <p class="text-muted mb-4"><?= htmlspecialchars($message ?? 'O arquivo solicitado não foi encontrado ou está inacessível.') ?></p>
    <a href="/portal/documents" class="btn btn-outline-secondary rounded-pill fw-semibold px-4">
        <i class="fas fa-arrow-left me-1"></i> Voltar para Meus Documentos
    </a>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
