<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Conta Contábil</h1>
    <a href="/admin/financial/chart-accounts" class="btn btn-outline-secondary">Voltar</a>
</div>

<div class="card shadow-sm">
        <div class="card-body">
            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['flash_error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>
            <div class="mb-3">
                <label class="form-label">Plano de Contas</label>
                <select name="account_set_id" class="form-select">
                    <?php foreach (($sets ?? []) as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= (int)$account['account_set_id'] === (int)$s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?> <?= (int)$s['is_default'] === 1 ? '(Padrão)' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <form action="/admin/financial/chart-accounts/update/<?= $account['id'] ?>" method="POST">
            <?= csrf_field() ?>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Estrutura <span class="text-danger">*</span></label>
                    <?php $isSynthetic = empty($account['parent_id']); ?>
                    <select name="structure" id="structureSelect" class="form-select" required>
                        <option value="synthetic" <?= $isSynthetic ? 'selected' : '' ?>>Sintética (Pai)</option>
                        <option value="analytic" <?= !$isSynthetic ? 'selected' : '' ?>>Analítica (Filho)</option>
                    </select>
                    <small class="text-muted">Sintética não possui conta pai; Analítica deve estar vinculada a uma conta pai.</small>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Código (Ex: 1.1.2) <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control" required value="<?= htmlspecialchars($account['code']) ?>">
                </div>
                
                <div class="col-md-5 mb-3">
                    <label class="form-label">Nome da Conta <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($account['name']) ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label mb-0">Natureza <span class="text-danger">*</span></label>
                        <a href="/admin/financial/chart-account-natures" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-tags me-1"></i> Gerenciar
                        </a>
                    </div>
                    <?php if (!empty($hasNatureFeature)): ?>
                        <select name="nature_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach (($natures ?? []) as $nature): ?>
                                <?php $selectedNature = (int)($account['nature_id'] ?? 0) === (int)$nature['id']; ?>
                                <option value="<?= $nature['id'] ?>" <?= $selectedNature ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($nature['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <select name="type" class="form-select" required>
                            <option value="asset" <?= $account['type'] === 'asset' ? 'selected' : '' ?>>Ativo (Bens e Direitos)</option>
                            <option value="liability" <?= $account['type'] === 'liability' ? 'selected' : '' ?>>Passivo (Obrigações)</option>
                            <option value="income" <?= $account['type'] === 'income' ? 'selected' : '' ?>>Receita (Entradas)</option>
                            <option value="expense" <?= $account['type'] === 'expense' ? 'selected' : '' ?>>Despesa (Saídas)</option>
                        </select>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Conta Pai (Opcional)</label>
                    <select name="parent_id" id="parentSelect" class="form-select">
                        <option value="">-- Nenhuma (Conta Principal) --</option>
                        <?php foreach ($parents as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $account['parent_id'] == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['code'] . ' - ' . $p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= $account['status'] === 'active' ? 'selected' : '' ?>>Ativa</option>
                        <option value="inactive" <?= $account['status'] === 'inactive' ? 'selected' : '' ?>>Inativa</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Descrição / Observações</label>
                <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($account['description'] ?? '') ?></textarea>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Atualizar Conta</button>
            </div>
        </form>
    </div>
</div>

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
