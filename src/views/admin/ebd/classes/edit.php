<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/ebd/classes" class="text-decoration-none">EBD</a></li>
                <li class="breadcrumb-item active">Editar Classe</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Editar Classe EBD</h1>
    </div>
    <div class="d-none d-md-flex gap-2">
        <a href="/admin/ebd/classes/show/<?= $class['id'] ?>" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">Cancelar</a>
        <button type="submit" form="ebdClassEditForm" class="btn btn-dark rounded-pill fw-semibold px-3">Salvar</button>
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

<div class="row">
<div class="col-lg-8">
<form action="/admin/ebd/classes/edit/<?= $class['id'] ?>" method="POST" class="app-form-with-bottom-actions" id="ebdClassEditForm">
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
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($class['name']) ?>" required>
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Descrição</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars((string)$class['description']) ?></textarea>
                </div>

                <div class="col-md-6">
                    <label for="min_age" class="form-label">Idade Mínima</label>
                    <input type="number" class="form-control" id="min_age" name="min_age" value="<?= $class['min_age'] ?>" min="0">
                </div>
                <div class="col-md-6">
                    <label for="max_age" class="form-label">Idade Máxima</label>
                    <input type="number" class="form-control" id="max_age" name="max_age" value="<?= $class['max_age'] ?>" min="0">
                </div>

                <div class="col-md-6">
                    <label for="congregation_id" class="form-label">Congregação</label>
                    <select class="form-select" id="congregation_id" name="congregation_id">
                        <?php if (empty($_SESSION['user_congregation_id'])): ?>
                            <option value="">Global (Todas)</option>
                        <?php endif; ?>
                        <?php foreach ($congregations as $cong): ?>
                            <option value="<?= $cong['id'] ?>" <?= $class['congregation_id'] == $cong['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cong['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" <?= $class['status'] == 'active' ? 'selected' : '' ?>>Ativa</option>
                        <option value="inactive" <?= $class['status'] == 'inactive' ? 'selected' : '' ?>>Inativa</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5 d-md-none">
        <a href="/admin/ebd/classes/show/<?= $class['id'] ?>" class="btn btn-outline-secondary px-4">Cancelar</a>
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
    </div>
</form>
</div>

<div class="col-lg-4">
    <div class="member-summary-box sticky-top" style="top: 1rem; z-index: 10;">
        <div class="member-form-card">
            <div class="member-form-card-body">
                <div class="fw-bold mb-3">Resumo</div>

                <div class="summary-label">Nome</div>
                <div class="summary-value" id="summaryName"><?= htmlspecialchars($class['name'] ?: '—') ?></div>

                <?php
                    $summaryCongName = 'Global (Todas)';
                    if (!empty($class['congregation_id'])) {
                        foreach ($congregations as $cong) {
                            if ($cong['id'] == $class['congregation_id']) {
                                $summaryCongName = $cong['name'];
                                break;
                            }
                        }
                    }
                ?>
                <div class="summary-label">Congregação</div>
                <div class="summary-value" id="summaryCongregation"><?= htmlspecialchars($summaryCongName) ?></div>

                <div class="summary-label">Faixa Etária</div>
                <div class="summary-value" id="summaryAgeRange"><?= ($class['min_age'] || $class['max_age']) ? ($class['min_age'] ?? 0) . ' a ' . ($class['max_age'] ?? 99) . ' anos' : 'Livre' ?></div>

                <div class="summary-label">Status</div>
                <div class="summary-value mb-2" id="summaryStatus"><?= $class['status'] == 'active' ? 'Ativa' : 'Inativa' ?></div>

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
    const ebdForm = document.getElementById('ebdClassEditForm');
    const summaryName = document.getElementById('summaryName');
    const summaryCongregation = document.getElementById('summaryCongregation');
    const summaryAgeRange = document.getElementById('summaryAgeRange');
    const summaryStatus = document.getElementById('summaryStatus');
    const summaryProgressPct = document.getElementById('summaryProgressPct');
    const summaryProgressBar = document.getElementById('summaryProgressBar');
    const congregationSelect = document.getElementById('congregation_id');
    const minAgeInput = document.getElementById('min_age');
    const maxAgeInput = document.getElementById('max_age');
    const statusSelect = document.getElementById('status');

    function updateEbdSummary() {
        summaryName.textContent = ebdForm.querySelector('[name="name"]').value.trim() || '—';

        const congOption = congregationSelect.options[congregationSelect.selectedIndex];
        summaryCongregation.textContent = (congOption && congOption.value) ? congOption.text : 'Global (Todas)';

        const minVal = minAgeInput.value;
        const maxVal = maxAgeInput.value;
        summaryAgeRange.textContent = (minVal || maxVal) ? `${minVal || 0} a ${maxVal || 99} anos` : 'Livre';

        summaryStatus.textContent = statusSelect.value === 'inactive' ? 'Inativa' : 'Ativa';

        const requiredFields = Array.from(ebdForm.querySelectorAll('[required]'));
        const filled = requiredFields.filter(f => f.value && f.value.trim() !== '').length;
        const pct = requiredFields.length ? Math.round((filled / requiredFields.length) * 100) : 0;
        summaryProgressPct.textContent = pct + '%';
        summaryProgressBar.style.width = pct + '%';
    }

    ebdForm.addEventListener('input', updateEbdSummary);
    ebdForm.addEventListener('change', updateEbdSummary);
    updateEbdSummary();
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
