<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/studies" class="text-decoration-none">Estudos</a></li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Editar Estudo / Esboço</h1>
    </div>
    <div class="d-none d-md-flex gap-2">
        <a href="/admin/studies" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">Cancelar</a>
        <button type="submit" form="studyEditForm" class="btn btn-dark rounded-pill fw-semibold px-3">Salvar</button>
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
    .member-form-card-body .form-control,
    .member-form-card-body .form-select {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
        padding: .55rem .8rem;
    }
    .member-form-card-body .form-control:focus,
    .member-form-card-body .form-select:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
    .required-mark { color: #dc3545; }

    .member-summary-box .summary-label {
        font-size: .76rem;
        color: #868e96;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .member-summary-box .summary-value {
        font-weight: 700;
        color: #212529;
        margin-bottom: .9rem;
    }
    .member-summary-box .summary-value.text-muted-value { color: #adb5bd; font-weight: 500; }
    .member-summary-note {
        font-size: .8rem;
        color: #868e96;
    }
    .current-cover-preview {
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 10px;
        padding: .4rem;
        background: #fafafa;
        display: inline-block;
    }
</style>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php
        if ($_GET['error'] == 'invalid_type') echo "Apenas arquivos PDF são permitidos.";
        elseif ($_GET['error'] == 'invalid_cover') echo "A capa deve ser uma imagem (JPG, PNG ou WEBP).";
        elseif ($_GET['error'] == 'upload_failed') echo "Falha ao enviar o arquivo.";
        else echo "Ocorreu um erro.";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php
$baseName = pathinfo((string)($study['file_path'] ?? ''), PATHINFO_FILENAME);
$coverUrl = null;
if ($baseName !== '') {
    $coverDir = __DIR__ . '/../../../../public/uploads/studies/covers/';
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $candidate = $coverDir . $baseName . '.' . $ext;
        if (is_file($candidate)) {
            $coverUrl = '/uploads/studies/covers/' . $baseName . '.' . $ext;
            break;
        }
    }
}
$studyFilePath = __DIR__ . '/../../../../public/uploads/studies/' . ($study['file_path'] ?? '');
$studyFileExists = !empty($study['file_path']) && is_file($studyFilePath);
?>

<div class="row">
<div class="col-lg-8">
<form action="/admin/studies/edit/<?= (int)$study['id'] ?>" method="POST" enctype="multipart/form-data" class="app-form-with-bottom-actions" id="studyEditForm">
    <?= csrf_field() ?>

    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-book"></i></div>
            <div>
                <div class="member-form-card-title">Dados do Estudo</div>
                <div class="member-form-card-subtitle">Título, arquivo PDF e visibilidade.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Título <span class="required-mark">*</span></label>
                    <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($study['title'] ?? '') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tipo de Material <span class="required-mark">*</span></label>
                    <select name="material_type" class="form-select" required>
                        <?php foreach ($materialTypes as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= ((string)($study['material_type'] ?? 'Estudo') === (string)$value) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Descrição (Opcional)</label>
                    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($study['description'] ?? '') ?></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Arquivo atual</label>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <?php if ($studyFileExists): ?>
                            <a href="/admin/studies/view/<?= (int)$study['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
                                <i class="fas fa-file-pdf me-1"></i> Abrir PDF
                            </a>
                        <?php else: ?>
                            <span class="badge bg-danger rounded-pill px-3 py-2">
                                <i class="fas fa-exclamation-triangle me-1"></i> Arquivo ausente no servidor
                            </span>
                        <?php endif; ?>
                        <span class="text-muted small"><?= htmlspecialchars($study['file_path'] ?? '') ?></span>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Substituir arquivo (PDF)</label>
                    <input type="file" name="file" class="form-control" accept="application/pdf">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Capa</label>
                    <div class="d-flex gap-3 align-items-start">
                        <?php if ($coverUrl): ?>
                            <div class="current-cover-preview">
                                <img src="<?= htmlspecialchars($coverUrl) ?>" alt="Capa" style="width: 56px; height: 74px; object-fit: contain; border-radius: 6px;">
                            </div>
                        <?php endif; ?>
                        <div class="flex-grow-1">
                            <input type="file" name="cover" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <div class="form-text">Se enviar, a capa substituirá a atual.</div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">Visibilidade</label>
                    <select name="congregation_id" class="form-select">
                        <option value="" <?= empty($study['congregation_id']) ? 'selected' : '' ?>>Geral (Visível para todos os membros)</option>
                        <?php foreach ($congregations as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ((string)($study['congregation_id'] ?? '') === (string)$c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Selecione uma congregação para restringir o acesso ou deixe "Geral" para todos.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5 d-md-none">
        <a href="/admin/studies" class="btn btn-outline-secondary px-4">Cancelar</a>
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
    </div>
</form>
</div>

<div class="col-lg-4">
    <div class="member-summary-box sticky-top" style="top: 1rem; z-index: 10;">
        <div class="member-form-card">
            <div class="member-form-card-body">
                <div class="fw-bold mb-3">Resumo</div>

                <div class="summary-label">Título</div>
                <div class="summary-value" id="summaryTitle"><?= htmlspecialchars($study['title'] ?: '—') ?></div>

                <div class="summary-label">Arquivo</div>
                <div class="summary-value">
                    <?php if ($studyFileExists): ?>
                        <span class="text-success"><i class="fas fa-check-circle me-1"></i> Disponível</span>
                    <?php else: ?>
                        <span class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Ausente</span>
                    <?php endif; ?>
                </div>

                <div class="summary-label">Visibilidade</div>
                <div class="summary-value mb-2" id="summaryVisibility">
                    <?php
                        $visName = 'Geral (Todas)';
                        if (!empty($study['congregation_id'])) {
                            foreach ($congregations as $c) {
                                if ($c['id'] == $study['congregation_id']) { $visName = $c['name']; break; }
                            }
                        }
                        echo htmlspecialchars($visName);
                    ?>
                </div>

                <hr>
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Preenchimento</span>
                    <span id="summaryProgressPct">0%</span>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-dark" id="summaryProgressBar" style="width: 0%"></div>
                </div>
            </div>
        </div>
        <div class="member-form-card">
            <div class="member-form-card-body member-summary-note">
                Campos marcados com <span class="required-mark">*</span> são obrigatórios.
            </div>
        </div>
    </div>
</div>
</div>

<script>
    const studyForm = document.getElementById('studyEditForm');
    const summaryTitle = document.getElementById('summaryTitle');
    const summaryVisibility = document.getElementById('summaryVisibility');
    const summaryProgressPct = document.getElementById('summaryProgressPct');
    const summaryProgressBar = document.getElementById('summaryProgressBar');
    const congregationSelect = document.querySelector('select[name="congregation_id"]');

    function updateStudySummary() {
        const titleVal = studyForm.querySelector('[name="title"]').value.trim();
        summaryTitle.textContent = titleVal || '—';

        const congOption = congregationSelect.options[congregationSelect.selectedIndex];
        summaryVisibility.textContent = (congOption && congOption.value) ? congOption.text : 'Geral (Todas)';

        const requiredFields = Array.from(studyForm.querySelectorAll('[required]'));
        const filled = requiredFields.filter(f => f.value && f.value.trim() !== '').length;
        const pct = requiredFields.length ? Math.round((filled / requiredFields.length) * 100) : 0;
        summaryProgressPct.textContent = pct + '%';
        summaryProgressBar.style.width = pct + '%';
    }

    studyForm.addEventListener('input', updateStudySummary);
    studyForm.addEventListener('change', updateStudySummary);
    updateStudySummary();
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
