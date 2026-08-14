<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/gallery" class="text-decoration-none">Galeria de Fotos</a></li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Editar Álbum</h1>
    </div>
    <div class="d-none d-md-flex gap-2">
        <a href="/admin/gallery" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">Cancelar</a>
        <button type="submit" form="albumEditForm" class="btn btn-dark rounded-pill fw-semibold px-3">Salvar</button>
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
</style>

<form action="/admin/gallery/edit/<?= $album['id'] ?>" method="POST" class="app-form-with-bottom-actions" id="albumEditForm">
    <?= csrf_field() ?>

    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-images"></i></div>
            <div>
                <div class="member-form-card-title">Informações do Álbum</div>
                <div class="member-form-card-subtitle">Título, data e local do evento.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Título do Álbum <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($album['title']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data do Evento</label>
                    <input type="date" class="form-control" name="event_date" value="<?= !empty($album['event_date']) ? date('Y-m-d', strtotime($album['event_date'])) : '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Local</label>
                    <input type="text" class="form-control" name="location" value="<?= htmlspecialchars($album['location']) ?>">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Descrição</label>
                    <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($album['description']) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5 d-md-none">
        <a href="/admin/gallery" class="btn btn-outline-secondary px-4">Cancelar</a>
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
    </div>
</form>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
