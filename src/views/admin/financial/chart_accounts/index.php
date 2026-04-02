<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Plano de Contas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/financial/account-sets" class="btn btn-sm btn-outline-secondary me-2">Gerenciar Planos</a>
        <a href="/admin/financial/chart-accounts/create?set=<?= (int)($selectedSet ?? 0) ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Nova Conta Contábil
        </a>
        <a href="/admin/financial/chart-accounts/import" class="btn btn-sm btn-outline-primary ms-2">
            <i class="fas fa-file-import"></i> Importar Planilha
        </a>
    </div>
</div>

<div class="mb-3">
    <form method="GET" action="/admin/financial/chart-accounts" class="row g-2">
        <div class="col-md-4">
            <label class="form-label">Plano de Contas</label>
            <select name="set" class="form-select" onchange="this.form.submit()">
                <?php foreach (($sets ?? []) as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= ($selectedSet ?? 0) == $s['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['name']) ?> <?= (int)$s['is_default'] === 1 ? '(Padrão)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['flash_success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Nome da Conta</th>
                        <th>Natureza</th>
                        <th>Estrutura</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $acc): 
                        // Visual indentation based on dot count in code
                        $level = substr_count($acc['code'], '.');
                        $padding = $level * 20;
                        $isMain = $level === 0;
                    ?>
                        <tr class="<?= $isMain ? 'table-secondary fw-bold' : '' ?>">
                            <td style="padding-left: <?= $padding + 10 ?>px;">
                                <?= htmlspecialchars($acc['code']) ?>
                            </td>
                            <td><?= htmlspecialchars($acc['name']) ?></td>
                            <td>
                                <?php 
                                    $types = [
                                        'asset' => '<span class="text-primary"><i class="fas fa-arrow-up"></i> Ativo</span>',
                                        'liability' => '<span class="text-danger"><i class="fas fa-arrow-down"></i> Passivo</span>',
                                        'income' => '<span class="text-success"><i class="fas fa-plus-circle"></i> Receita</span>',
                                        'expense' => '<span class="text-warning text-dark"><i class="fas fa-minus-circle"></i> Despesa</span>'
                                    ];
                                    if (!empty($acc['nature_name'])) {
                                        echo htmlspecialchars($acc['nature_name']);
                                        echo '<div><small class="text-muted">' . ($types[$acc['type']] ?? htmlspecialchars($acc['type'])) . '</small></div>';
                                    } else {
                                        echo $types[$acc['type']] ?? $acc['type'];
                                    }
                                ?>
                            </td>
                            <td>
                                <?php if (empty($acc['parent_id'])): ?>
                                    <span class="badge bg-info text-dark">Sintética (Pai)</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Analítica (Filho)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($acc['status'] === 'active'): ?>
                                    <span class="badge bg-success">Ativa</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inativa</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="/admin/financial/chart-accounts/edit/<?= $acc['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger btn-delete-account" 
                                            data-id="<?= $acc['id'] ?>" 
                                            data-children="<?= (int)($acc['children_count'] ?? 0) ?>"
                                            data-structure="<?= empty($acc['parent_id']) ? 'synthetic' : 'analytic' ?>"
                                            title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($accounts)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">Nenhuma conta contábil cadastrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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
