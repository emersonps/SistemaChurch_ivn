<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/studies" class="text-decoration-none">Estudos</a></li>
                <li class="breadcrumb-item active">Novo</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Novo Estudo / Esboço</h1>
    </div>
    <div class="d-none d-md-flex gap-2">
        <a href="/admin/studies" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">Cancelar</a>
        <button type="submit" form="studyCreateForm" class="btn btn-dark rounded-pill fw-semibold px-3">Salvar</button>
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
</style>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php
        if ($_GET['error'] == 'invalid_type') echo "Apenas arquivos PDF são permitidos.";
        elseif ($_GET['error'] == 'invalid_cover') echo "A capa deve ser uma imagem (JPG, PNG ou WEBP).";
        elseif ($_GET['error'] == 'upload_failed') echo "Falha ao enviar o arquivo.";
        elseif ($_GET['error'] == 'no_file') echo "Nenhum arquivo selecionado.";
        else echo "Ocorreu um erro.";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
<div class="col-lg-8">
<form action="/admin/studies/create" method="POST" enctype="multipart/form-data" class="app-form-with-bottom-actions" id="studyCreateForm">
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
                <div class="col-12">
                    <label class="form-label">Título <span class="required-mark">*</span></label>
                    <input type="text" name="title" class="form-control" required placeholder="Ex: Estudo sobre Oração">
                </div>

                <div class="col-12">
                    <label class="form-label">Descrição (Opcional)</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Arquivo (PDF) <span class="required-mark">*</span></label>
                    <input type="file" name="file" class="form-control" accept="application/pdf" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Capa (Opcional)</label>
                    <input type="file" name="cover" class="form-control" accept="image/png,image/jpeg,image/webp">
                    <div class="form-text">Se enviar, a capa será usada como miniatura do estudo.</div>
                </div>

                <div class="col-12">
                    <label class="form-label">Visibilidade</label>
                    <select name="congregation_id" class="form-select">
                        <option value="">Geral (Visível para todos os membros)</option>
                        <?php foreach ($congregations as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
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
                <div class="summary-value text-muted-value" id="summaryTitle">—</div>

                <div class="summary-label">Arquivo</div>
                <div class="summary-value text-muted-value" id="summaryFile">Nenhum arquivo selecionado</div>

                <div class="summary-label">Visibilidade</div>
                <div class="summary-value mb-2" id="summaryVisibility">Geral (Todas)</div>

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
    const studyForm = document.getElementById('studyCreateForm');
    const summaryTitle = document.getElementById('summaryTitle');
    const summaryFile = document.getElementById('summaryFile');
    const summaryVisibility = document.getElementById('summaryVisibility');
    const summaryProgressPct = document.getElementById('summaryProgressPct');
    const summaryProgressBar = document.getElementById('summaryProgressBar');
    const congregationSelect = document.querySelector('select[name="congregation_id"]');
    const fileInput = document.querySelector('input[name="file"]');

    function updateStudySummary() {
        const titleVal = studyForm.querySelector('[name="title"]').value.trim();
        summaryTitle.textContent = titleVal || '—';
        summaryTitle.classList.toggle('text-muted-value', !titleVal);

        const fileName = fileInput.files.length ? fileInput.files[0].name : '';
        summaryFile.textContent = fileName || 'Nenhum arquivo selecionado';
        summaryFile.classList.toggle('text-muted-value', !fileName);

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
