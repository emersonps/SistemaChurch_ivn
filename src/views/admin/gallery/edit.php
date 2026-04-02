<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Álbum</h1>
</div>

<form action="/admin/gallery/edit/<?= $album['id'] ?>" method="POST" class="row g-3">
    <?= csrf_field() ?>
    <div class="col-md-6">
        <label class="form-label">Título do Álbum</label>
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

    <div class="col-12 mt-4">
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        <a href="/admin/gallery" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
