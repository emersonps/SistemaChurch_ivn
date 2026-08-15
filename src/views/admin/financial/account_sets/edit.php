<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Plano</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/financial/account-sets" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<style>
    .member-form-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
    }
    .member-form-card-header {
        display: flex;
        align-items: flex-start;
        gap: .85rem;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.07);
        background: #fafafa;
    }
    .member-form-badge {
        flex: 0 0 auto;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #eef0f2;
        color: #212529;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: .95rem;
    }
    .member-form-card-title {
        font-weight: 800;
        font-size: 1.05rem;
        color: #1a1a1a;
        line-height: 1.2;
    }
    .member-form-card-subtitle {
        font-size: .82rem;
        color: #868e96;
        margin-top: .1rem;
    }
    .member-form-card-body { padding: 1.25rem; }
    .member-form-card-body .form-label {
        font-weight: 600;
        font-size: .88rem;
        color: #343a40;
    }
    .member-form-card-body .form-control,
    .member-form-card-body .form-select {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
        padding: .55rem .8rem;
    }
    .member-form-card-body .form-control:focus,
    .member-form-card-body .form-select:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
</style>

<div class="member-form-card">
    <div class="member-form-card-header">
        <div class="member-form-badge"><i class="fas fa-edit"></i></div>
        <div>
            <div class="member-form-card-title"><?= htmlspecialchars($set['name']) ?></div>
            <div class="member-form-card-subtitle">Atualize os dados deste plano de contas.</div>
        </div>
    </div>
    <div class="member-form-card-body">
        <form method="POST" action="/admin/financial/account-sets/update/<?= (int)$set['id'] ?>" class="row g-3">
            <?= csrf_field() ?>
            <div class="col-md-6">
                <label class="form-label">Nome <span class="text-danger">*</span></label>
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
                <button type="submit" class="btn btn-dark rounded-pill fw-semibold px-4"><i class="fas fa-save me-2"></i> Salvar</button>
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
