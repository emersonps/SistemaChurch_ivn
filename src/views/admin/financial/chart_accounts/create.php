<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Nova Conta Contábil</h1>
    <a href="/admin/financial/chart-accounts" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">Voltar</a>
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
    .required-mark { color: #dc3545; }
</style>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?= $_SESSION['flash_error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<form action="/admin/financial/chart-accounts/store" method="POST">
    <?= csrf_field() ?>

    <div class="member-form-card mb-3">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-sitemap"></i></div>
            <div>
                <div class="member-form-card-title">Dados da Conta Contábil</div>
                <div class="member-form-card-subtitle">Plano de contas, código, natureza e estrutura.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Plano de Contas</label>
                    <select name="account_set_id" class="form-select">
                        <?php foreach (($sets ?? []) as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= ($selectedSet ?? 0) == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?> <?= (int)$s['is_default'] === 1 ? '(Padrão)' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Estrutura <span class="required-mark">*</span></label>
                    <select name="structure" id="structureSelect" class="form-select" required>
                        <option value="synthetic">Sintética (Pai)</option>
                        <option value="analytic" selected>Analítica (Filho)</option>
                    </select>
                    <div class="form-text">Sintética não possui conta pai; Analítica deve estar vinculada a uma conta pai.</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Código (Ex: 1.1.2) <span class="required-mark">*</span></label>
                    <input type="text" name="code" class="form-control" required>
                </div>

                <div class="col-md-5">
                    <label class="form-label">Nome da Conta <span class="required-mark">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="Ex: Despesas com Pessoal">
                </div>

                <div class="col-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label mb-0">Natureza <span class="required-mark">*</span></label>
                        <a href="/admin/financial/chart-account-natures" class="btn btn-sm btn-outline-secondary rounded-pill">
                            <i class="fas fa-tags me-1"></i> Gerenciar
                        </a>
                    </div>
                    <?php if (!empty($hasNatureFeature)): ?>
                        <select name="nature_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach (($natures ?? []) as $nature): ?>
                                <option value="<?= $nature['id'] ?>" <?= $nature['base_type'] === 'expense' ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($nature['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <select name="type" class="form-select" required>
                            <option value="asset">Ativo (Bens e Direitos)</option>
                            <option value="liability">Passivo (Obrigações)</option>
                            <option value="income">Receita (Entradas)</option>
                            <option value="expense" selected>Despesa (Saídas)</option>
                        </select>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Conta Pai (Opcional)</label>
                    <select name="parent_id" id="parentSelect" class="form-select">
                        <option value="">-- Nenhuma (Conta Principal) --</option>
                        <?php foreach ($parents as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['code'] . ' - ' . $p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" selected>Ativa</option>
                        <option value="inactive">Inativa</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Descrição / Observações</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-4">
        <button type="submit" class="btn btn-dark rounded-pill fw-semibold px-4"><i class="fas fa-save me-2"></i> Salvar Conta</button>
    </div>
</form>

<script>
const structSel = document.getElementById('structureSelect');
const parentSel = document.getElementById('parentSelect');
function applyStructure() {
    const v = structSel.value;
    if (v === 'synthetic') {
        parentSel.value = '';
        parentSel.setAttribute('disabled', 'disabled');
        parentSel.removeAttribute('required');
    } else {
        parentSel.removeAttribute('disabled');
        parentSel.setAttribute('required', 'required');
    }
}
structSel.addEventListener('change', applyStructure);
applyStructure();
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
