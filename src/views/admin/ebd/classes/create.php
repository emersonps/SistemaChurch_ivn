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

    .ecm-add-teacher-btn { display: block; width: 100%; border: 1.5px dashed #d3d8e0; border-radius: 12px; padding: 13px 14px; text-align: center; color: #3b6fef; font-size: .84rem; font-weight: 700; background: transparent; text-decoration: none; }

    .ecm-bottom-cta { padding: 14px 0 22px; }
    .ecm-bottom-cta button { display: block; width: 100%; background: #18a558; color: #fff; text-align: center; font-weight: 700; font-size: .95rem; padding: 15px 0; border-radius: 999px; box-shadow: 0 10px 20px rgba(24,165,88,.3); border: none; }

    @media (max-width: 991.98px) {
        .member-form-card-body .form-label { color: #3b6fef; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
        .member-form-card-body .form-control,
        .member-form-card-body .form-select {
            height: 44px;
            border-radius: 12px;
            border-color: #e3e7ee;
            background: #fff;
            color: #101828;
        }
        .member-form-card-body textarea.form-control { height: auto; }
        .member-form-card-body .form-control::placeholder { color: #c2c8d2; }
        .member-form-card-body .form-control:focus,
        .member-form-card-body .form-select:focus {
            border-color: #18a558;
            box-shadow: 0 0 0 .2rem rgba(24,165,88,.12);
            background: #fff;
        }
        .member-form-card-body .row.g-3 { row-gap: 1.1rem !important; }
        .progress-bar.bg-dark { background-color: #18a558 !important; }
        .progress { height: 4px !important; background: #eef0f2; }
        .d-lg-none .btn-primary { background-color: #18a558; border-color: #18a558; }
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
                    <label for="congregation_id" class="form-label">Congregação</label>
                    <select class="form-select" id="congregation_id" name="congregation_id">
                        <?php if (empty($_SESSION['user_congregation_id'])): ?>
                            <option value="">Global (Todas)</option>
                        <?php endif; ?>
                        <?php foreach ($congregations as $cong): ?>
                            <option value="<?= $cong['id'] ?>"><?= htmlspecialchars($cong['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text d-none d-lg-block">Selecione se a classe for específica de uma congregação.</div>
                </div>

                <div class="col-12 d-lg-none">
                    <button type="button" class="ecm-add-teacher-btn" id="ecmAddTeacherBtn"><i class="fas fa-plus me-1"></i> Adicionar Professor</button>
                </div>
            </div>
        </div>
    </div>

    <div class="d-none d-lg-flex justify-content-end gap-2 mb-5">
        <a href="/admin/ebd/classes" class="btn btn-outline-secondary px-4">Cancelar</a>
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
    </div>
    <div class="ecm-bottom-cta d-lg-none">
        <button type="submit">Salvar Classe</button>
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

    var ecmAddTeacherBtn = document.getElementById('ecmAddTeacherBtn');
    if (ecmAddTeacherBtn) {
        ecmAddTeacherBtn.addEventListener('click', function () {
            Swal.fire({
                icon: 'info',
                title: 'Salve a classe primeiro',
                text: 'Você poderá adicionar professores na tela da classe depois de criá-la.',
                confirmButtonColor: '#18a558',
                confirmButtonText: 'Entendi'
            });
        });
    }
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
