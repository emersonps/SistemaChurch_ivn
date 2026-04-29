<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Novo Álbum</h1>
</div>

<form action="/admin/gallery/create" method="POST" class="row g-3 app-form-with-bottom-actions">
    <?= csrf_field() ?>
    <div class="col-md-6">
        <label class="form-label">Título do Álbum</label>
        <input type="text" class="form-control" name="title" required placeholder="Ex: Congresso de Jovens 2024">
    </div>
    <div class="col-md-3">
        <label class="form-label">Data do Evento</label>
        <input type="date" class="form-control" name="event_date">
    </div>
    <div class="col-md-3">
        <label class="form-label">Local</label>
        <input type="text" class="form-control" name="location">
    </div>
    <div class="col-md-12">
        <label class="form-label">Descrição</label>
        <textarea class="form-control" name="description" rows="3"></textarea>
    </div>

    <div class="col-12 mt-4 text-end d-none d-lg-block">
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
        <a href="/admin/gallery" class="btn btn-outline-secondary px-4">Cancelar</a>
    </div>

    <div class="col-12 app-form-bottom-actions d-lg-none">
        <div class="row g-2">
            <div class="col-6">
                <button type="submit" class="btn btn-primary w-100">Salvar</button>
            </div>
            <div class="col-6">
                <a href="/admin/gallery" class="btn btn-outline-secondary w-100">Cancelar</a>
            </div>
        </div>
    </div>
</form>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
