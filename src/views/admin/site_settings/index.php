<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2">Configurações do Site (Layout)</h1>
</div>

<style>
    .member-form-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
    }
    .member-form-card-header {
        display: flex;
        align-items: flex-start;
        gap: .85rem;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.07);
        background: #fafafa;
    }
    .member-form-badge {
        flex: 0 0 auto;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #eef0f2;
        color: #212529;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: .95rem;
    }
    .member-form-card-title {
        font-weight: 800;
        font-size: 1.05rem;
        color: #1a1a1a;
        line-height: 1.2;
    }
    .member-form-card-subtitle {
        font-size: .82rem;
        color: #868e96;
        margin-top: .1rem;
    }
    .member-form-card-body { padding: 1.25rem; }
    .theme-option {
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        transition: border-color .15s ease, box-shadow .15s ease;
        height: 100%;
        background: #fff;
    }
    .theme-option.is-selected {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
    .theme-preview {
        height: 110px;
        position: relative;
        overflow: hidden;
    }
    .theme-option-body {
        padding: .85rem;
        text-align: center;
    }
    .theme-option-name {
        font-weight: 700;
        font-size: .92rem;
        color: #1a1a1a;
    }
    .theme-option-desc {
        font-size: .78rem;
        color: #868e96;
        margin-top: .25rem;
    }
    .theme-current-tag {
        background: #b30000;
        color: #fff;
        text-align: center;
        font-size: .74rem;
        font-weight: 700;
        padding: .3rem;
    }
    .form-check-input:checked {
        background-color: #b30000;
        border-color: #b30000;
    }
</style>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= $_SESSION['flash_success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<form action="/admin/site-settings/update" method="POST" enctype="multipart/form-data" id="siteSettingsForm">
    <?= csrf_field() ?>

    <div class="member-form-card mb-3">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-palette"></i></div>
            <div>
                <div class="member-form-card-title">Escolha o Tema do Site Principal</div>
                <div class="member-form-card-subtitle">Selecione o modelo visual que será exibido no site público.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <?php foreach ($themes as $id => $theme): ?>
                <div class="col-md-4 col-lg-3">
                    <label class="w-100 m-0" style="cursor: pointer;">
                        <div class="theme-option <?= ($currentSettings['theme_id'] == $id) ? 'is-selected' : '' ?>" data-theme-option>
                            <div class="theme-preview" style="background-color: <?= $theme['secondary_color'] ?>;">
                                <?php
                                    $bgUrl = "/assets/uploads/themes/" . $theme['hero_bg_image'];
                                    $unsplash_fallbacks = [
                                        'theme-0' => 'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=600&q=80',
                                        'theme-1' => 'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=600&q=80',
                                        'theme-2' => 'https://images.unsplash.com/photo-1504052434569-70ad5836ab65?w=600&q=80',
                                        'theme-3' => 'https://images.unsplash.com/photo-1448375240586-882707db888b?w=600&q=80',
                                        'theme-4' => 'https://images.unsplash.com/photo-1502759683299-cdcd6974244f?w=600&q=80',
                                        'theme-5' => 'https://images.unsplash.com/photo-1550684848-fac1c5b4e853?w=600&q=80',
                                        'theme-6' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=600&q=80',
                                        'theme-7' => 'https://images.unsplash.com/photo-1498623116890-37e912163d5d?w=600&q=80',
                                        'theme-8' => 'https://images.unsplash.com/photo-1478760329108-5c3ed9d495a0?w=600&q=80',
                                        'theme-9' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=600&q=80',
                                        'theme-10'=> 'https://images.unsplash.com/photo-1518621736915-f3b1c41bfd00?w=600&q=80'
                                    ];
                                    if (!file_exists(__DIR__ . '/../../../../public/assets/uploads/themes/' . $theme['hero_bg_image'])) {
                                        $bgUrl = $unsplash_fallbacks[$id] ?? $unsplash_fallbacks['theme-1'];
                                    }
                                ?>
                                <div style="position: absolute; inset: 0; background-image: url('<?= $bgUrl ?>'); background-size: cover; background-position: center; opacity: 0.6;"></div>
                                <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 8px; background: linear-gradient(transparent, rgba(0,0,0,0.8));">
                                    <span class="badge" style="background-color: <?= $theme['primary_color'] ?>;">Cor Principal</span>
                                </div>
                            </div>
                            <div class="theme-option-body">
                                <div class="form-check d-flex justify-content-center mb-1">
                                    <input class="form-check-input theme-radio me-2" type="radio" name="theme_id" id="theme_<?= $id ?>" value="<?= $id ?>" <?= ($currentSettings['theme_id'] == $id) ? 'checked' : '' ?>>
                                    <label class="form-check-label theme-option-name" for="theme_<?= $id ?>"><?= htmlspecialchars($theme['name']) ?></label>
                                </div>
                                <div class="theme-option-desc" style="font-family: <?= htmlspecialchars($theme['font_family']) ?>;"><?= htmlspecialchars($theme['description']) ?></div>
                                <div class="theme-option-desc mt-1" style="font-family: <?= htmlspecialchars($theme['font_family']) ?>;">Fonte: <?= htmlspecialchars(explode(',', $theme['font_family'])[0]) ?></div>
                            </div>
                            <?php if ($currentSettings['theme_id'] == $id): ?>
                                <div class="theme-current-tag"><i class="fas fa-check-circle me-1"></i> Tema Atual</div>
                            <?php endif; ?>
                        </div>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="member-form-card mb-3">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-image"></i></div>
            <div>
                <div class="member-form-card-title">Personalizar Imagem de Fundo (Hero Section)</div>
                <div class="member-form-card-subtitle">Envie uma imagem própria para substituir o fundo padrão do tema escolhido.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <label for="custom_hero_bg" class="form-label fw-semibold">Imagem Personalizada (Opcional)</label>
            <input class="form-control" type="file" id="custom_hero_bg" name="custom_hero_bg" accept=".jpg,.jpeg,.png,.webp">
            <div class="form-text">Tamanho recomendado: 1920x1080px. Formatos: JPG, PNG, WEBP.</div>

            <?php if (strpos($currentSettings['hero_bg_image'], 'custom_hero_') === 0): ?>
                <div class="mt-2 text-success small">
                    <i class="fas fa-check-circle me-1"></i> O site atualmente está usando uma imagem personalizada.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="/" target="_blank" class="btn btn-outline-secondary rounded-pill fw-semibold px-4">Ver Site</a>
        <button type="submit" class="btn btn-dark rounded-pill fw-semibold px-4"><i class="fas fa-save me-2"></i> Salvar Configurações</button>
    </div>
</form>

<script>
document.querySelectorAll('.theme-radio').forEach(function (radio) {
    radio.addEventListener('change', function () {
        document.querySelectorAll('[data-theme-option]').forEach(function (option) {
            option.classList.remove('is-selected');
        });
        this.closest('[data-theme-option]').classList.add('is-selected');
    });
});

document.querySelectorAll('.theme-preview, .theme-option-body').forEach(function (el) {
    el.addEventListener('click', function (e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'LABEL') return;
        const option = el.closest('[data-theme-option]');
        const radio = option.querySelector('.theme-radio');
        if (radio) {
            radio.checked = true;
            radio.dispatchEvent(new Event('change'));
        }
    });
});
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
