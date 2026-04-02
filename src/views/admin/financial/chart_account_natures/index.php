<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Naturezas Contábeis</h1>
    <a href="/admin/financial/chart-accounts/create" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Voltar
    </a>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['flash_success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['flash_error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="row">
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">Adicionar Natureza</div>
            <div class="card-body">
                <form method="POST" action="/admin/financial/chart-account-natures/store">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ex: Patrimônio, Receita Missionária">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Grupo Base</label>
                        <select name="base_type" class="form-select" required>
                            <option value="asset">Ativo</option>
                            <option value="liability">Passivo</option>
                            <option value="income">Receita</option>
                            <option value="expense" selected>Despesa</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" selected>Ativa</option>
                            <option value="inactive">Inativa</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-1"></i> Adicionar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Editar ou Remover</div>
            <div class="card-body">
                <?php if (empty($natures)): ?>
                    <div class="text-muted">Nenhuma natureza cadastrada.</div>
                <?php else: ?>
                    <div class="vstack gap-3">
                        <?php foreach ($natures as $nature): ?>
                            <form method="POST" action="/admin/financial/chart-account-natures/update/<?= $nature['id'] ?>" class="border rounded p-3">
                                <?= csrf_field() ?>
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label">Nome</label>
                                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($nature['name']) ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Grupo Base</label>
                                        <select name="base_type" class="form-select" required>
                                            <option value="asset" <?= $nature['base_type'] === 'asset' ? 'selected' : '' ?>>Ativo</option>
                                            <option value="liability" <?= $nature['base_type'] === 'liability' ? 'selected' : '' ?>>Passivo</option>
                                            <option value="income" <?= $nature['base_type'] === 'income' ? 'selected' : '' ?>>Receita</option>
                                            <option value="expense" <?= $nature['base_type'] === 'expense' ? 'selected' : '' ?>>Despesa</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="active" <?= ($nature['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Ativa</option>
                                            <option value="inactive" <?= ($nature['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inativa</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-flex gap-2">
                                        <button type="submit" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-save me-1"></i> Salvar
                                        </button>
                                        <button type="submit"
                                                class="btn btn-outline-danger"
                                                formaction="/admin/financial/chart-account-natures/delete/<?= $nature['id'] ?>"
                                                onclick="return confirm('Deseja remover esta natureza?');">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
