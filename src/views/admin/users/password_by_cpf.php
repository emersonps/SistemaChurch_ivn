<?php $isDeveloperView = (($_SESSION['user_role'] ?? '') === 'developer'); ?>
<?php include $isDeveloperView ? __DIR__ . '/../../developer/layout_developer.php' : __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Redefinir Senha por CPF</h1>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    <?= $_SESSION['flash_success'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <?= $_SESSION['flash_error'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Alterar Senha</div>
            <div class="card-body">
                <form action="<?= $actionUrl ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">CPF</label>
                        <input type="text" class="form-control" name="cpf" value="<?= htmlspecialchars($cpf ?? '') ?>" placeholder="000.000.000-00" required>
                        <small class="text-muted">O sistema procura o membro pelo CPF e redefine a senha do portal. Se houver usuário(s) do sistema vinculado(s), a mesma senha também será aplicada.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nova Senha</label>
                        <input type="password" class="form-control" name="new_password" minlength="6" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar Nova Senha</label>
                        <input type="password" class="form-control" name="confirm_password" minlength="6" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key me-1"></i> Redefinir Senha
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Dados Encontrados</div>
            <div class="card-body">
                <?php if (empty($cpf)): ?>
                    <div class="text-muted">Informe um CPF para localizar o cadastro.</div>
                <?php elseif (empty($member)): ?>
                    <div class="text-danger">Nenhum cadastro encontrado para o CPF informado.</div>
                <?php else: ?>
                    <div class="mb-2"><strong>Membro:</strong> <?= htmlspecialchars($member['name']) ?></div>
                    <div class="mb-2"><strong>CPF:</strong> <?= htmlspecialchars($member['cpf']) ?></div>
                    <div class="mb-3"><strong>Senha do Portal:</strong> <?= empty($member['password']) ? 'Ainda não cadastrada' : 'Já cadastrada' ?></div>

                    <div class="border-top pt-3">
                        <div class="fw-semibold mb-2">Usuários do Sistema Vinculados</div>
                        <?php if (empty($linkedUsers)): ?>
                            <div class="text-muted">Nenhum usuário do sistema vinculado a este CPF.</div>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($linkedUsers as $user): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><?= htmlspecialchars($user['username']) ?></span>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($user['role']) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include $isDeveloperView ? __DIR__ . '/../../developer/layout_footer.php' : __DIR__ . '/../../layout/footer.php'; ?>
