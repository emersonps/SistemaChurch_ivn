<?php include __DIR__ . '/../../layout/header.php'; ?>
<?php $siteProfile = getChurchSiteProfileSettings(); ?>

<div class="member-form-topbar d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2 mb-0">Layout da Carteirinha</h1>
    <div class="d-none d-md-flex gap-2">
        <button type="button" class="btn btn-outline-secondary rounded-pill fw-semibold px-4" data-bs-toggle="modal" data-bs-target="#cardPreviewModal"><i class="fas fa-id-card me-2"></i> Ver Carteirinha</button>
        <button type="submit" form="cardLayoutForm" class="btn btn-dark rounded-pill fw-semibold px-4"><i class="fas fa-save me-2"></i> Salvar Layout</button>
    </div>
</div>

<style>
    .member-form-topbar {
        position: sticky;
        top: 0;
        z-index: 1030;
        background: #f8f9fa;
    }
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
    .member-form-card-body .form-control,
    .member-form-card-body .form-select {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
        padding: .55rem .8rem;
    }
    .member-form-card-body .form-control:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }

    .layout-option {
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        height: 100%;
        background: #fff;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .layout-option.is-selected {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
    .layout-option-header {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .6rem .85rem;
        background: #fafafa;
        border-bottom: 1px solid rgba(0,0,0,0.06);
        font-weight: 700;
        font-size: .86rem;
    }
    .layout-option-body {
        padding: .75rem;
        background: #f8f9fa;
    }
    .custom-badge {
        display: inline-block;
        padding: .1rem .5rem;
        border-radius: 999px;
        font-size: .64rem;
        font-weight: 700;
        background: rgba(179,0,0,0.10);
        color: #b30000;
        margin-left: auto;
    }
    .card-preview-box {
        width: 100%;
        aspect-ratio: 1.58;
        border-radius: 8px;
        position: relative;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    #cardPreviewModal .modal-content { border-radius: 16px; border: none; overflow: hidden; }
    #cardPreviewModal .modal-header { background: #fafafa; }
    #previewCardFront {
        width: 85.6mm;
        height: 53.98mm;
        max-width: 100%;
        border: 1px dashed #ccc;
        font-family: 'Arial', sans-serif;
        position: relative;
        overflow: hidden;
        margin: 0 auto;
    }
</style>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> Layout salvo com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<form action="/admin/settings/card-layout" method="POST" enctype="multipart/form-data" id="cardLayoutForm">
    <?= csrf_field() ?>

    <div class="member-form-card mb-3">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-palette"></i></div>
            <div>
                <div class="member-form-card-title">Cor da Sigla</div>
                <div class="member-form-card-subtitle">Aplicada na sigla da igreja (<?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?>) nos modelos com imagem de fundo.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row">
                <div class="col-md-4">
                    <?php $siglaColor = getSystemSetting('card_sigla_color', '#0d6efd'); ?>
                    <div class="input-group">
                        <input type="color" class="form-control form-control-color" name="card_sigla_color" id="siglaColorInput" value="<?= htmlspecialchars($siglaColor) ?>" title="Escolha a cor da sigla da igreja">
                        <input type="text" class="form-control" id="siglaColorText" value="<?= htmlspecialchars($siglaColor) ?>" disabled>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="member-form-card mb-3">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-upload"></i></div>
            <div>
                <div class="member-form-card-title">Enviar Imagem Personalizada</div>
                <div class="member-form-card-subtitle">Escolha qualquer imagem sua para usar como fundo da carteirinha.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <label for="custom_card_image" class="form-label fw-semibold">Imagem (Opcional)</label>
            <input class="form-control" type="file" id="custom_card_image" name="custom_card_image" accept=".jpg,.jpeg,.png,.webp">
            <div class="form-text">Tamanho recomendado: proporção 1.58:1 (ex: 1012x640px). Formatos: JPG, PNG, WEBP. Ao enviar, esta imagem é selecionada automaticamente ao salvar.</div>
            <?php if (isset($models['custom_upload'])): ?>
                <div class="mt-2 text-success small">
                    <i class="fas fa-check-circle me-1"></i> Você já possui uma imagem personalizada enviada.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="member-form-card mb-3">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-id-card"></i></div>
            <div>
                <div class="member-form-card-title">Escolha o Modelo</div>
                <div class="member-form-card-subtitle">Selecione o layout usado para gerar as carteirinhas de membro.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <?php foreach ($models as $key => $model): ?>
                    <div class="col-md-6 col-lg-4">
                        <label class="w-100 m-0" style="cursor: pointer;">
                            <div class="layout-option <?= $current_layout === $key ? 'is-selected' : '' ?>" data-layout-option>
                                <div class="layout-option-header">
                                    <input class="form-check-input layout-radio me-1 mt-0" type="radio" name="card_layout" value="<?= $key ?>" <?= $current_layout === $key ? 'checked' : '' ?>>
                                    <span><?= htmlspecialchars($model['name']) ?></span>
                                    <?php if (!empty($model['custom'])): ?>
                                        <span class="custom-badge">Sua Imagem</span>
                                    <?php endif; ?>
                                </div>
                                <div class="layout-option-body">
                                    <div class="card-preview-box" style="font-family: 'Arial', sans-serif;">
                                        <div style="position: absolute; inset: 0; background: <?= $model['bg'] ?>; z-index: 0;"></div>
                                        <?php if (($model['type'] ?? 'color') !== 'image'): ?>
                                            <div style="position: absolute; top: 0; left: 0; width: 6px; height: 100%; background-color: <?= $model['left'] ?>; z-index: 1;"></div>
                                            <div style="position: absolute; top: 0; right: 0; width: 100%; height: 30%; background: <?= $model['top'] ?>; z-index: 0;"></div>
                                        <?php endif; ?>

                                        <div class="d-flex h-100 position-relative" style="z-index: 2; padding: 10px;">
                                            <div class="d-flex flex-column align-items-center justify-content-start" style="width: 32%;">
                                                <div class="mb-1 bg-light rounded" style="height: 20px; width: 40px;"></div>
                                                <div class="border border-1 rounded-3 overflow-hidden shadow-sm" style="width: 40px; height: 50px; background-color: #fff; border-color: <?= $model['left'] ?> !important;">
                                                    <div class="bg-light d-flex align-items-center justify-content-center h-100">
                                                        <i class="fas fa-user text-secondary"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="ps-2 d-flex flex-column justify-content-center" style="width: 68%;">
                                                <div style="height: 6px; width: 60%; background: <?= $model['left'] ?>; margin-bottom: 8px; border-radius: 3px;"></div>
                                                <div style="height: 4px; width: 80%; background: #ccc; margin-bottom: 4px; border-radius: 2px;"></div>
                                                <div style="height: 4px; width: 50%; background: #ccc; margin-bottom: 4px; border-radius: 2px;"></div>
                                                <div class="d-flex mt-2">
                                                    <div style="width: 30px; height: 30px; background: #eee; margin-left: auto;"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if (($model['type'] ?? 'color') !== 'image'): ?>
                                            <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 4px; background-color: <?= $model['bottom'] ?>; z-index: 1;"></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-4 d-md-none">
        <button type="button" class="btn btn-outline-secondary rounded-pill fw-semibold px-4" data-bs-toggle="modal" data-bs-target="#cardPreviewModal"><i class="fas fa-id-card me-2"></i> Ver Carteirinha</button>
        <button type="submit" class="btn btn-dark rounded-pill fw-semibold px-4"><i class="fas fa-save me-2"></i> Salvar Layout</button>
    </div>
</form>

<!-- Modal Pré-visualização da Carteirinha -->
<div class="modal fade" id="cardPreviewModal" tabindex="-1" aria-labelledby="cardPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cardPreviewModalLabel"><i class="fas fa-id-card me-2"></i>Pré-visualização da Carteirinha</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="previewCardFront" class="bg-white shadow-sm">
                    <div id="previewBg" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0;"></div>
                    <div id="previewLeftBar" style="position: absolute; top: 0; left: 0; width: 6px; height: 100%; z-index: 1;"></div>
                    <div id="previewTopGradient" style="position: absolute; top: 0; right: 0; width: 100%; height: 60px; z-index: 0;"></div>

                    <div class="d-flex h-100 position-relative" style="z-index: 2; padding: 12px 10px 12px 15px; align-items: flex-start;">
                        <div class="d-flex flex-column align-items-center justify-content-start pt-1" style="width: 32%;">
                            <div class="mb-2">
                                <img src="<?= htmlspecialchars(getChurchLogoUrl($siteProfile)) ?>" alt="<?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?>" style="height: 54px; width: auto; filter: drop-shadow(0px 2px 2px rgba(0,0,0,0.1));">
                            </div>
                            <div id="previewPhotoBox" class="border border-2 rounded-3 overflow-hidden shadow-sm" style="width: 72px; height: 95px; background-color: #fff;">
                                <div class="bg-light d-flex align-items-center justify-content-center h-100">
                                    <i class="fas fa-user text-secondary fa-2x"></i>
                                </div>
                            </div>
                        </div>

                        <div class="ps-2 d-flex flex-column" style="width: 68%; margin-top: -2px;">
                            <div class="mb-2 pe-1">
                                <h6 id="previewSigla" class="m-0 fw-bold text-uppercase" style="font-size: 16px; letter-spacing: 0.5px;"><?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?></h6>
                                <div class="fw-bold text-uppercase" style="font-size: 7px; line-height: 1.1; color: #333; max-width: 170px;"><?= htmlspecialchars($siteProfile['name'] ?? 'Igreja Vida Nova') ?></div>
                            </div>

                            <div class="mb-2 pe-1">
                                <div class="d-inline-block bg-white px-2 py-1 shadow-sm" style="opacity: 0.95; border-radius: 12px;">
                                    <label class="d-block text-primary" style="font-size: 6px; margin-bottom: 0; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold;">Nome do Membro</label>
                                    <div class="fw-bold" style="font-size: 11px; color: #333; padding-bottom: 1px; max-width: 170px; line-height: 1.1;">João da Silva Exemplo</div>
                                </div>
                            </div>

                            <div class="d-flex mb-2 pe-1">
                                <div class="me-2">
                                    <div class="d-inline-block bg-white px-2 py-1 shadow-sm" style="opacity: 0.95; border-radius: 12px;">
                                        <label class="d-block text-primary" style="font-size: 6px; margin-bottom: 0; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold;">Cargo/Função</label>
                                        <div class="fw-bold" style="font-size: 10px; color: #444; max-width: 80px; line-height: 1.1;">Membro</div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-inline-block bg-white px-2 py-1 shadow-sm" style="opacity: 0.95; border-radius: 12px;">
                                        <label class="d-block text-primary" style="font-size: 6px; margin-bottom: 0; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold;">Congregação</label>
                                        <div class="fw-bold" style="font-size: 10px; color: #444; max-width: 100px; line-height: 1.1;">Sede</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex pe-1">
                                <div class="me-2">
                                    <div class="d-inline-block bg-white px-2 py-1 shadow-sm" style="opacity: 0.95; border-radius: 12px;">
                                        <label class="d-block text-primary" style="font-size: 6px; margin-bottom: 0; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold;">Data de Batismo</label>
                                        <div class="fw-bold" style="font-size: 9px; color: #555;">01/01/2020</div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-inline-block bg-white px-2 py-1 shadow-sm" style="opacity: 0.95; border-radius: 12px;">
                                        <label class="d-block text-primary" style="font-size: 6px; margin-bottom: 0; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold;">ID Único</label>
                                        <div class="fw-bold" style="font-size: 9px; color: #555; font-family: monospace;">0000001</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="previewBottomBar" style="position: absolute; bottom: 0; left: 0; width: 100%; height: 4px; z-index: 1;"></div>
                </div>
                <p class="text-muted small mt-3 mb-0">Exemplo com dados fictícios, no modelo e cor atualmente selecionados.</p>
            </div>
        </div>
    </div>
</div>

<script>
    const cardModels = <?= json_encode($models) ?>;

    document.getElementById('siglaColorInput').addEventListener('input', function () {
        document.getElementById('siglaColorText').value = this.value;
    });

    function getCheckedLayoutKey() {
        const checked = document.querySelector('.layout-radio:checked');
        return checked ? checked.value : Object.keys(cardModels)[0];
    }

    document.querySelectorAll('.layout-radio').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('[data-layout-option]').forEach(function (opt) {
                opt.classList.remove('is-selected');
            });
            this.closest('[data-layout-option]').classList.add('is-selected');
        });
    });

    document.querySelectorAll('.layout-option-body').forEach(function (el) {
        el.addEventListener('click', function () {
            const option = el.closest('[data-layout-option]');
            const radio = option.querySelector('.layout-radio');
            if (radio) {
                radio.checked = true;
                radio.dispatchEvent(new Event('change'));
            }
        });
    });

    function renderCardPreview() {
        const key = getCheckedLayoutKey();
        const model = cardModels[key];
        if (!model) return;

        const isImageLayout = (model.type || 'color') === 'image';
        const siglaColor = isImageLayout ? document.getElementById('siglaColorInput').value : model.left;

        document.getElementById('previewBg').style.background = model.bg;
        document.getElementById('previewSigla').style.color = siglaColor;
        // Bootstrap's .border utility sets border-color with !important, so a plain
        // style.borderColor assignment is silently ignored — must use setProperty.
        document.getElementById('previewPhotoBox').style.setProperty('border-color', model.left, 'important');

        const leftBar = document.getElementById('previewLeftBar');
        const topGradient = document.getElementById('previewTopGradient');
        const bottomBar = document.getElementById('previewBottomBar');

        if (isImageLayout) {
            leftBar.style.display = 'none';
            topGradient.style.display = 'none';
            bottomBar.style.display = 'none';
        } else {
            leftBar.style.display = '';
            leftBar.style.backgroundColor = model.left;
            topGradient.style.display = '';
            topGradient.style.background = model.top;
            bottomBar.style.display = '';
            bottomBar.style.backgroundColor = model.bottom;
        }
    }

    document.getElementById('cardPreviewModal').addEventListener('show.bs.modal', renderCardPreview);
    document.getElementById('siglaColorInput').addEventListener('input', renderCardPreview);
    document.querySelectorAll('.layout-radio').forEach(function (radio) {
        radio.addEventListener('change', renderCardPreview);
    });
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
