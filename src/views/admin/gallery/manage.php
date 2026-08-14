<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/gallery" class="text-decoration-none">Galeria de Fotos</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($album['title']) ?></li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Gerenciar Fotos: <?= htmlspecialchars($album['title']) ?></h1>
    </div>
    <a href="/admin/gallery" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
        <i class="fas fa-arrow-left me-1"></i> Voltar
    </a>
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
    .member-form-card-body .form-control {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
    }
    .member-form-card-body .form-control:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }

    .count-pill {
        display: inline-flex;
        align-items: center;
        padding: .3rem .7rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
        background: #eef0f2;
        color: #495057;
    }

    .photo-tile {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
        height: 100%;
    }
    .photo-tile-frame {
        width: 100%;
        height: 230px;
        background: #f1f3f5;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .photo-tile-frame img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
    }
    .icon-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        padding: 0;
    }
</style>

<!-- Adicionar Fotos -->
<div class="member-form-card mb-4">
    <div class="member-form-card-header">
        <div class="member-form-badge"><i class="fas fa-upload"></i></div>
        <div>
            <div class="member-form-card-title">Adicionar Fotos</div>
            <div class="member-form-card-subtitle">Limite de 7 fotos por álbum.</div>
        </div>
    </div>
    <div class="member-form-card-body">
        <?php if (count($photos) >= 7): ?>
            <div class="alert alert-warning mb-0">
                Limite de 7 fotos atingido para este álbum. Remova algumas para adicionar novas.
            </div>
        <?php else: ?>
            <form action="/admin/gallery/upload/<?= $album['id'] ?>" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                <?= csrf_field() ?>
                <div class="col-auto flex-grow-1">
                    <label for="photo" class="form-label">Selecionar Imagem</label>
                    <input type="file" class="form-control" name="photo" id="photo" accept="image/*" required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-success rounded-pill fw-semibold px-4">
                        <i class="fas fa-upload me-1"></i> Upload
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="d-flex align-items-center gap-2 mb-3">
    <h5 class="mb-0">Fotos Cadastradas</h5>
    <span class="count-pill"><?= count($photos) ?>/7</span>
</div>

<div class="row g-3">
    <?php foreach ($photos as $photo): ?>
        <div class="col-md-3 col-sm-6">
            <div class="photo-tile">
                <div class="photo-tile-frame">
                    <img src="/uploads/gallery/<?= $photo['filename'] ?>" alt="Foto">
                </div>
                <div class="p-2 text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill fw-semibold px-3 btn-delete-photo" data-href="/admin/gallery/delete_photo/<?= $photo['id'] ?>">
                        <i class="fas fa-trash me-1"></i> Excluir
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($photos)): ?>
        <div class="col-12">
            <div class="member-form-card">
                <div class="member-form-card-body text-center py-5">
                    <i class="fas fa-image fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">Nenhuma foto neste álbum ainda.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.btn-delete-photo').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const href = btn.getAttribute('data-href');
        Swal.fire({
            title: 'Excluir foto?',
            text: 'Esta ação não pode ser desfeita.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
