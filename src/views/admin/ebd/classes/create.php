<?php $suppressMobileTopbar = true; include __DIR__ . '/../../../layout/header.php'; ?>

<div class="member-form-topbar d-none d-lg-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/ebd/classes" class="text-decoration-none">EBD</a></li>
                <li class="breadcrumb-item active">Nova Classe</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Nova Classe EBD</h1>
    </div>
    <div class="d-none d-lg-flex gap-2">
        <a href="/admin/ebd/classes" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">Cancelar</a>
        <button type="submit" form="ebdClassCreateForm" class="btn btn-dark rounded-pill fw-semibold px-3">Salvar</button>
    </div>
</div>

<div class="d-lg-none mb-2">
    <?php
    $mobilePageCategory = 'Ensino';
    $mobilePageTitle = 'Nova Classe';
    include __DIR__ . '/../../../layout/mobile_page_header.php';
    ?>
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

    .ecm-select-btn { display: flex; align-items: center; justify-content: space-between; gap: .5rem; width: 100%; height: 44px; border-radius: 12px; border: 1px solid rgba(17,24,39,.08); background: #f8f9fb; color: #16213e; font-size: .86rem; font-weight: 600; padding: 0 .9rem; text-align: left; }
    .ecm-select-btn .ecm-select-label { display: flex; align-items: center; gap: .5rem; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ecm-select-btn .ecm-select-label i:first-child { color: #8b93a7; flex: 0 0 auto; }
    .ecm-select-btn .fa-chevron-down { color: #adb5bd; flex: 0 0 auto; font-size: .78rem; }

    .ecm-cong-sheet.offcanvas-bottom { border-top-left-radius: 20px; border-top-right-radius: 20px; height: auto; max-height: 85vh; }
    .ecm-pick-row { display: flex; align-items: center; gap: .6rem; width: 100%; text-align: left; border: 1px solid rgba(17,24,39,.08); background: #fff; color: #16213e; font-size: .86rem; font-weight: 600; padding: .7rem .9rem; border-radius: 12px; margin-bottom: .5rem; }
    .ecm-pick-row i:first-child { color: #8b93a7; }
    .ecm-pick-row.active { border-color: #2563eb; background: rgba(37,99,235,.06); color: #2563eb; }
    .ecm-pick-row.active i:first-child { color: #2563eb; }
    .ecm-pick-row .fa-check { margin-left: auto; color: #2563eb; display: none; }
    .ecm-pick-row.active .fa-check { display: inline; }

    @media (max-width: 991.98px) {
        .member-form-card-body .form-control,
        .member-form-card-body .form-select {
            height: 44px;
            border-radius: 12px;
            border-color: rgba(17,24,39,.08);
            background: #f8f9fb;
        }
        .member-form-card-body textarea.form-control { height: auto; }
        .member-form-card-body .form-control:focus,
        .member-form-card-body .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 .2rem rgba(37,99,235,.1);
            background: #fff;
        }
        .member-form-card-body .row.g-3 { row-gap: 1.1rem !important; }
        .progress-bar.bg-dark { background-color: #16a34a !important; }
        .progress { height: 4px !important; background: #eef0f2; }
    }
</style>

<div class="row">
<div class="col-12 col-lg-8">
<form action="/admin/ebd/classes/create" method="POST" class="app-form-with-bottom-actions" id="ebdClassCreateForm">
    <?= csrf_field() ?>

    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-book-reader"></i></div>
            <div>
                <div class="member-form-card-title">Dados da Classe</div>
                <div class="member-form-card-subtitle">Nome, faixa etária e congregação.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label for="name" class="form-label">Nome da Classe <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Ex: Jovens, Adultos, Crianças" required>
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Descrição</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>

                <div class="col-6">
                    <label for="min_age" class="form-label">Idade Mínima</label>
                    <input type="number" class="form-control" id="min_age" name="min_age" min="0">
                </div>
                <div class="col-6">
                    <label for="max_age" class="form-label">Idade Máxima</label>
                    <input type="number" class="form-control" id="max_age" name="max_age" min="0">
                </div>

                <div class="col-12">
                    <label for="congregation_id" class="form-label d-none d-lg-block">Congregação</label>
                    <select class="form-select d-none d-lg-block" id="congregation_id" name="congregation_id">
                        <?php if (empty($_SESSION['user_congregation_id'])): ?>
                            <option value="">Global (Todas)</option>
                        <?php endif; ?>
                        <?php foreach ($congregations as $cong): ?>
                            <option value="<?= $cong['id'] ?>"><?= htmlspecialchars($cong['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text d-none d-lg-block">Selecione se a classe for específica de uma congregação.</div>

                    <div class="d-lg-none">
                        <label class="form-label">Congregação</label>
                        <button type="button" class="ecm-select-btn" data-bs-toggle="offcanvas" data-bs-target="#ecmCongSheet">
                            <span class="ecm-select-label" id="ecmCongLabel"><i class="fas fa-globe"></i><span>Global (Todas)</span></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5 d-lg-none">
        <a href="/admin/ebd/classes" class="btn btn-outline-secondary px-4">Cancelar</a>
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
    </div>
</form>
</div>

<div class="col-lg-4 d-none d-lg-block">
    <div class="member-summary-box sticky-top" style="top: 1rem; z-index: 10;">
        <div class="member-form-card">
            <div class="member-form-card-body">
                <div class="fw-bold mb-3">Resumo</div>

                <div class="summary-label">Nome</div>
                <div class="summary-value text-muted-value" id="summaryName">—</div>

                <div class="summary-label">Congregação</div>
                <div class="summary-value text-muted-value" id="summaryCongregation">Global (Todas)</div>

                <div class="summary-label">Faixa Etária</div>
                <div class="summary-value mb-2" id="summaryAgeRange">Livre</div>

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

<div class="offcanvas offcanvas-bottom ecm-cong-sheet" tabindex="-1" id="ecmCongSheet">
    <div class="offcanvas-header">
        <h6 class="offcanvas-title fw-bold">Congregação</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body">
        <?php if (empty($_SESSION['user_congregation_id'])): ?>
            <button type="button" class="ecm-pick-row active" data-value="" data-icon="fa-globe">
                <i class="fas fa-globe"></i> Global (Todas) <i class="fas fa-check"></i>
            </button>
        <?php endif; ?>
        <?php foreach ($congregations as $cong): ?>
            <button type="button" class="ecm-pick-row" data-value="<?= $cong['id'] ?>" data-icon="fa-church">
                <i class="fas fa-church"></i> <?= htmlspecialchars($cong['name']) ?> <i class="fas fa-check"></i>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<script>
    const ebdForm = document.getElementById('ebdClassCreateForm');
    const summaryName = document.getElementById('summaryName');
    const summaryCongregation = document.getElementById('summaryCongregation');
    const summaryAgeRange = document.getElementById('summaryAgeRange');
    const summaryProgressPct = document.getElementById('summaryProgressPct');
    const summaryProgressBar = document.getElementById('summaryProgressBar');
    const congregationSelect = document.getElementById('congregation_id');
    const minAgeInput = document.getElementById('min_age');
    const maxAgeInput = document.getElementById('max_age');

    function updateEbdSummary() {
        const nameVal = ebdForm.querySelector('[name="name"]').value.trim();
        summaryName.textContent = nameVal || '—';
        summaryName.classList.toggle('text-muted-value', !nameVal);

        const congOption = congregationSelect.options[congregationSelect.selectedIndex];
        summaryCongregation.textContent = (congOption && congOption.value) ? congOption.text : 'Global (Todas)';
        summaryCongregation.classList.toggle('text-muted-value', !(congOption && congOption.value));

        const minVal = minAgeInput.value;
        const maxVal = maxAgeInput.value;
        if (minVal || maxVal) {
            summaryAgeRange.textContent = `${minVal || 0} a ${maxVal || 99} anos`;
        } else {
            summaryAgeRange.textContent = 'Livre';
        }

        const requiredFields = Array.from(ebdForm.querySelectorAll('[required]'));
        const filled = requiredFields.filter(f => f.value && f.value.trim() !== '').length;
        const pct = requiredFields.length ? Math.round((filled / requiredFields.length) * 100) : 0;
        summaryProgressPct.textContent = pct + '%';
        summaryProgressBar.style.width = pct + '%';
    }

    ebdForm.addEventListener('input', updateEbdSummary);
    ebdForm.addEventListener('change', updateEbdSummary);
    updateEbdSummary();

    var ecmCongSheet = document.getElementById('ecmCongSheet');
    var ecmCongLabel = document.getElementById('ecmCongLabel');
    if (ecmCongSheet) {
        ecmCongSheet.querySelectorAll('.ecm-pick-row').forEach(function (row) {
            row.addEventListener('click', function () {
                ecmCongSheet.querySelectorAll('.ecm-pick-row').forEach(function (r) { r.classList.remove('active'); });
                row.classList.add('active');
                congregationSelect.value = row.getAttribute('data-value');
                congregationSelect.dispatchEvent(new Event('change'));
                ecmCongLabel.innerHTML = '<i class="fas ' + row.getAttribute('data-icon') + '"></i><span>' + row.textContent.trim().replace(/\s*$/, '') + '</span>';
                var inst = bootstrap.Offcanvas.getInstance(ecmCongSheet);
                if (inst) inst.hide();
            });
        });
    }
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
