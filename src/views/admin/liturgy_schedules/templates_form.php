<?php include __DIR__ . '/../../layout/header.php'; ?>

<?php
$isEdit = $template !== null;
$activeKeys = array_column($activeRoles, 'key');
$activeLabels = array_column($activeRoles, 'label', 'key');
$customValues = [];
foreach ($activeRoles as $r) {
    if (strpos($r['key'], 'custom_') === 0) {
        $customValues[] = $r['label'];
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $isEdit ? 'Editar Modelo' : 'Novo Modelo de Escala' ?></h1>
    <a href="/admin/liturgy-schedules/templates" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-arrow-left me-1"></i> Voltar</a>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="card shadow-sm" style="max-width: 640px;">
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? '/admin/liturgy-schedules/templates/edit/' . (int)$template['id'] : '/admin/liturgy-schedules/templates/create' ?>">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Nome do modelo</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($template['name'] ?? '') ?>" placeholder="Ex: Escala Mensal - Congregação Sede" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Congregação (opcional)</label>
                <select name="congregation_id" class="form-select">
                    <option value="">Todas as congregações</option>
                    <?php foreach ($congregations as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= (int)($template['congregation_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <label class="form-label">Papéis da escala</label>
            <div class="border rounded p-3 mb-3">
                <?php foreach ($roleCatalog as $key => $defaultLabel): ?>
                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-auto">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="<?= $key ?>" id="role_<?= $key ?>" <?= in_array($key, $activeKeys, true) ? 'checked' : '' ?>>
                            </div>
                        </div>
                        <div class="col">
                            <label class="form-label mb-0 small text-muted" for="role_<?= $key ?>"><?= htmlspecialchars($defaultLabel) ?></label>
                        </div>
                        <div class="col-6">
                            <input type="text" name="role_label[<?= $key ?>]" class="form-control form-control-sm" value="<?= htmlspecialchars($activeLabels[$key] ?? $defaultLabel) ?>" placeholder="Rótulo exibido">
                        </div>
                    </div>
                <?php endforeach; ?>

                <hr>
                <div class="small text-muted mb-2">Colunas personalizadas (opcional)</div>
                <div id="customFieldsList">
                    <?php foreach ($customValues as $customLabel): ?>
                        <div class="input-group input-group-sm mb-2 custom-field-row">
                            <input type="text" name="custom_labels[]" class="form-control" value="<?= htmlspecialchars($customLabel) ?>" placeholder="Ex: Regente do Coral">
                            <button type="button" class="btn btn-outline-danger btn-remove-custom" title="Remover campo"><i class="fas fa-trash"></i></button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddCustomField"><i class="fas fa-plus me-1"></i> Adicionar campo personalizado</button>
            </div>

            <div class="form-text mb-3">"Observações" é incluída automaticamente em toda escala.</div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Salvar</button>
        </form>
    </div>
</div>

<template id="customFieldTemplate">
    <div class="input-group input-group-sm mb-2 custom-field-row">
        <input type="text" name="custom_labels[]" class="form-control" placeholder="Ex: Regente do Coral">
        <button type="button" class="btn btn-outline-danger btn-remove-custom" title="Remover campo"><i class="fas fa-trash"></i></button>
    </div>
</template>

<script>
(function () {
    var list = document.getElementById('customFieldsList');
    var tpl = document.getElementById('customFieldTemplate');

    function wireRemove(row) {
        row.querySelector('.btn-remove-custom').addEventListener('click', function () {
            row.remove();
        });
    }

    list.querySelectorAll('.custom-field-row').forEach(wireRemove);

    document.getElementById('btnAddCustomField').addEventListener('click', function () {
        var wrapper = document.createElement('div');
        wrapper.innerHTML = tpl.innerHTML.trim();
        var row = wrapper.firstElementChild;
        list.appendChild(row);
        wireRemove(row);
        row.querySelector('input').focus();
    });
})();
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
