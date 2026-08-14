<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Congregações</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/congregations/create" class="btn btn-sm btn-primary rounded-pill fw-semibold px-3">
            <i class="fas fa-plus me-1"></i> Nova Congregação
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
    .congregations-table thead th {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .congregations-table td {
        vertical-align: middle;
        padding-top: .65rem;
        padding-bottom: .65rem;
    }
    .hq-pill {
        display: inline-block;
        padding: .2rem .55rem;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 700;
        background: rgba(179,0,0,0.10);
        color: #b30000;
        margin-left: .4rem;
    }
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

<?php if (isset($_GET['error']) && $_GET['error'] == 'has_members'): ?>
    <div class="alert alert-danger">
        Não é possível excluir esta congregação pois existem membros vinculados a ela.
    </div>
<?php endif; ?>

<div class="member-form-card">
    <div class="table-responsive p-2">
        <table class="table table-hover congregations-table" style="width:100%">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Dirigente</th>
                    <th>Telefone</th>
                    <th>Data Abertura</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($congregations as $c): ?>
                    <tr>
                        <?php $type = strtolower((string)($c['type'] ?? '')); $isHq = in_array($type, ['headquarters', 'sede', 'matriz', 'principal'], true); ?>
                        <td class="fw-bold">
                            <?= htmlspecialchars($c['name']) ?>
                            <?php if ($isHq): ?>
                                <span class="hq-pill">Principal</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($c['leader_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($c['phone'] ?? 'N/A') ?></td>
                        <td><?= $c['opening_date'] ? date('d/m/Y', strtotime($c['opening_date'])) : 'N/A' ?></td>
                        <td class="text-end">
                            <a href="/admin/members?congregation_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary icon-btn" title="Ver Membros">
                                <i class="fas fa-users"></i>
                            </a>
                            <a href="/admin/congregations/edit/<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary icon-btn" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="/admin/congregations/delete/<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger icon-btn btn-delete-congregation" data-name="<?= htmlspecialchars($c['name']) ?>" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.querySelectorAll('.btn-delete-congregation').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const href = btn.getAttribute('href');
        const name = btn.getAttribute('data-name');
        Swal.fire({
            title: 'Excluir congregação?',
            text: `Tem certeza que deseja excluir "${name}"? Esta ação não pode ser desfeita.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
