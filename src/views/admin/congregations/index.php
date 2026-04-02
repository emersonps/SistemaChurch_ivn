<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Congregações</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/congregations/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Nova Congregação
        </a>
    </div>
</div>

<?php if (isset($_GET['error']) && $_GET['error'] == 'has_members'): ?>
    <div class="alert alert-danger">
        Não é possível excluir esta congregação pois existem membros vinculados a ela.
    </div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Dirigente</th>
                <th>Telefone</th>
                <th>Data Abertura</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($congregations as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= htmlspecialchars($c['leader_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($c['phone'] ?? 'N/A') ?></td>
                    <td><?= $c['opening_date'] ? date('d/m/Y', strtotime($c['opening_date'])) : 'N/A' ?></td>
                    <td>
                        <a href="/admin/members?congregation_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-info" title="Ver Membros">
                            <i class="fas fa-users"></i>
                        </a>
                        <a href="/admin/congregations/edit/<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="/admin/congregations/delete/<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja excluir?')" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
