<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/financial/bank-accounts" class="text-decoration-none">Contas e Caixas</a></li>
                <li class="breadcrumb-item active">Nova</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Nova Conta/Caixa</h1>
    </div>
    <div class="d-none d-md-flex gap-2">
        <a href="/admin/financial/bank-accounts" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">Cancelar</a>
        <button type="submit" form="bankAccountCreateForm" class="btn btn-dark rounded-pill fw-semibold px-3">Salvar</button>
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
<form action="/admin/financial/bank-accounts/store" method="POST" class="app-form-with-bottom-actions" id="bankAccountCreateForm">
    <?= csrf_field() ?>

    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-wallet"></i></div>
            <div>
                <div class="member-form-card-title">Dados da Conta</div>
                <div class="member-form-card-subtitle">Identificação, tipo e dados bancários.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nome da Conta <span class="required-mark">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="Ex: Bradesco, Caixa Físico Sede">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tipo <span class="required-mark">*</span></label>
                    <select name="type" class="form-select" id="accountType" required>
                        <option value="caixa">Caixa Físico</option>
                        <option value="conta_corrente" selected>Conta Corrente</option>
                        <option value="poupanca">Poupança</option>
                        <option value="investimento">Investimento</option>
                        <option value="centro_custo">Centro de Custo</option>
                    </select>
                </div>

                <div class="col-md-4 bank-field">
                    <label class="form-label">Nome do Banco</label>
                    <input type="text" name="bank_name" class="form-control" placeholder="Ex: Bradesco, Itaú">
                </div>

                <div class="col-md-4 bank-field">
                    <label class="form-label">Agência</label>
                    <input type="text" name="agency" class="form-control" placeholder="Ex: 1234-5">
                </div>

                <div class="col-md-4 bank-field">
                    <label class="form-label">Número da Conta</label>
                    <input type="text" name="account_number" class="form-control" placeholder="Ex: 12345-6">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Saldo Inicial (R$)</label>
                    <input type="number" step="0.01" name="initial_balance" class="form-control" value="0.00">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" selected>Ativa</option>
                        <option value="inactive">Inativa</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5 d-md-none">
        <a href="/admin/financial/bank-accounts" class="btn btn-outline-secondary px-4">Cancelar</a>
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
                <div class="summary-value text-muted-value" id="summaryName">—</div>

                <div class="summary-label">Tipo</div>
                <div class="summary-value" id="summaryType"></div>

                <div class="summary-label">Saldo Inicial</div>
                <div class="summary-value mb-2" id="summaryBalance">R$ 0,00</div>

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
    const accountForm = document.getElementById('bankAccountCreateForm');
    const accountTypeSelect = document.getElementById('accountType');
    const bankFields = document.querySelectorAll('.bank-field');
    const summaryName = document.getElementById('summaryName');
    const summaryType = document.getElementById('summaryType');
    const summaryBalance = document.getElementById('summaryBalance');
    const summaryProgressPct = document.getElementById('summaryProgressPct');
    const summaryProgressBar = document.getElementById('summaryProgressBar');

    function toggleBankFields() {
        const hide = accountTypeSelect.value === 'caixa' || accountTypeSelect.value === 'centro_custo';
        bankFields.forEach(function (field) {
            field.style.display = hide ? 'none' : '';
        });
    }

    function formatCurrency(value) {
        const num = parseFloat(value || 0);
        return 'R$ ' + num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateSummary() {
        const nameVal = accountForm.querySelector('[name="name"]').value.trim();
        summaryName.textContent = nameVal || '—';
        summaryName.classList.toggle('text-muted-value', !nameVal);

        const typeOption = accountTypeSelect.options[accountTypeSelect.selectedIndex];
        summaryType.textContent = typeOption ? typeOption.text : '—';

        summaryBalance.textContent = formatCurrency(accountForm.querySelector('[name="initial_balance"]').value);

        const requiredFields = Array.from(accountForm.querySelectorAll('[required]'));
        const filled = requiredFields.filter(f => f.value && f.value.trim() !== '').length;
        const pct = requiredFields.length ? Math.round((filled / requiredFields.length) * 100) : 0;
        summaryProgressPct.textContent = pct + '%';
        summaryProgressBar.style.width = pct + '%';
    }

    accountTypeSelect.addEventListener('change', function () {
        toggleBankFields();
        updateSummary();
    });
    accountForm.addEventListener('input', updateSummary);
    accountForm.addEventListener('change', updateSummary);

    toggleBankFields();
    updateSummary();
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
