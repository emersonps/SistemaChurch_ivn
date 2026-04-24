<?php include __DIR__ . '/layout_developer.php'; ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h2 mb-1">Sincronização da Central</h1>
        <p class="text-muted mb-0">Conecte esta instalação à central e escolha quais módulos compartilhados devem atualizar os dados locais.</p>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="row g-4">
    <div class="col-xl-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Configuração da Central</div>
            <div class="card-body">
                <form action="/developer/manual-sync/save" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">URL da Central</label>
                        <input type="url" name="central_url" class="form-control" value="<?= htmlspecialchars($config['central_url'] ?? '') ?>" placeholder="http://localhost:8080" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Código da Instância</label>
                        <input type="text" name="instance_code" class="form-control" value="<?= htmlspecialchars($config['instance_code'] ?? '') ?>" placeholder="sistemachurch_ivn" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Token da Instância</label>
                        <input type="text" name="token" class="form-control" value="<?= htmlspecialchars($config['token'] ?? '') ?>" autocomplete="off" required>
                    </div>

                    <div class="border rounded p-3 mb-3">
                        <div class="fw-semibold mb-2">Módulos que esta instalação deve receber da central</div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="manual_sync_enabled" name="manual_sync_enabled" value="1" <?= !empty($config['enabled']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="manual_sync_enabled">Manuais em vídeo</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="global_settings_sync_enabled" name="global_settings_sync_enabled" value="1" <?= !empty($globalSettingsConfig['enabled']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="global_settings_sync_enabled">Configurações globais do site</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Salvar Configuração
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">Status Geral</div>
            <div class="card-body">
                <div class="mb-2"><strong>Manuais via central:</strong> <?= !empty($config['enabled']) ? 'Ativado' : 'Desativado' ?></div>
                <div class="mb-2"><strong>Configurações globais via central:</strong> <?= !empty($globalSettingsConfig['enabled']) ? 'Ativado' : 'Desativado' ?></div>
                <div class="mb-2"><strong>Vídeos locais:</strong> <?= (int)$localVideoCount ?></div>
                <div class="mb-2"><strong>Temas locais:</strong> <?= (int)$localThemeCount ?></div>
                <div class="mb-0"><strong>Telefone atual do site:</strong> <?= htmlspecialchars($siteProfile['phone'] ?? '') ?></div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">Conectividade com a Central</div>
            <div class="card-body">
                <?php if (empty($config['central_url']) || empty($config['instance_code']) || empty($config['token'])): ?>
                    <div class="text-muted">Preencha URL, código da instância e token para validar a conexão.</div>
                <?php elseif (!empty($manualRemoteStatus['error'])): ?>
                    <div class="alert alert-danger mb-0"><?= htmlspecialchars($manualRemoteStatus['error']) ?></div>
                <?php elseif (!empty($manualRemoteStatus)): ?>
                    <div class="mb-2"><strong>Ping:</strong> OK</div>
                    <div class="mb-0"><strong>Instância validada:</strong> <?= htmlspecialchars($manualRemoteStatus['ping']['instance'] ?? '') ?></div>
                <?php else: ?>
                    <div class="text-muted">Salve a configuração para consultar a central.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Manuais em Vídeo</span>
                <?php if (!empty($config['enabled'])): ?>
                    <form action="/developer/manual-sync/run" method="POST">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-rotate me-1"></i> Sincronizar
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="mb-2"><strong>Status local:</strong> <?= !empty($config['enabled']) ? 'Ativo' : 'Desativado' ?></div>
                <div class="mb-2"><strong>Última sincronização:</strong> <?= !empty($config['last_sync_at']) ? htmlspecialchars($config['last_sync_at']) : 'Ainda não realizada' ?></div>
                <div class="mb-2"><strong>Última versão importada:</strong> <?= !empty($config['last_sync_version']) ? htmlspecialchars($config['last_sync_version']) : 'Nenhuma' ?></div>
                <div class="mb-3"><strong>Checksum local:</strong> <?= !empty($config['last_sync_checksum']) ? htmlspecialchars($config['last_sync_checksum']) : 'Nenhum' ?></div>
                <?php if (!empty($manualRemoteStatus['version'])): ?>
                    <div class="border-top pt-3">
                        <div class="mb-2"><strong>Versão remota:</strong> <?= htmlspecialchars((string)($manualRemoteStatus['version']['version'] ?? '')) ?></div>
                        <div class="mb-2"><strong>Publicado em:</strong> <?= htmlspecialchars($manualRemoteStatus['version']['published_at'] ?? '') ?></div>
                        <div class="mb-0"><strong>Checksum remoto:</strong> <?= htmlspecialchars($manualRemoteStatus['version']['checksum'] ?? '') ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Configurações Globais</span>
                <?php if (!empty($globalSettingsConfig['enabled'])): ?>
                    <form action="/developer/manual-sync/global-settings" method="POST">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-rotate me-1"></i> Sincronizar
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="mb-2"><strong>Status local:</strong> <?= !empty($globalSettingsConfig['enabled']) ? 'Ativo' : 'Desativado' ?></div>
                <div class="mb-2"><strong>Última sincronização:</strong> <?= !empty($globalSettingsConfig['last_sync_at']) ? htmlspecialchars($globalSettingsConfig['last_sync_at']) : 'Ainda não realizada' ?></div>
                <div class="mb-2"><strong>Última versão importada:</strong> <?= !empty($globalSettingsConfig['last_sync_version']) ? htmlspecialchars($globalSettingsConfig['last_sync_version']) : 'Nenhuma' ?></div>
                <div class="mb-3"><strong>Checksum local:</strong> <?= !empty($globalSettingsConfig['last_sync_checksum']) ? htmlspecialchars($globalSettingsConfig['last_sync_checksum']) : 'Nenhum' ?></div>
                <?php if (!empty($globalSettingsRemoteStatus['error'])): ?>
                    <div class="alert alert-danger mb-0"><?= htmlspecialchars($globalSettingsRemoteStatus['error']) ?></div>
                <?php elseif (!empty($globalSettingsRemoteStatus)): ?>
                    <div class="border-top pt-3">
                        <div class="mb-2"><strong>Versão remota:</strong> <?= htmlspecialchars((string)($globalSettingsRemoteStatus['version'] ?? '')) ?></div>
                        <div class="mb-2"><strong>Publicado em:</strong> <?= htmlspecialchars($globalSettingsRemoteStatus['published_at'] ?? '') ?></div>
                        <div class="mb-0"><strong>Checksum remoto:</strong> <?= htmlspecialchars($globalSettingsRemoteStatus['checksum'] ?? '') ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-white">Como funciona</div>
    <div class="card-body">
        <ul class="mb-0">
            <li>Manuais sincronizados substituem os vídeos locais pela última versão publicada na central.</li>
            <li>Configurações globais sincronizadas atualizam telefone, e-mail, texto "Quem Somos" e redes sociais do site.</li>
            <li>Se quiser editar um módulo localmente novamente, desative a sincronização desse módulo antes de alterar os dados.</li>
        </ul>
    </div>
</div>

<?php include __DIR__ . '/layout_footer.php'; ?>
