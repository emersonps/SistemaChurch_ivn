<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/expenses" class="text-decoration-none">Saídas / Despesas</a></li>
                <li class="breadcrumb-item active">Nova</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Nova Saída</h1>
    </div>
    <div class="d-none d-md-flex gap-2">
        <a href="/admin/expenses" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">Cancelar</a>
        <button type="submit" form="expenseCreateForm" class="btn btn-dark rounded-pill fw-semibold px-3">Salvar</button>
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
<form action="/admin/expenses/store" method="POST" class="app-form-with-bottom-actions" id="expenseCreateForm">
    <?= csrf_field() ?>

    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-money-bill-wave"></i></div>
            <div>
                <div class="member-form-card-title">Dados da Saída</div>
                <div class="member-form-card-subtitle">Descrição, valor, data e origem financeira.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Descrição <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" name="description" required placeholder="Ex: Pagamento de Energia">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Valor (R$) <span class="required-mark">*</span></label>
                    <input type="number" step="0.01" class="form-control" name="amount" required placeholder="0.00">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Data <span class="required-mark">*</span></label>
                    <input type="date" class="form-control" name="expense_date" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Conta de Saída <span class="required-mark">*</span></label>
                    <select name="bank_account_id" id="bankAccountSelect" class="form-select" required>
                        <?php foreach ($bankAccounts as $bank): ?>
                            <option value="<?= $bank['id'] ?>"><?= htmlspecialchars($bank['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Categoria Contábil</label>
                    <select name="chart_account_id" id="chartAccountSelect" class="form-select">
                        <option value="">-- Automático --</option>
                        <?php foreach ($chartAccounts as $chart): ?>
                            <option value="<?= $chart['id'] ?>"><?= $chart['code'] ?> - <?= htmlspecialchars($chart['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Plano de Contas</label>
                    <select id="accountSetSelect" class="form-select">
                        <option value="">-- Padrão da Congregação --</option>
                    </select>
                </div>

                <?php if (!empty($hasAccountableField)): ?>
                <div class="col-12">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="isAccountableInput" name="is_accountable" value="1" checked>
                        <label class="form-check-label" for="isAccountableInput">Contabilizar esta saída</label>
                    </div>
                    <div class="form-text">Se desmarcado, a saída ficará registrada, mas não entrará na contabilidade, relatórios, fechamentos e saldos.</div>
                </div>
                <?php endif; ?>

                <div class="col-md-8">
                    <label class="form-label">Categoria Antiga (Legado) <span class="required-mark">*</span></label>
                    <select class="form-select" name="category" required>
                        <option value="Manutenção">Manutenção</option>
                        <option value="Contas Fixas">Contas Fixas (Água, Luz, Net)</option>
                        <option value="Eventos">Eventos</option>
                        <option value="Ajuda de Custo">Ajuda de Custo</option>
                        <option value="Missões">Missões</option>
                        <option value="Material de Limpeza">Material de Limpeza</option>
                        <option value="Outros">Outros</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Congregação <span class="required-mark">*</span></label>
                    <select class="form-select" name="congregation_id" id="congregationSelect" required>
                        <?php foreach ($congregations as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea class="form-control" name="notes" rows="3"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5 d-md-none">
        <a href="/admin/expenses" class="btn btn-outline-secondary px-4">Cancelar</a>
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
    </div>
</form>
</div>

<div class="col-lg-4">
    <div class="member-summary-box sticky-top" style="top: 1rem; z-index: 10;">
        <div class="member-form-card">
            <div class="member-form-card-body">
                <div class="fw-bold mb-3">Resumo</div>

                <div class="summary-label">Descrição</div>
                <div class="summary-value text-muted-value" id="summaryDescription">—</div>

                <div class="summary-label">Categoria</div>
                <div class="summary-value" id="summaryCategory"></div>

                <div class="summary-label">Valor</div>
                <div class="summary-value mb-2 text-danger" id="summaryAmount">R$ 0,00</div>

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
const congSel = document.getElementById('congregationSelect');
const accSel = document.getElementById('chartAccountSelect');
const setSel = document.getElementById('accountSetSelect');
async function loadAccounts() {
    const cid = congSel.value;
    const setId = setSel.value;
    accSel.innerHTML = '<option value=\"\">-- Automático --</option>';
    try {
        const url = setId ? `/api/financial/chart-accounts?type=expense&set_id=${encodeURIComponent(setId)}` : `/api/financial/chart-accounts?type=expense&congregation_id=${encodeURIComponent(cid)}`;
        const res = await fetch(url);
        const data = await res.json();
        if (data && Array.isArray(data.accounts)) {
            data.accounts.forEach(a => {
                const opt = document.createElement('option');
                opt.value = a.id;
                opt.textContent = `${a.code} - ${a.name}`;
                accSel.appendChild(opt);
            });
        }
    } catch (e) {}
}
async function loadSets() {
    const cid = congSel.value;
    setSel.innerHTML = '<option value=\"\">-- Padrão da Congregação --</option>';
    try {
        const res = await fetch(`/api/financial/account-sets?congregation_id=${encodeURIComponent(cid)}`);
        const data = await res.json();
        if (data && Array.isArray(data.sets)) {
            let defaultId = '';
            data.sets.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = `${s.name}${s.is_default ? ' (Padrão)' : ''}`;
                setSel.appendChild(opt);
                if (s.is_default && !defaultId) defaultId = s.id;
            });
            if (defaultId) setSel.value = defaultId;
        }
    } catch (e) {}
}
congSel.addEventListener('change', loadAccounts);
congSel.addEventListener('change', loadSets);
setSel.addEventListener('change', loadAccounts);
document.addEventListener('DOMContentLoaded', loadAccounts);
document.addEventListener('DOMContentLoaded', loadSets);

function toggleAccountingFields() {
    const accountable = document.getElementById('isAccountableInput');
    if (!accountable) return;
    const disabled = !accountable.checked;
    const bank = document.getElementById('bankAccountSelect');
    const chart = document.getElementById('chartAccountSelect');
    const plan = document.getElementById('accountSetSelect');
    if (bank) {
        bank.disabled = disabled;
        bank.required = !disabled;
        if (disabled) bank.value = '';
    }
    if (chart) {
        chart.disabled = disabled;
        if (disabled) chart.value = '';
    }
    if (plan) {
        plan.disabled = disabled;
        if (disabled) plan.value = '';
    }
}
document.getElementById('isAccountableInput')?.addEventListener('change', toggleAccountingFields);
toggleAccountingFields();

const expenseForm = document.getElementById('expenseCreateForm');
const summaryDescription = document.getElementById('summaryDescription');
const summaryCategory = document.getElementById('summaryCategory');
const summaryAmount = document.getElementById('summaryAmount');
const summaryProgressPct = document.getElementById('summaryProgressPct');
const summaryProgressBar = document.getElementById('summaryProgressBar');
const categorySelect = expenseForm.querySelector('[name="category"]');

function formatCurrency(value) {
    const num = parseFloat(value || 0);
    return 'R$ ' + num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function updateSummary() {
    const descVal = expenseForm.querySelector('[name="description"]').value.trim();
    summaryDescription.textContent = descVal || '—';
    summaryDescription.classList.toggle('text-muted-value', !descVal);

    const catOption = categorySelect.options[categorySelect.selectedIndex];
    summaryCategory.textContent = catOption ? catOption.text : '—';

    summaryAmount.textContent = formatCurrency(expenseForm.querySelector('[name="amount"]').value);

    const requiredFields = Array.from(expenseForm.querySelectorAll('[required]')).filter(f => !f.disabled);
    const filled = requiredFields.filter(f => f.value && f.value.trim() !== '').length;
    const pct = requiredFields.length ? Math.round((filled / requiredFields.length) * 100) : 0;
    summaryProgressPct.textContent = pct + '%';
    summaryProgressBar.style.width = pct + '%';
}

expenseForm.addEventListener('input', updateSummary);
expenseForm.addEventListener('change', updateSummary);
updateSummary();
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
