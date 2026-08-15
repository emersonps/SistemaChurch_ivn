<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Planos de Contas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/financial/chart-accounts" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
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
    .sets-table thead th {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .sets-table td { vertical-align: middle; }
    .scope-pill, .status-pill, .default-pill {
        display: inline-block;
        padding: .2rem .6rem;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 700;
    }
    .scope-pill.scope-cong { background: rgba(13,110,253,0.10); color: #0d6efd; }
    .scope-pill.scope-general { background: #eef0f2; color: #495057; }
    .status-pill.status-active { background: rgba(25,135,84,0.10); color: #198754; }
    .status-pill.status-inactive { background: #eef0f2; color: #6c757d; }
    .default-pill { background: rgba(13,202,240,0.14); color: #087990; }
    .icon-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        padding: 0;
    }
</style>

<div class="row">
    <div class="col-lg-5">
        <div class="member-form-card mb-3">
            <div class="member-form-card-header">
                <div class="member-form-badge"><i class="fas fa-plus"></i></div>
                <div>
                    <div class="member-form-card-title">Criar Novo Plano</div>
                    <div class="member-form-card-subtitle">Um novo conjunto de contas contábeis.</div>
                </div>
            </div>
            <div class="member-form-card-body">
                <form method="POST" action="/admin/financial/account-sets/store">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Nome <span class="text-danger">*</span></label>
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
                    <button type="submit" class="btn btn-dark rounded-pill fw-semibold w-100"><i class="fas fa-save me-1"></i> Criar</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="member-form-card">
            <div class="member-form-card-header">
                <div class="member-form-badge"><i class="fas fa-list"></i></div>
                <div>
                    <div class="member-form-card-title">Conjuntos Existentes</div>
                    <div class="member-form-card-subtitle">Editar, ativar/desativar ou excluir planos de contas.</div>
                </div>
            </div>
            <div class="table-responsive p-2">
                <table class="table table-hover sets-table mb-0" style="width:100%">
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
                        <?php if (empty($sets)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">Nenhum Plano cadastrado.</td></tr>
                        <?php else: ?>
                            <?php foreach (($sets ?? []) as $s): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($s['name']) ?></td>
                                    <td>
                                        <?php if (!empty($s['congregation_id'])): ?>
                                            <span class="scope-pill scope-cong">Congregação</span>
                                            <div class="small text-muted"><?= htmlspecialchars($s['congregation_name'] ?? '') ?></div>
                                        <?php else: ?>
                                            <span class="scope-pill scope-general">Geral</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="status-pill status-<?= $s['active'] ? 'active' : 'inactive' ?>"><?= $s['active'] ? 'Ativo' : 'Inativo' ?></span></td>
                                    <td><?php if ($s['is_default']): ?><span class="default-pill">Padrão</span><?php endif; ?></td>
                                    <td class="text-end">
                                        <a href="/admin/financial/account-sets/edit/<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary icon-btn" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if (!$s['is_default']): ?>
                                            <a href="/admin/financial/account-sets/make-default/<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary icon-btn" title="Tornar padrão">
                                                <i class="fas fa-star"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="/admin/financial/account-sets/toggle/<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary icon-btn" title="<?= $s['active'] ? 'Desativar' : 'Ativar' ?>">
                                            <i class="fas <?= $s['active'] ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger icon-btn btn-delete-set" data-id="<?= $s['id'] ?>" data-name="<?= htmlspecialchars($s['name']) ?>" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
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

document.querySelectorAll('.btn-delete-set').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const id = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name');
        Swal.fire({
            title: 'Excluir Plano?',
            text: `Tem certeza que deseja excluir "${name}"? Apenas conjuntos sem contas podem ser excluídos.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `/admin/financial/account-sets/delete/${id}`;
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
