<?php include __DIR__ . '/layout_developer.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Ajustes do Site (White Label)</h1>
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

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-paint-roller me-2"></i> Identidade Visual</h5>
            </div>
            <div class="card-body">
                <form action="/developer/settings" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    
                    <div class="mb-4 text-center">
                        <label class="form-label d-block fw-bold">Logo Atual</label>
                        <div class="p-3 bg-light border rounded d-inline-block">
                            <img src="/assets/img/logo.png?v=<?= time() ?>" alt="Logo Atual" style="max-height: 100px; max-width: 200px; object-fit: contain;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nova Logo (Opcional)</label>
                        <input type="file" name="logo" class="form-control" accept="image/png">
                        <div class="form-text">Envie um arquivo PNG com fundo transparente. Ele substituirá a logo atual em todo o sistema.</div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <?php
                    // Tentar inferir o nome atual do header
                    $currentAlias = 'IVN';
                    $currentName = 'Igreja Vida Nova';
                    $headerPath = __DIR__ . '/../layout/header.php';
                    if (file_exists($headerPath)) {
                        $content = file_get_contents($headerPath);
                        if (preg_match('/<title><\?= \$seo_title \?\? \'(.*?)\' \?><\/title>/', $content, $matches)) {
                            $parts = explode(' - ', $matches[1]);
                            if (count($parts) >= 2) {
                                $currentAlias = trim($parts[0]);
                                $currentName = trim($parts[1]);
                            }
                        }
                    }
                    ?>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Sigla da Igreja</label>
                        <input type="text" name="church_alias" class="form-control text-uppercase" value="<?= htmlspecialchars($currentAlias) ?>" required>
                        <div class="form-text">Ex: IVN, ADMP, IEJ. Usado em menus, títulos curtos e carteirinha.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Nome Completo da Igreja</label>
                        <input type="text" name="church_name" class="form-control" value="<?= htmlspecialchars($currentName) ?>" required>
                        <div class="form-text">Ex: Igreja Vida Nova. Usado em cabeçalhos, rodapés e documentos oficiais.</div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Telefone da Igreja</label>
                            <input type="text" name="church_phone" class="form-control" value="<?= htmlspecialchars($siteProfile['phone'] ?? '') ?>" placeholder="+55 (00) 00000-0000">
                            <div class="form-text">Usado no rodapé e nos pontos públicos de contato.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">E-mail da Igreja</label>
                            <input type="email" name="church_email" class="form-control" value="<?= htmlspecialchars($siteProfile['email'] ?? '') ?>" placeholder="contato@suaigreja.com.br">
                            <div class="form-text">Usado no rodapé e nos contatos públicos do site.</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Texto "Quem Somos"</label>
                        <textarea name="church_about_text" class="form-control" rows="6" placeholder="Descreva a história, missão e identidade da igreja."><?= htmlspecialchars($siteProfile['about_text'] ?? '') ?></textarea>
                        <div class="form-text">Este texto aparece na seção "Quem Somos" do site público.</div>
                    </div>

                    <?php
                    $socialFormRows = $siteProfile['social_links'] ?? [];
                    if (empty($socialFormRows)) {
                        $socialFormRows = [
                            ['platform' => 'facebook', 'url' => ''],
                            ['platform' => 'instagram', 'url' => ''],
                            ['platform' => 'youtube', 'url' => ''],
                        ];
                    }
                    ?>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold mb-0">Redes Sociais</label>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-social-link">
                                <i class="fas fa-plus me-1"></i> Adicionar Rede
                            </button>
                        </div>
                        <div id="social-links-wrapper" class="d-grid gap-2">
                            <?php foreach ($socialFormRows as $social): ?>
                                <div class="row g-2 align-items-center social-link-row">
                                    <div class="col-md-4">
                                        <select name="social_platform[]" class="form-select">
                                            <?php foreach ($socialIconOptions as $optionKey => $option): ?>
                                                <option value="<?= htmlspecialchars($optionKey) ?>" <?= ($social['platform'] ?? '') === $optionKey ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($option['label']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-7">
                                        <input type="url" name="social_url[]" class="form-control" value="<?= htmlspecialchars($social['url'] ?? '') ?>" placeholder="https://...">
                                    </div>
                                    <div class="col-md-1 d-grid">
                                        <button type="button" class="btn btn-outline-danger remove-social-link">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-text">Escolha os ícones e links que devem aparecer no rodapé do site.</div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Atenção:</strong> Ao salvar, o sistema fará uma substituição em lote nos arquivos de código para refletir o novo nome e sigla em 100% das telas (incluindo painel do membro, relatórios e portal público). Esta operação pode levar alguns segundos.
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Tem certeza que deseja alterar a identidade visual do sistema? Isso modificará arquivos do código fonte.')">
                            <i class="fas fa-save me-2"></i> Salvar e Aplicar em Todo o Site
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Como Funciona?</h5>
            </div>
            <div class="card-body">
                <p>O recurso <strong>White Label</strong> permite que você revenda ou adapte este sistema para qualquer outra igreja sem precisar programar.</p>
                <ul class="mb-0 ps-3">
                    <li class="mb-2"><strong>A Logo</strong> substitui a imagem <code>public/assets/img/logo.png</code>, alterando o topo do site, menu de admin e relatórios.</li>
                    <li class="mb-2"><strong>A Sigla e o Nome</strong> acionam um script de "Replace" automático que varre todas as views (telas) do sistema e troca os textos estáticos pelo novo nome informado.</li>
                    <li>Não afeta os dados salvos no banco de dados (nomes de membros, etc), apenas a estrutura do site.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<template id="social-link-template">
    <div class="row g-2 align-items-center social-link-row">
        <div class="col-md-4">
            <select name="social_platform[]" class="form-select">
                <?php foreach ($socialIconOptions as $optionKey => $option): ?>
                    <option value="<?= htmlspecialchars($optionKey) ?>"><?= htmlspecialchars($option['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-7">
            <input type="url" name="social_url[]" class="form-control" placeholder="https://...">
        </div>
        <div class="col-md-1 d-grid">
            <button type="button" class="btn btn-outline-danger remove-social-link">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var wrapper = document.getElementById('social-links-wrapper');
    var addButton = document.getElementById('add-social-link');
    var template = document.getElementById('social-link-template');

    function bindRemoveButtons(scope) {
        var buttons = (scope || document).querySelectorAll('.remove-social-link');
        buttons.forEach(function (button) {
            if (button.dataset.bound === '1') {
                return;
            }
            button.dataset.bound = '1';
            button.addEventListener('click', function () {
                var row = button.closest('.social-link-row');
                if (row) {
                    row.remove();
                }
            });
        });
    }

    bindRemoveButtons(document);

    addButton.addEventListener('click', function () {
        var clone = template.content.cloneNode(true);
        wrapper.appendChild(clone);
        bindRemoveButtons(wrapper);
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
