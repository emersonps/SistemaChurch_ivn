<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/banners" class="text-decoration-none">Banners</a></li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Editar Banner</h1>
    </div>
    <div class="d-none d-md-flex gap-2">
        <a href="/admin/banners" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">Cancelar</a>
        <button type="submit" form="bannerEditForm" class="btn btn-dark rounded-pill fw-semibold px-3">Salvar</button>
    </div>
</div>

<style>
    .member-form-topbar {
        position: sticky;
        top: 0;
        z-index: 1030;
        background: #f8f9fa;
        padding-bottom: .85rem;
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
    .member-form-card-body .form-label {
        font-weight: 600;
        font-size: .88rem;
        color: #343a40;
    }
    .member-form-card-body .form-control {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
        padding: .55rem .8rem;
    }
    .member-form-card-body .form-control:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
    .required-mark { color: #dc3545; }
    .status-options { display: flex; gap: .75rem; flex-wrap: wrap; }
    .status-option {
        flex: 1 1 220px;
        border: 1.5px solid rgba(0,0,0,0.12);
        border-radius: 12px;
        padding: .85rem 1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: .65rem;
        transition: border-color .15s ease, background-color .15s ease;
    }
    .status-option input { margin: 0; }
    .status-option .status-title { font-weight: 700; font-size: .92rem; color: #212529; }
    .status-option .status-desc { font-size: .78rem; color: #868e96; }
    .status-option.active {
        border-color: #b30000;
        background: rgba(179,0,0,0.045);
    }
    .current-image-preview {
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 10px;
        padding: .5rem;
        background: #fafafa;
        display: inline-block;
    }
</style>

<form action="/admin/banners/edit/<?= $banner['id'] ?>" method="POST" enctype="multipart/form-data" class="app-form-with-bottom-actions" id="bannerEditForm">
    <?= csrf_field() ?>

    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-image"></i></div>
            <div>
                <div class="member-form-card-title">Informações do Banner</div>
                <div class="member-form-card-subtitle">Imagem, link e ordem de exibição.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label for="title" class="form-label">Título <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($banner['title']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="display_order" class="form-label">Ordem de Exibição</label>
                    <input type="number" class="form-control" id="display_order" name="display_order" value="<?= $banner['display_order'] ?>">
                </div>

                <div class="col-md-8">
                    <label for="image" class="form-label">Imagem</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    <div class="form-text">Deixe em branco para manter a imagem atual.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Imagem Atual</label><br>
                    <?php if ($banner['image_path']): ?>
                        <div class="current-image-preview">
                            <img src="/<?= $banner['image_path'] ?>" alt="Atual" style="max-height: 70px; max-width: 100%; width: auto; height: auto; border-radius: 6px;">
                        </div>
                    <?php else: ?>
                        <span class="text-muted small">Sem imagem</span>
                    <?php endif; ?>
                </div>

                <div class="col-md-8">
                    <label for="link" class="form-label">Link (Opcional)</label>
                    <input type="text" class="form-control" id="link" name="link" value="<?= htmlspecialchars($banner['link']) ?>" placeholder="https://...">
                </div>

                <div class="col-12">
                    <label class="form-label d-block">Situação</label>
                    <div class="status-options">
                        <label class="status-option <?= $banner['active'] ? 'active' : '' ?>" data-status-option>
                            <input type="checkbox" id="active" name="active" value="1" <?= $banner['active'] ? 'checked' : '' ?>>
                            <div>
                                <div class="status-title">Ativo</div>
                                <div class="status-desc">O banner aparecerá nas telas públicas.</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5 d-md-none">
        <a href="/admin/banners" class="btn btn-outline-secondary px-4">Cancelar</a>
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
    </div>
</form>

<script>
    const activeCheckbox = document.getElementById('active');
    const statusOption = activeCheckbox.closest('[data-status-option]');
    activeCheckbox.addEventListener('change', function () {
        statusOption.classList.toggle('active', activeCheckbox.checked);
    });
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
