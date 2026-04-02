<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Banner</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/banners" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <form action="/admin/banners/edit/<?= $banner['id'] ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="title" class="form-label">Título</label>
                <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($banner['title']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="image" class="form-label">Imagem (Deixe em branco para manter a atual)</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                <?php if ($banner['image_path']): ?>
                    <div class="mt-2">
                        <img src="/<?= $banner['image_path'] ?>" alt="Atual" style="height: 100px;" class="img-thumbnail">
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="link" class="form-label">Link (Opcional)</label>
                <input type="text" class="form-control" id="link" name="link" value="<?= htmlspecialchars($banner['link']) ?>" placeholder="https://...">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="display_order" class="form-label">Ordem de Exibição</label>
                    <input type="number" class="form-control" id="display_order" name="display_order" value="<?= $banner['display_order'] ?>">
                </div>
                
                <div class="col-md-6 mb-3 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="active" name="active" value="1" <?= $banner['active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="active">Ativo</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Atualizar Banner</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
