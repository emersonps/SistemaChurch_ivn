<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Vídeo</h1>
    <a href="/admin/video-wall" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-arrow-left me-1"></i> Voltar</a>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="card shadow-sm" style="max-width: 640px;">
    <div class="card-body">
        <form method="POST" action="/admin/video-wall/edit/<?= (int)$video['id'] ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($video['title']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Link do YouTube</label>
                <input type="url" name="youtube_url" class="form-control" value="<?= htmlspecialchars($video['youtube_url']) ?>" required>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span>Categoria</span>
                        <button type="button" class="btn btn-link btn-sm p-0" data-bs-toggle="modal" data-bs-target="#categoryModal">
                            <i class="fas fa-gear me-1"></i>Gerenciar
                        </button>
                    </label>
                    <select name="category" class="form-select">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $video['category'] === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Data</label>
                    <input type="date" name="video_date" class="form-control" value="<?= !empty($video['video_date']) ? date('Y-m-d', strtotime($video['video_date'])) : '' ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Pregador(a) / Ministério</label>
                <input type="text" name="speaker" class="form-control" value="<?= htmlspecialchars($video['speaker'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($video['description'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Salvar</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/partials/category_modal.php'; ?>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
