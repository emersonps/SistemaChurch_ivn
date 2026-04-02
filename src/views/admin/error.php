<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Aviso do Sistema</h1>
</div>

<div class="alert alert-warning" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <?= htmlspecialchars($message ?? 'Ocorreu um problema de configuração.') ?>
</div>

<?php if (!empty($hint)): ?>
    <div class="card">
        <div class="card-body">
            <p class="mb-2"><?= htmlspecialchars($hint) ?></p>
            <a href="/developer/dashboard" class="btn btn-outline-secondary btn-sm">Abrir Gerenciador de Migrações</a>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>
