<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Planos de Contas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/financial/chart-accounts" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card mb-4">
            <div class="card-header bg-white">Criar Novo Plano</div>
            <div class="card-body">
                <form method="POST" action="/admin/financial/account-sets/store">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Escopo</label>
                        <select name="scope" id="scopeSelect" class="form-select">
                            <option value="general">Geral (Sistema)</option>
                            <option value="congregation">Por Congregação</option>
                        </select>
                    </div>
                    <div class="mb-3" id="congregationBox" style="display:none">
                        <label class="form-label">Congregação</label>
                        <select name="congregation_id" class="form-select">
                            <option value="">Selecione...</option>
                            <?php foreach (($congregations ?? []) as $cg): ?>
                                <option value="<?= $cg['id'] ?>"><?= htmlspecialchars($cg['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Criar</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-white">Conjuntos Existentes</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Escopo</th>
                                <th>Status</th>
                                <th>Padrão</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (($sets ?? []) as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['name']) ?></td>
                                    <td>
                                        <?php if (!empty($s['congregation_id'])): ?>
                                            <span class="badge bg-info text-dark">Congregação</span>
                                            <small class="text-muted"><?= htmlspecialchars($s['congregation_name'] ?? '') ?></small>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Geral</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= ($s['active'] ? 'success' : 'secondary') ?>"><?= $s['active'] ? 'Ativo' : 'Inativo' ?></span>
                                    </td>
                                    <td>
                                        <?php if ($s['is_default']): ?>
                                            <span class="badge bg-info text-dark">Padrão</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="/admin/financial/account-sets/edit/<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                            <?php if (!$s['is_default']): ?>
                                                <a href="/admin/financial/account-sets/make-default/<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">Tornar padrão</a>
                                            <?php endif; ?>
                                            <a href="/admin/financial/account-sets/toggle/<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary"><?= $s['active'] ? 'Desativar' : 'Ativar' ?></a>
                                            <a href="/admin/financial/account-sets/delete/<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Excluir este Plano? Apenas conjuntos sem contas podem ser excluídos.')">Excluir</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($sets)): ?>
                                <tr><td colspan="4" class="text-center py-4">Nenhum Plano cadastrado.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const scopeSelect = document.getElementById('scopeSelect');
const congregationBox = document.getElementById('congregationBox');
function updateScope() {
    congregationBox.style.display = scopeSelect.value === 'congregation' ? '' : 'none';
}
scopeSelect.addEventListener('change', updateScope);
updateScope();
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
