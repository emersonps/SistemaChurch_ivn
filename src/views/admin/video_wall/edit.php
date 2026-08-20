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
            <div class="form-check form-switch mb-3">
                <input type="checkbox" name="is_livestream" id="isLivestream" class="form-check-input" <?= !empty($video['is_livestream']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="isLivestream">É uma transmissão ao vivo</label>
            </div>
            <div class="mb-3 <?= empty($video['is_livestream']) ? 'd-none' : '' ?>" id="livestreamScheduleField">
                <label class="form-label">Início da transmissão</label>
                <input type="datetime-local" name="livestream_scheduled_at" class="form-control" value="<?= !empty($video['livestream_scheduled_at']) ? date('Y-m-d\TH:i', strtotime($video['livestream_scheduled_at'])) : '' ?>">
                <div class="form-text">Antes desse horário o vídeo mostra uma contagem regressiva; a partir dele, o selo "AO VIVO".</div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Salvar</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var checkbox = document.getElementById('isLivestream');
    var field = document.getElementById('livestreamScheduleField');
    function sync() {
        field.classList.toggle('d-none', !checkbox.checked);
    }
    checkbox.addEventListener('change', sync);
    sync();
});
</script>

<?php include __DIR__ . '/partials/category_modal.php'; ?>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
