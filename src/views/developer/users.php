<?php include __DIR__ . '/layout_developer.php'; ?>

<h1 class="h2 mb-4">Gerenciar Papéis (Roles) - Dev</h1>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Ação realizada com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        Erro ao realizar ação. Verifique os dados.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users-cog text-primary me-2"></i> Lista de Papéis do Sistema</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Papel (Role)</th>
                                <th>Descrição/Nome</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $rbac = require __DIR__ . '/../../../config/rbac.php';
                            foreach ($rbac['roles'] as $roleKey => $roleData): 
                            ?>
                            <tr>
                                <td><span class="badge bg-primary"><?= $roleKey ?></span></td>
                                <td><strong><?= htmlspecialchars($roleData['label']) ?></strong></td>
                                <td>
                                    <a href="/developer/roles/edit/<?= $roleKey ?>" class="btn btn-sm btn-info text-white me-1">
                                        <i class="fas fa-user-shield"></i> Editar Permissões do Papel
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Carregar usuários para alteração de senha
try {
    $db = (new Database())->connect();
    $users = $db->query("SELECT id, username, role FROM users ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $users = [];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-user-lock text-primary me-2"></i> Usuários do Sistema</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Usuário</th>
                                <th>Perfil</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?= htmlspecialchars($u['username']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($u['role']) ?></span></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-warning btn-change-pass" 
                                                data-id="<?= $u['id'] ?>" 
                                                data-username="<?= htmlspecialchars($u['username']) ?>">
                                            <i class="fas fa-key me-1"></i> Alterar Senha
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($users)): ?>
                                <tr><td colspan="3" class="text-center text-muted">Nenhum usuário encontrado.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Alterar Senha -->
<div class="modal fade" id="changePassModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-key me-2"></i> Alterar Senha</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="/developer/users/password">
        <div class="modal-body">
            <input type="hidden" name="user_id" id="changePassUserId">
            <div class="mb-3">
                <label class="form-label">Usuário</label>
                <input type="text" class="form-control" id="changePassUsername" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Nova Senha</label>
                <input type="password" class="form-control" name="new_password" required minlength="6">
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
      </form>
    </div>
  </div>
 </div>

<script>
document.querySelectorAll('.btn-change-pass').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const username = this.getAttribute('data-username');
        document.getElementById('changePassUserId').value = id;
        document.getElementById('changePassUsername').value = username;
        const modal = new bootstrap.Modal(document.getElementById('changePassModal'));
        modal.show();
    });
});
</script>

<?php include __DIR__ . '/layout_footer.php'; ?>
