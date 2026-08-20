<?php include __DIR__ . '/layout_developer.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Página de Demonstração</h1>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm mb-4 border-warning">
            <div class="card-header bg-warning-subtle">
                <h5 class="mb-0"><i class="fas fa-flask me-2"></i> Página de Demonstração (Produto/Vendas)</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Quando ativa, substitui a página inicial pública (<code>/</code>) por uma tela de demonstração do produto, com credenciais de acesso que se renovam automaticamente a cada 2 dias. Use apenas em instâncias de demonstração/vendas.</p>
                <form action="/developer/settings/demo-landing" method="POST">
                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" name="demo_landing_enabled" id="demoLandingEnabled" class="form-check-input" value="1" <?= !empty($demoLandingConfig['enabled']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="demoLandingEnabled">Ativar página de demonstração nesta instância</label>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Link do site público (abre em nova aba)</label>
                            <input type="text" name="demo_public_url" class="form-control" value="<?= htmlspecialchars($demoLandingConfig['public_url'] ?? '') ?>" placeholder="https://igrejabr.com.br">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Usuário demo — Administrador</label>
                            <input type="text" name="demo_admin_username" class="form-control" value="<?= htmlspecialchars($demoLandingConfig['admin_username'] ?? '') ?>" placeholder="usuário já existente no sistema">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Usuário demo — Secretaria</label>
                            <input type="text" name="demo_secretary_username" class="form-control" value="<?= htmlspecialchars($demoLandingConfig['secretary_username'] ?? '') ?>" placeholder="usuário já existente no sistema">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Usuário demo — Membro</label>
                            <input type="text" name="demo_member_username" class="form-control" value="<?= htmlspecialchars($demoLandingConfig['member_username'] ?? '') ?>" placeholder="usuário já existente no sistema">
                        </div>
                    </div>
                    <div class="form-text mb-3">Cada campo deve ser o <strong>usuário</strong> (username) de uma conta já cadastrada em <a href="/admin/users" target="_blank">Usuários do Sistema</a>. Deixe em branco pra não mostrar aquele cartão. A senha dessas contas passa a ser trocada automaticamente a cada 2 dias — não use contas reais de administradores.</div>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i> Salvar Configuração de Demonstração
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
