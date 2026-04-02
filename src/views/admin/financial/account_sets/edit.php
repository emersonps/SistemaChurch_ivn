<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Plano</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/financial/account-sets" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/admin/financial/account-sets/update/<?= (int)$set['id'] ?>" class="row g-3">
            <?= csrf_field() ?>
            <div class="col-md-6">
                <label class="form-label">Nome</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($set['name']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Descrição</label>
                <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($set['description'] ?? '') ?>">
            </div>
            <?php if (!empty($hasCongCol)): ?>
            <div class="col-md-6">
                <label class="form-label">Escopo</label>
                <?php $isCong = !empty($set['congregation_id']); ?>
                <select name="scope" id="scopeSelect" class="form-select">
                    <option value="general" <?= !$isCong ? 'selected' : '' ?>>Geral (Sistema)</option>
                    <option value="congregation" <?= $isCong ? 'selected' : '' ?>>Por Congregação</option>
                </select>
            </div>
            <div class="col-md-6" id="congregationBox" style="<?= $isCong ? '' : 'display:none' ?>">
                <label class="form-label">Congregação</label>
                <select name="congregation_id" class="form-select">
                    <option value="">Selecione...</option>
                    <?php foreach (($congregations ?? []) as $cg): ?>
                        <option value="<?= $cg['id'] ?>" <?= (int)$set['congregation_id'] === (int)$cg['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cg['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
 </div>

<script>
const scopeSelect = document.getElementById('scopeSelect');
const congregationBox = document.getElementById('congregationBox');
if (scopeSelect) {
    function updateScope() {
        congregationBox.style.display = scopeSelect.value === 'congregation' ? '' : 'none';
    }
    scopeSelect.addEventListener('change', updateScope);
}
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
