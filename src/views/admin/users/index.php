<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gerenciar Usuários</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/users/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Novo Usuário
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Função (Role)</th>
                <th>Criado em</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td>
                        <?php 
                            $roleLabel = $u['role'];
                            $rbac = require __DIR__ . '/../../../../config/rbac.php';
                            if (isset($rbac['roles'][$u['role']])) {
                                $roleLabel = $rbac['roles'][$u['role']]['label'];
                            }
                            echo htmlspecialchars($roleLabel);
                        ?>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
                    <td>
                        <a href="/admin/users/edit/<?= $u['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php if ($u['id'] != $_SESSION['user_id'] && $u['role'] !== 'developer'): ?>
                        <a href="/admin/users/delete/<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                            <i class="fas fa-trash"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
