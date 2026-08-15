<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2">Alterar Senha</h1>
</div>

<style>
    .password-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
    }
    .password-card-header {
        display: flex;
        align-items: flex-start;
        gap: .85rem;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.07);
        background: #fafafa;
    }
    .password-card-badge {
        flex: 0 0 auto;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(179,0,0,0.10);
        color: #b30000;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }
    .password-card-title {
        font-weight: 800;
        font-size: 1.05rem;
        color: #1a1a1a;
        line-height: 1.2;
    }
    .password-card-subtitle {
        font-size: .82rem;
        color: #868e96;
        margin-top: .1rem;
    }
    .password-card-body { padding: 1.25rem; }
    .password-card-body .form-label {
        font-weight: 600;
        font-size: .88rem;
        color: #343a40;
    }
    .password-card-body .form-control {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
        padding: .55rem .8rem;
    }
    .password-card-body .form-control:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
    .password-strength-hint {
        font-size: .78rem;
        color: #868e96;
    }
</style>

<div class="row justify-content-center">
    <div class="col-lg-6 col-xl-5">
        <div class="password-card">
            <div class="password-card-header">
                <div class="password-card-badge"><i class="fas fa-key"></i></div>
                <div>
                    <div class="password-card-title">Segurança da Conta</div>
                    <div class="password-card-subtitle">Atualize sua senha de acesso ao painel administrativo.</div>
                </div>
            </div>
            <div class="password-card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="/admin/change-password" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Senha Atual <span class="text-danger">*</span></label>
                        <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nova Senha <span class="text-danger">*</span></label>
                        <input type="password" name="new_password" class="form-control" required autocomplete="new-password" minlength="6">
                        <div class="password-strength-hint mt-1">Use pelo menos 6 caracteres.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirmar Nova Senha <span class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" class="form-control" required autocomplete="new-password" minlength="6">
                    </div>
                    <button type="submit" class="btn btn-dark rounded-pill fw-semibold w-100 py-2">
                        <i class="fas fa-save me-2"></i> Alterar Senha
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
