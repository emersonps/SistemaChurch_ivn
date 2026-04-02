<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2">Configurações do Site (Layout)</h1>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['flash_success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Escolha o Tema do Site Principal</h5>
    </div>
    <div class="card-body">
        <form action="/admin/site-settings/update" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row">
                <?php foreach ($themes as $id => $theme): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card h-100 theme-card <?= ($currentSettings['theme_id'] == $id) ? 'border-primary shadow' : '' ?>" style="cursor: pointer;" onclick="document.getElementById('theme_<?= $id ?>').checked = true; updateCardStyles();">
                        
                        <!-- Preview Box -->
                        <div class="card-img-top theme-preview" style="height: 120px; background-color: <?= $theme['secondary_color'] ?>; position: relative; overflow: hidden;">
                            <?php 
                                $bgUrl = "/assets/uploads/themes/" . $theme['hero_bg_image'];
                                // Fallback para imagens do unsplash para os previews baseados no ID do tema
                                $unsplash_fallbacks = [
                                    'theme-0' => 'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=600&q=80', // Original
                                    'theme-1' => 'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=600&q=80',
                                    'theme-2' => 'https://images.unsplash.com/photo-1504052434569-70ad5836ab65?w=600&q=80',
                                    'theme-3' => 'https://images.unsplash.com/photo-1448375240586-882707db888b?w=600&q=80',
                                    'theme-4' => 'https://images.unsplash.com/photo-1502759683299-cdcd6974244f?w=600&q=80', // Fogo/Chamas (Novo)
                                    'theme-5' => 'https://images.unsplash.com/photo-1550684848-fac1c5b4e853?w=600&q=80', // Roxo/Majestoso
                                    'theme-6' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=600&q=80',
                                    'theme-7' => 'https://images.unsplash.com/photo-1498623116890-37e912163d5d?w=600&q=80', // Oceano/Barco
                                    'theme-8' => 'https://images.unsplash.com/photo-1478760329108-5c3ed9d495a0?w=600&q=80', // Dark/Noite Estrelada
                                    'theme-9' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=600&q=80', // Natureza/Terra
                                    'theme-10'=> 'https://images.unsplash.com/photo-1518621736915-f3b1c41bfd00?w=600&q=80'  // Graça Rosa
                                ];
                                if (!file_exists(__DIR__ . '/../../../../public/assets/uploads/themes/' . $theme['hero_bg_image'])) {
                                    $bgUrl = $unsplash_fallbacks[$id] ?? $unsplash_fallbacks['theme-1'];
                                }
                            ?>
                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: url('<?= $bgUrl ?>'); background-size: cover; background-position: center; opacity: 0.6;"></div>
                            <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 10px; background: linear-gradient(transparent, rgba(0,0,0,0.8));">
                                <span class="badge" style="background-color: <?= $theme['primary_color'] ?>;">Cor Principal</span>
                            </div>
                        </div>

                        <div class="card-body text-center">
                            <div class="form-check d-flex justify-content-center mb-2">
                                <input class="form-check-input theme-radio me-2" type="radio" name="theme_id" id="theme_<?= $id ?>" value="<?= $id ?>" <?= ($currentSettings['theme_id'] == $id) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold" for="theme_<?= $id ?>">
                                    <?= $theme['name'] ?>
                                </label>
                            </div>
                            <p class="small text-muted mb-2" style="font-family: <?= $theme['font_family'] ?>;">
                                <?= $theme['description'] ?>
                            </p>
                            <small class="text-muted" style="font-family: <?= $theme['font_family'] ?>;">Fonte: <?= explode(',', $theme['font_family'])[0] ?></small>
                        </div>
                        <?php if ($currentSettings['theme_id'] == $id): ?>
                        <div class="card-footer bg-primary text-white text-center py-1">
                            <small><i class="fas fa-check-circle"></i> Tema Atual</small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <hr class="my-4">

            <h5 class="mb-3">Personalizar Imagem de Fundo (Hero Section)</h5>
            <div class="mb-4">
                <label for="custom_hero_bg" class="form-label">Você pode enviar uma imagem própria para substituir a imagem padrão do tema escolhido (Opcional):</label>
                <input class="form-control" type="file" id="custom_hero_bg" name="custom_hero_bg" accept=".jpg,.jpeg,.png,.webp">
                <div class="form-text">Tamanho recomendado: 1920x1080px. Formatos: JPG, PNG, WEBP.</div>
                
                <?php if (strpos($currentSettings['hero_bg_image'], 'custom_hero_') === 0): ?>
                    <div class="mt-2 text-success small">
                        <i class="fas fa-info-circle"></i> O site atualmente está usando uma imagem personalizada.
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="/" target="_blank" class="btn btn-outline-secondary me-md-2">Ver Site</a>
                <button type="submit" class="btn btn-primary px-4">Salvar Configurações</button>
            </div>
        </form>
    </div>
</div>

<script>
function updateCardStyles() {
    document.querySelectorAll('.theme-card').forEach(card => {
        card.classList.remove('border-primary', 'shadow');
        let radio = card.querySelector('.theme-radio');
        if (radio && radio.checked) {
            card.classList.add('border-primary', 'shadow');
        }
    });
}
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
