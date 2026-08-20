<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Vídeo</h1>
    <a href="/admin/video-wall" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-arrow-left me-1"></i> Voltar</a>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<style>
    .livestream-toggle-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        transition: border-color .2s ease, background-color .2s ease;
    }
    .livestream-toggle-card.is-active {
        border-color: #f1aeb5;
        background: rgba(220, 53, 69, 0.05);
    }
    .livestream-icon {
        width: 40px; height: 40px; flex-shrink: 0;
        border-radius: 50%;
        background: #f1f3f5; color: #868e96;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1rem;
        transition: background-color .2s ease, color .2s ease;
    }
    .livestream-toggle-card.is-active .livestream-icon {
        background: #dc3545; color: #fff;
    }
    .livestream-toggle-card .form-switch .form-check-input {
        width: 2.6em; height: 1.4em; cursor: pointer;
    }
</style>

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

            <div class="livestream-toggle-card p-3 mb-3" id="livestreamCard">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <label class="d-flex align-items-center gap-3 mb-0" for="isLivestream" style="cursor: pointer;">
                        <span class="livestream-icon"><i class="fas fa-satellite-dish"></i></span>
                        <span>
                            <span class="d-block fw-semibold">Transmissão ao vivo</span>
                            <span class="d-block small text-muted">Ativa contagem regressiva e o selo "AO VIVO" no site</span>
                        </span>
                    </label>
                    <div class="form-check form-switch mb-0">
                        <input type="checkbox" name="is_livestream" id="isLivestream" class="form-check-input" role="switch" <?= !empty($video['is_livestream']) ? 'checked' : '' ?>>
                    </div>
                </div>
                <div class="mt-3 <?= empty($video['is_livestream']) ? 'd-none' : '' ?>" id="livestreamScheduleField">
                    <label class="form-label">Data e hora da transmissão</label>
                    <input type="datetime-local" name="livestream_scheduled_at" class="form-control" value="<?= !empty($video['livestream_scheduled_at']) ? date('Y-m-d\TH:i', strtotime($video['livestream_scheduled_at'])) : '' ?>" <?= empty($video['is_livestream']) ? 'disabled' : '' ?>>
                    <div class="form-text">Essa é a data usada para o vídeo. Antes desse horário aparece uma contagem regressiva; a partir dele, o selo "AO VIVO".</div>
                </div>
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
                <div class="col-md-6 <?= !empty($video['is_livestream']) ? 'd-none' : '' ?>" id="normalDateField">
                    <label class="form-label">Data</label>
                    <input type="date" name="video_date" class="form-control" value="<?= !empty($video['video_date']) ? date('Y-m-d', strtotime($video['video_date'])) : '' ?>" <?= !empty($video['is_livestream']) ? 'disabled' : '' ?>>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    var checkbox = document.getElementById('isLivestream');
    var card = document.getElementById('livestreamCard');
    var liveField = document.getElementById('livestreamScheduleField');
    var liveInput = liveField.querySelector('input');
    var normalDateField = document.getElementById('normalDateField');
    var normalDateInput = normalDateField.querySelector('input');

    function sync() {
        var isLive = checkbox.checked;
        card.classList.toggle('is-active', isLive);
        liveField.classList.toggle('d-none', !isLive);
        liveInput.disabled = !isLive;
        normalDateField.classList.toggle('d-none', isLive);
        normalDateInput.disabled = isLive;
    }
    checkbox.addEventListener('change', sync);
    sync();
});
</script>

<?php include __DIR__ . '/partials/category_modal.php'; ?>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
