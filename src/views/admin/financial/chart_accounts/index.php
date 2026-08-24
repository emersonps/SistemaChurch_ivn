<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Plano de Contas</h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="/admin/financial/account-sets" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">Gerenciar Planos</a>
        <a href="/admin/financial/chart-accounts/create?set=<?= (int)($selectedSet ?? 0) ?>" class="btn btn-sm btn-primary rounded-pill fw-semibold px-3">
            <i class="fas fa-plus me-1"></i> Nova Conta Contábil
        </a>
        <a href="/admin/financial/chart-accounts/import" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold px-3">
            <i class="fas fa-file-import me-1"></i> Importar Planilha
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
    .accounts-table thead th {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .accounts-table td {
        vertical-align: middle;
        padding-top: .6rem;
        padding-bottom: .6rem;
    }
    .accounts-table tr.is-main td { background: #fafafa; font-weight: 700; }
    .type-pill {
        display: inline-block;
        padding: .2rem .6rem;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 700;
    }
    .type-pill.type-asset { background: rgba(13,110,253,0.10); color: #0d6efd; }
    .type-pill.type-liability { background: rgba(220,53,69,0.10); color: #dc3545; }
    .type-pill.type-income { background: rgba(25,135,84,0.10); color: #198754; }
    .type-pill.type-expense { background: rgba(253,126,20,0.12); color: #b8590a; }
    .structure-pill {
        display: inline-block;
        padding: .2rem .6rem;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 700;
    }
    .structure-pill.structure-synthetic { background: rgba(13,202,240,0.14); color: #087990; }
    .structure-pill.structure-analytic { background: #eef0f2; color: #495057; }
    .status-pill {
        display: inline-block;
        padding: .2rem .6rem;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 700;
    }
    .status-pill.status-active { background: rgba(25,135,84,0.10); color: #198754; }
    .status-pill.status-inactive { background: #eef0f2; color: #6c757d; }
    .icon-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        padding: 0;
    }
    .set-select-card .form-select {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
    }
    .set-select-card .form-select:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
</style>

<div class="member-form-card set-select-card mb-3">
    <div class="p-3">
        <form method="GET" action="/admin/financial/chart-accounts">
            <label class="form-label fw-semibold mb-1">Plano de Contas</label>
            <select name="set" class="form-select" onchange="this.form.submit()">
                <?php foreach (($sets ?? []) as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= ($selectedSet ?? 0) == $s['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['name']) ?> <?= (int)$s['is_default'] === 1 ? '(Padrão)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= $_SESSION['flash_success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php
$typeLabels = ['asset' => 'Ativo', 'liability' => 'Passivo', 'income' => 'Receita', 'expense' => 'Despesa'];
$typeIcons = ['asset' => 'fa-arrow-up', 'liability' => 'fa-arrow-down', 'income' => 'fa-plus-circle', 'expense' => 'fa-minus-circle'];
?>

<div class="member-form-card">
    <div class="table-responsive p-2">
        <table class="table table-hover accounts-table" style="width:100%">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nome da Conta</th>
                    <th>Natureza</th>
                    <th>Estrutura</th>
                    <th>Status</th>
                    <th>Criado em</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($accounts)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">Nenhuma conta contábil cadastrada.</td></tr>
                <?php else: ?>
                    <?php foreach ($accounts as $acc):
                        $level = substr_count($acc['code'], '.');
                        $padding = $level * 20;
                        $isMain = $level === 0;
                    ?>
                        <tr class="<?= $isMain ? 'is-main' : '' ?>">
                            <td style="padding-left: <?= $padding + 12 ?>px;"><?= htmlspecialchars($acc['code']) ?></td>
                            <td><?= htmlspecialchars($acc['name']) ?></td>
                            <td>
                                <?php if (!empty($acc['nature_name'])): ?>
                                    <?= htmlspecialchars($acc['nature_name']) ?>
                                    <div><span class="type-pill type-<?= $acc['type'] ?>"><i class="fas <?= $typeIcons[$acc['type']] ?? '' ?> me-1"></i><?= $typeLabels[$acc['type']] ?? $acc['type'] ?></span></div>
                                <?php else: ?>
                                    <span class="type-pill type-<?= $acc['type'] ?>"><i class="fas <?= $typeIcons[$acc['type']] ?? '' ?> me-1"></i><?= $typeLabels[$acc['type']] ?? $acc['type'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (empty($acc['parent_id'])): ?>
                                    <span class="structure-pill structure-synthetic">Sintética (Pai)</span>
                                <?php else: ?>
                                    <span class="structure-pill structure-analytic">Analítica (Filho)</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="status-pill status-<?= $acc['status'] ?>"><?= $acc['status'] === 'active' ? 'Ativa' : 'Inativa' ?></span></td>
                            <td class="small text-muted"><?= !empty($acc['created_at']) ? date('d/m/Y H:i', strtotime($acc['created_at'])) : '—' ?></td>
                            <td class="text-end">
                                <a href="/admin/financial/chart-accounts/edit/<?= $acc['id'] ?>" class="btn btn-sm btn-outline-primary icon-btn" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger icon-btn btn-delete-account"
                                        data-id="<?= $acc['id'] ?>"
                                        data-children="<?= (int)($acc['children_count'] ?? 0) ?>"
                                        data-structure="<?= empty($acc['parent_id']) ? 'synthetic' : 'analytic' ?>"
                                        title="Excluir">
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

<?php include __DIR__ . '/../../../layout/footer.php'; ?>

<script>
document.querySelectorAll('.btn-delete-account').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const children = parseInt(this.getAttribute('data-children') || '0', 10);
        const structure = this.getAttribute('data-structure') || 'analytic';
        let text = '';
        if (structure === 'synthetic') {
            if (children > 0) {
                text = `Esta conta é Sintética (Pai) e possui ${children} subconta(s). Ao confirmar, todas as contas filhas serão excluídas. Deseja continuar?`;
            } else {
                text = `Esta conta é Sintética (Pai) e não possui subcontas. Deseja excluir esta conta?`;
            }
        } else {
            text = `Excluir esta conta Analítica (Filho)?`;
        }
        Swal.fire({
            title: 'Excluir Conta Contábil?',
            text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `/admin/financial/chart-accounts/delete/${id}`;
            }
        });
    });
});
</script>
