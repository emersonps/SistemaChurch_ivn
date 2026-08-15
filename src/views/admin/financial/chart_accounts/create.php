<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/financial/chart-accounts" class="text-decoration-none">Plano de Contas</a></li>
                <li class="breadcrumb-item active">Nova</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Nova Conta Contábil</h1>
    </div>
    <div class="d-none d-md-flex gap-2">
        <a href="/admin/financial/chart-accounts" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">Cancelar</a>
        <button type="submit" form="chartAccountCreateForm" class="btn btn-dark rounded-pill fw-semibold px-3">Salvar</button>
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

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?= $_SESSION['flash_error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="row">
<div class="col-lg-8">
<form action="/admin/financial/chart-accounts/store" method="POST" class="app-form-with-bottom-actions" id="chartAccountCreateForm">
    <?= csrf_field() ?>

    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-sitemap"></i></div>
            <div>
                <div class="member-form-card-title">Dados da Conta Contábil</div>
                <div class="member-form-card-subtitle">Plano de contas, código, natureza e estrutura.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Plano de Contas</label>
                    <select name="account_set_id" class="form-select">
                        <?php foreach (($sets ?? []) as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= ($selectedSet ?? 0) == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?> <?= (int)$s['is_default'] === 1 ? '(Padrão)' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Estrutura <span class="required-mark">*</span></label>
                    <select name="structure" id="structureSelect" class="form-select" required>
                        <option value="synthetic">Sintética (Pai)</option>
                        <option value="analytic" selected>Analítica (Filho)</option>
                    </select>
                    <div class="form-text">Sintética não possui conta pai; Analítica deve estar vinculada a uma conta pai.</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Código (Ex: 1.1.2) <span class="required-mark">*</span></label>
                    <input type="text" name="code" id="codeInput" class="form-control" required>
                </div>

                <div class="col-md-5">
                    <label class="form-label">Nome da Conta <span class="required-mark">*</span></label>
                    <input type="text" name="name" id="nameInput" class="form-control" required placeholder="Ex: Despesas com Pessoal">
                </div>

                <div class="col-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label mb-0">Natureza <span class="required-mark">*</span></label>
                        <a href="/admin/financial/chart-account-natures" class="btn btn-sm btn-outline-secondary rounded-pill">
                            <i class="fas fa-tags me-1"></i> Gerenciar
                        </a>
                    </div>
                    <?php if (!empty($hasNatureFeature)): ?>
                        <select name="nature_id" id="natureSelect" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach (($natures ?? []) as $nature): ?>
                                <option value="<?= $nature['id'] ?>" <?= $nature['base_type'] === 'expense' ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($nature['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <select name="type" id="natureSelect" class="form-select" required>
                            <option value="asset">Ativo (Bens e Direitos)</option>
                            <option value="liability">Passivo (Obrigações)</option>
                            <option value="income">Receita (Entradas)</option>
                            <option value="expense" selected>Despesa (Saídas)</option>
                        </select>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Conta Pai (Opcional)</label>
                    <select name="parent_id" id="parentSelect" class="form-select">
                        <option value="">-- Nenhuma (Conta Principal) --</option>
                        <?php foreach ($parents as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['code'] . ' - ' . $p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" selected>Ativa</option>
                        <option value="inactive">Inativa</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Descrição / Observações</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5 d-md-none">
        <a href="/admin/financial/chart-accounts" class="btn btn-outline-secondary px-4">Cancelar</a>
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
    </div>
</form>
</div>

<div class="col-lg-4">
    <div class="member-summary-box sticky-top" style="top: 1rem; z-index: 10;">
        <div class="member-form-card">
            <div class="member-form-card-body">
                <div class="fw-bold mb-3">Resumo</div>

                <div class="summary-label">Código</div>
                <div class="summary-value text-muted-value" id="summaryCode">—</div>

                <div class="summary-label">Nome</div>
                <div class="summary-value text-muted-value" id="summaryName">—</div>

                <div class="summary-label">Natureza</div>
                <div class="summary-value mb-2" id="summaryNature"></div>

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
const chartForm = document.getElementById('chartAccountCreateForm');
const structSel = document.getElementById('structureSelect');
const parentSel = document.getElementById('parentSelect');
const natureSel = document.getElementById('natureSelect');
const summaryCode = document.getElementById('summaryCode');
const summaryName = document.getElementById('summaryName');
const summaryNature = document.getElementById('summaryNature');
const summaryProgressPct = document.getElementById('summaryProgressPct');
const summaryProgressBar = document.getElementById('summaryProgressBar');

function applyStructure() {
    const v = structSel.value;
    if (v === 'synthetic') {
        parentSel.value = '';
        parentSel.setAttribute('disabled', 'disabled');
        parentSel.removeAttribute('required');
    } else {
        parentSel.removeAttribute('disabled');
        parentSel.setAttribute('required', 'required');
    }
}
structSel.addEventListener('change', applyStructure);
applyStructure();

function updateSummary() {
    const codeVal = document.getElementById('codeInput').value.trim();
    summaryCode.textContent = codeVal || '—';
    summaryCode.classList.toggle('text-muted-value', !codeVal);

    const nameVal = document.getElementById('nameInput').value.trim();
    summaryName.textContent = nameVal || '—';
    summaryName.classList.toggle('text-muted-value', !nameVal);

    const natureOption = natureSel.options[natureSel.selectedIndex];
    summaryNature.textContent = (natureOption && natureOption.value) ? natureOption.text : '—';

    const requiredFields = Array.from(chartForm.querySelectorAll('[required]')).filter(f => !f.disabled);
    const filled = requiredFields.filter(f => f.value && f.value.trim() !== '').length;
    const pct = requiredFields.length ? Math.round((filled / requiredFields.length) * 100) : 0;
    summaryProgressPct.textContent = pct + '%';
    summaryProgressBar.style.width = pct + '%';
}

chartForm.addEventListener('input', updateSummary);
chartForm.addEventListener('change', updateSummary);
updateSummary();
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
