<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/tithes" class="text-decoration-none">Dízimos e Ofertas</a></li>
                <li class="breadcrumb-item active">Novo</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Lançar Dízimo/Oferta</h1>
    </div>
    <div class="d-none d-md-flex gap-2">
        <a href="/admin/tithes" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">Cancelar</a>
        <button type="submit" form="titheCreateForm" class="btn btn-dark rounded-pill fw-semibold px-3">Salvar</button>
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
<form action="/admin/tithes/store" method="POST" class="app-form-with-bottom-actions" id="titheCreateForm">
    <?= csrf_field() ?>

    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-hand-holding-dollar"></i></div>
            <div>
                <div class="member-form-card-title">Dados do Lançamento</div>
                <div class="member-form-card-subtitle">Doador, valor, data e destino financeiro.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nome (Membro ou Visitante) <span class="required-mark">*</span></label>
                    <input type="text" class="form-control" name="giver_name" list="memberList" placeholder="Digite o nome..." onchange="checkMember(this)" autocomplete="off" required>
                    <input type="hidden" name="member_id" id="member_id">
                    <small class="d-none" id="member_feedback"></small>
                </div>

                <datalist id="memberList">
                    <?php foreach ($members as $m): ?>
                        <option value="<?= htmlspecialchars($m['name']) ?>"></option>
                    <?php endforeach; ?>
                </datalist>

                <div class="col-md-3">
                    <label class="form-label">Congregação</label>
                    <select class="form-select" name="congregation_id" id="congregationSelect" <?= !empty($_SESSION['user_congregation_id']) ? 'disabled' : '' ?>>
                        <?php if (empty($_SESSION['user_congregation_id'])): ?>
                            <option value="">Selecione...</option>
                        <?php endif; ?>
                        <?php foreach ($congregations as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (!empty($_SESSION['user_congregation_id']) && $_SESSION['user_congregation_id'] == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($_SESSION['user_congregation_id'])): ?>
                        <input type="hidden" name="congregation_id" value="<?= $_SESSION['user_congregation_id'] ?>">
                    <?php endif; ?>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tipo <span class="required-mark">*</span></label>
                    <select class="form-select" name="type" id="typeSelect" required>
                        <option value="Dízimo" selected>Dízimo</option>
                        <option value="Oferta">Oferta</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Valor (R$) <span class="required-mark">*</span></label>
                    <input type="number" step="0.01" class="form-control" name="amount" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Data <span class="required-mark">*</span></label>
                    <input type="date" class="form-control" name="payment_date" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Método</label>
                    <select class="form-select" name="payment_method">
                        <option value="Dinheiro">Dinheiro</option>
                        <option value="PIX">PIX</option>
                        <option value="Cartão">Cartão</option>
                        <option value="Transferência">Transferência</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Conta de Destino</label>
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
                        <label class="form-check-label" for="isAccountableInput">Contabilizar esta entrada</label>
                    </div>
                    <div class="form-text">Se desmarcado, o lançamento ficará apenas registrado, sem entrar em relatórios financeiros, fechamentos e saldos.</div>
                </div>
                <?php endif; ?>

                <div class="col-md-8">
                    <label class="form-label">Observações</label>
                    <input type="text" class="form-control" name="notes">
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5 d-md-none">
        <a href="/admin/tithes" class="btn btn-outline-secondary px-4">Cancelar</a>
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
    </div>
</form>
</div>

<div class="col-lg-4">
    <div class="member-summary-box sticky-top" style="top: 1rem; z-index: 10;">
        <div class="member-form-card">
            <div class="member-form-card-body">
                <div class="fw-bold mb-3">Resumo</div>

                <div class="summary-label">Doador</div>
                <div class="summary-value text-muted-value" id="summaryGiver">—</div>

                <div class="summary-label">Tipo</div>
                <div class="summary-value" id="summaryType">Dízimo</div>

                <div class="summary-label">Valor</div>
                <div class="summary-value mb-2 text-success" id="summaryAmount">R$ 0,00</div>

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
    const membersData = <?php echo json_encode($members); ?>;
    const congSel = document.getElementById('congregationSelect');
    const accSel = document.getElementById('chartAccountSelect');
    const setSel = document.getElementById('accountSetSelect');
    async function loadAccounts() {
        const cid = congSel.value;
        const setId = setSel.value;
        accSel.innerHTML = '<option value=\"\">-- Automático --</option>';
        try {
            const url = setId ? `/api/financial/chart-accounts?type=income&set_id=${encodeURIComponent(setId)}` : `/api/financial/chart-accounts?type=income&congregation_id=${encodeURIComponent(cid)}`;
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
    if (congSel) {
        congSel.addEventListener('change', loadAccounts);
        congSel.addEventListener('change', loadSets);
    }
    setSel.addEventListener('change', loadAccounts);
    document.addEventListener('DOMContentLoaded', () => {
        if (congSel) {
            loadSets().then(loadAccounts);
        }
    });

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

    function checkMember(input) {
        const val = input.value;
        const hiddenId = document.getElementById('member_id');
        const feedback = document.getElementById('member_feedback');

        hiddenId.value = '';
        feedback.className = 'd-none';

        const person = membersData.find(m => m.name === val);

        if (person) {
            if (person.type === 'member') {
                hiddenId.value = person.id;
                feedback.className = 'text-success';
                feedback.innerHTML = '<i class="fas fa-check-circle"></i> Membro identificado';
            } else {
                hiddenId.value = '';
                feedback.className = 'text-info';
                feedback.innerHTML = '<i class="fas fa-history"></i> Visitante frequente';
            }
            feedback.classList.remove('d-none');
        } else {
            feedback.classList.add('d-none');
        }
        updateSummary();
    }

    const titheForm = document.getElementById('titheCreateForm');
    const summaryGiver = document.getElementById('summaryGiver');
    const summaryType = document.getElementById('summaryType');
    const summaryAmount = document.getElementById('summaryAmount');
    const summaryProgressPct = document.getElementById('summaryProgressPct');
    const summaryProgressBar = document.getElementById('summaryProgressBar');
    const typeSelect = document.getElementById('typeSelect');

    function formatCurrency(value) {
        const num = parseFloat(value || 0);
        return 'R$ ' + num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateSummary() {
        const giverVal = titheForm.querySelector('[name="giver_name"]').value.trim();
        summaryGiver.textContent = giverVal || '—';
        summaryGiver.classList.toggle('text-muted-value', !giverVal);

        summaryType.textContent = typeSelect.value;
        summaryAmount.textContent = formatCurrency(titheForm.querySelector('[name="amount"]').value);

        const requiredFields = Array.from(titheForm.querySelectorAll('[required]')).filter(f => !f.disabled);
        const filled = requiredFields.filter(f => f.value && f.value.trim() !== '').length;
        const pct = requiredFields.length ? Math.round((filled / requiredFields.length) * 100) : 0;
        summaryProgressPct.textContent = pct + '%';
        summaryProgressBar.style.width = pct + '%';
    }

    titheForm.addEventListener('input', updateSummary);
    titheForm.addEventListener('change', updateSummary);
    updateSummary();
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
