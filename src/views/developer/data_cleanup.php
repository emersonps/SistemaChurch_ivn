<?php include __DIR__ . '/layout_developer.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Limpar Dados</h1>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="alert alert-warning">
    <i class="fas fa-triangle-exclamation me-1"></i>
    <strong>Ação irreversível.</strong> Use isso só numa instância recém-provisionada que ainda tem dados de
    exemplo/demo — nunca numa igreja que já está em uso real. Não existe desfazer.
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-success text-white"><i class="fas fa-shield me-1"></i> Vai ficar (preservado)</div>
            <div class="card-body">
                <p class="text-muted small">Usuários, papéis, permissões, configurações do sistema e o hinário (Harpa Cristã).</p>
                <div class="d-flex flex-wrap gap-1">
                    <?php foreach ($keepTables as $t): ?>
                        <span class="badge bg-success-subtle text-success-emphasis border"><?= htmlspecialchars($t) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-danger text-white"><i class="fas fa-trash me-1"></i> Vai ser apagado (<?= count($clearTables) ?> tabelas)</div>
            <div class="card-body" style="max-height: 320px; overflow-y: auto;">
                <div class="d-flex flex-wrap gap-1">
                    <?php foreach ($clearTables as $t): ?>
                        <span class="badge bg-danger-subtle text-danger-emphasis border"><?= htmlspecialchars($t) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-body">
        <form id="cleanupForm" action="/developer/data-cleanup/run" method="POST" onsubmit="return confirm('Confirmar? Isso apaga permanentemente os dados das tabelas listadas acima.');">
            <?= csrf_field() ?>
            <label class="form-label fw-bold">
                Pra confirmar, digite o nome da instância: <span class="text-danger"><?= htmlspecialchars($confirmationLabel) ?></span>
            </label>
            <div class="input-group" style="max-width: 480px;">
                <input type="text" name="confirmation" id="confirmationInput" class="form-control" autocomplete="off" required>
                <button type="submit" class="btn btn-danger" id="confirmButton" disabled>
                    <i class="fas fa-trash me-1"></i> Limpar dados agora
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var expected = <?= json_encode($confirmationLabel, JSON_UNESCAPED_UNICODE) ?>;
        var input = document.getElementById('confirmationInput');
        var button = document.getElementById('confirmButton');
        input.addEventListener('input', function () {
            button.disabled = input.value.trim().toLowerCase() !== expected.trim().toLowerCase();
        });
    })();
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
