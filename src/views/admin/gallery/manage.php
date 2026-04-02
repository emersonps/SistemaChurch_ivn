<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gerenciar Fotos: <?= htmlspecialchars($album['title']) ?></h1>
    <a href="/admin/gallery" class="btn btn-sm btn-secondary">Voltar</a>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Adicionar Fotos</h5>
                <?php if (count($photos) >= 7): ?>
                    <div class="alert alert-warning">
                        Limite de 7 fotos atingido para este álbum. Remova algumas para adicionar novas.
                    </div>
                <?php else: ?>
                    <form action="/admin/gallery/upload/<?= $album['id'] ?>" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <div class="col-auto">
                            <label for="photo" class="form-label">Selecionar Imagem</label>
                            <input type="file" class="form-control" name="photo" id="photo" accept="image/*" required>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-success mb-3">Upload</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<h4 class="mb-3">Fotos Cadastradas (<?= count($photos) ?>/7)</h4>
<div class="row">
    <?php foreach ($photos as $photo): ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <img src="/uploads/gallery/<?= $photo['filename'] ?>" class="card-img-top" alt="Foto" style="height: 200px; object-fit: cover;">
                <div class="card-body text-center">
                    <a href="/admin/gallery/delete_photo/<?= $photo['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Excluir esta foto?')">
                        <i class="fas fa-trash"></i> Excluir
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($photos)): ?>
        <div class="col-12">
            <p class="text-muted">Nenhuma foto neste álbum ainda.</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
