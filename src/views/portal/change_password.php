<?php include __DIR__ . '/layout/header.php'; ?>

<div class="portal-page-title">Alterar Minha Senha</div>
<p class="text-muted mb-3">Atualize sua senha de acesso ao portal.</p>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="portal-card">
            <div class="portal-card-header">
                <div class="portal-card-title"><i class="fas fa-key text-primary me-2"></i> Segurança da Conta</div>
            </div>
            <div class="p-4">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger rounded-3"><i class="fas fa-exclamation-triangle me-2"></i><?= $error ?></div>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success rounded-3"><i class="fas fa-check-circle me-2"></i><?= $success ?></div>
                <?php endif; ?>

                <form action="/portal/change-password" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Senha Atual</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nova Senha</label>
                        <input type="password" name="new_password" class="form-control" minlength="6" required>
                        <div class="form-text">Use pelo menos 6 caracteres.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Confirmar Nova Senha</label>
                        <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                    </div>
                    <button type="submit" class="btn btn-dark rounded-pill fw-semibold w-100 py-2">
                        <i class="fas fa-save me-2"></i> Alterar Senha
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
