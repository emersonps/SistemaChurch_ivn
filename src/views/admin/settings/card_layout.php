<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Layout da Carteirinha</h1>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Layout salvo com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-id-card text-primary me-2"></i> Escolha o modelo para a carteirinha de membro</h5>
    </div>
    <div class="card-body">
        <form action="/admin/settings/card-layout" method="POST">
            <?= csrf_field() ?>

            <!-- Cor da Sigla da Igreja -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold"><i class="fas fa-palette me-1"></i> Cor da Sigla (IVN)</label>
                    <div class="input-group">
                        <?php $siglaColor = getSystemSetting('card_sigla_color', '#0d6efd'); ?>
                        <input type="color" class="form-control form-control-color" name="card_sigla_color" value="<?= $siglaColor ?>" title="Escolha a cor da sigla da igreja">
                        <input type="text" class="form-control" value="<?= $siglaColor ?>" disabled style="max-width: 100px;">
                    </div>
                    <small class="text-muted">Esta cor será aplicada apenas na sigla da igreja nos modelos de imagem.</small>
                </div>
            </div>

            <div class="row">
                <?php foreach ($models as $key => $model): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <label class="w-100" style="cursor: pointer;">
                            <div class="card border-<?= $current_layout === $key ? 'primary' : 'light' ?> shadow-sm h-100" style="<?= $current_layout === $key ? 'border-width: 2px !important;' : '' ?>">
                                <div class="card-header bg-light d-flex align-items-center">
                                    <input class="form-check-input me-2 mt-0" type="radio" name="card_layout" value="<?= $key ?>" <?= $current_layout === $key ? 'checked' : '' ?>>
                                    <span class="fw-bold"><?= $model['name'] ?></span>
                                </div>
                                <div class="card-body p-2 bg-light">
                                    <!-- Preview da Carteirinha -->
                                    <div class="position-relative bg-white shadow-sm overflow-hidden" 
                                         style="width: 100%; aspect-ratio: 1.58; border-radius: 8px; font-family: 'Arial', sans-serif;">
                                        
                                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: <?= $model['bg'] ?>; z-index: 0;"></div>
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
            
            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i> Salvar Layout</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>