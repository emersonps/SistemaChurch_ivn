<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Naturezas Contábeis</h1>
    <a href="/admin/financial/chart-accounts/create" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">
        <i class="fas fa-arrow-left me-1"></i> Voltar
    </a>
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
    .nature-row {
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 12px;
        padding: 1rem;
        background: #fafafa;
    }
</style>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= $_SESSION['flash_success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?= $_SESSION['flash_error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="row">
    <div class="col-lg-4">
        <div class="member-form-card mb-3">
            <div class="member-form-card-header">
                <div class="member-form-badge"><i class="fas fa-plus"></i></div>
                <div>
                    <div class="member-form-card-title">Adicionar Natureza</div>
                    <div class="member-form-card-subtitle">Categoria contábil para agrupar contas.</div>
                </div>
            </div>
            <div class="member-form-card-body">
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
                    <button type="submit" class="btn btn-dark rounded-pill fw-semibold w-100">
                        <i class="fas fa-plus me-1"></i> Adicionar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="member-form-card">
            <div class="member-form-card-header">
                <div class="member-form-badge"><i class="fas fa-tags"></i></div>
                <div>
                    <div class="member-form-card-title">Editar ou Remover</div>
                    <div class="member-form-card-subtitle">Naturezas contábeis cadastradas no sistema.</div>
                </div>
            </div>
            <div class="member-form-card-body">
                <?php if (empty($natures)): ?>
                    <div class="text-muted text-center py-3">Nenhuma natureza cadastrada.</div>
                <?php else: ?>
                    <div class="vstack gap-3">
                        <?php foreach ($natures as $nature): ?>
                            <form method="POST" action="/admin/financial/chart-account-natures/update/<?= $nature['id'] ?>" class="nature-row btn-delete-nature-form" data-name="<?= htmlspecialchars($nature['name']) ?>">
                                <?= csrf_field() ?>
                                <div class="small text-muted mb-2"><i class="far fa-clock me-1"></i>Criado em <?= !empty($nature['created_at']) ? date('d/m/Y H:i', strtotime($nature['created_at'])) : '—' ?></div>
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
                                        <button type="submit" class="btn btn-outline-primary rounded-pill fw-semibold w-100">
                                            <i class="fas fa-save me-1"></i> Salvar
                                        </button>
                                        <button type="button"
                                                class="btn btn-outline-danger icon-btn btn-delete-nature"
                                                data-formaction="/admin/financial/chart-account-natures/delete/<?= $nature['id'] ?>"
                                                style="width: 38px; flex: 0 0 auto; border-radius: 50%;">
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

<script>
document.querySelectorAll('.btn-delete-nature').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const form = btn.closest('form');
        const name = form.getAttribute('data-name');
        const formaction = btn.getAttribute('data-formaction');
        Swal.fire({
            title: 'Remover natureza?',
            text: `Tem certeza que deseja remover "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, remover',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.setAttribute('action', formaction);
                form.submit();
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
