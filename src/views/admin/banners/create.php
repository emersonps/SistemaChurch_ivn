<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Novo Banner</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/banners" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <form action="/admin/banners/create" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="title" class="form-label">Título</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            
            <div class="mb-3">
                <label for="image" class="form-label">Imagem</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                <div class="form-text">Recomendado: 1200x400 pixels.</div>
            </div>

            <div class="mb-3">
                <label for="link" class="form-label">Link (Opcional)</label>
                <input type="text" class="form-control" id="link" name="link" placeholder="https://...">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="display_order" class="form-label">Ordem de Exibição</label>
                    <input type="number" class="form-control" id="display_order" name="display_order" value="0">
                </div>
                
                <div class="col-md-6 mb-3 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="active" name="active" value="1" checked>
                        <label class="form-check-label" for="active">Ativo</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Salvar Banner</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
