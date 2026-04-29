<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Saída</h1>
</div>

<form action="/admin/expenses/update/<?= $expense['id'] ?>" method="POST" class="row g-3 app-form-with-bottom-actions">
    <?= csrf_field() ?>
    <div class="col-md-6">
        <label class="form-label">Descrição</label>
        <input type="text" class="form-control" name="description" value="<?= htmlspecialchars($expense['description']) ?>" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Valor (R$)</label>
        <input type="number" step="0.01" class="form-control" name="amount" value="<?= $expense['amount'] ?>" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Data</label>
        <input type="date" class="form-control" name="expense_date" value="<?= !empty($expense['expense_date']) ? date('Y-m-d', strtotime($expense['expense_date'])) : '' ?>" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Conta de Saída</label>
        <select name="bank_account_id" id="bankAccountSelect" class="form-select" required>
            <?php foreach ($bankAccounts as $bank): ?>
                <option value="<?= $bank['id'] ?>" <?= $expense['bank_account_id'] == $bank['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($bank['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Categoria Contábil</label>
        <select name="chart_account_id" id="chartAccountSelect" class="form-select">
            <option value="">-- Automático --</option>
            <?php foreach ($chartAccounts as $chart): ?>
                <option value="<?= $chart['id'] ?>" <?= $expense['chart_account_id'] == $chart['id'] ? 'selected' : '' ?>>
                    <?= $chart['code'] ?> - <?= htmlspecialchars($chart['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if (!empty($hasAccountableField)): ?>
    <div class="col-12">
        <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" id="isAccountableInput" name="is_accountable" value="1" <?= !isset($expense['is_accountable']) || (int)$expense['is_accountable'] === 1 ? 'checked' : '' ?>>
            <label class="form-check-label" for="isAccountableInput">Contabilizar esta saída</label>
        </div>
        <small class="text-muted">Se desmarcado, a saída ficará registrada, mas não entrará na contabilidade, relatórios, fechamentos e saldos.</small>
    </div>
    <?php endif; ?>

    <div class="col-md-8">
        <label class="form-label">Categoria Antiga (Legado)</label>
        <select class="form-select" name="category" required>
            <option value="Manutenção" <?= $expense['category'] == 'Manutenção' ? 'selected' : '' ?>>Manutenção</option>
            <option value="Contas Fixas" <?= $expense['category'] == 'Contas Fixas' ? 'selected' : '' ?>>Contas Fixas</option>
            <option value="Eventos" <?= $expense['category'] == 'Eventos' ? 'selected' : '' ?>>Eventos</option>
            <option value="Ajuda de Custo" <?= $expense['category'] == 'Ajuda de Custo' ? 'selected' : '' ?>>Ajuda de Custo</option>
            <option value="Missões" <?= $expense['category'] == 'Missões' ? 'selected' : '' ?>>Missões</option>
            <option value="Material de Limpeza" <?= $expense['category'] == 'Material de Limpeza' ? 'selected' : '' ?>>Material de Limpeza</option>
            <option value="Outros" <?= $expense['category'] == 'Outros' ? 'selected' : '' ?>>Outros</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Congregação</label>
        <select class="form-select" name="congregation_id" id="congregationSelect" required>
            <?php foreach ($congregations as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $c['id'] == $expense['congregation_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-12">
        <label class="form-label">Observações</label>
        <textarea class="form-control" name="notes" rows="3"><?= htmlspecialchars($expense['notes']) ?></textarea>
    </div>

    <div class="col-12 mt-4 text-end d-none d-lg-block">
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
        <a href="/admin/expenses" class="btn btn-outline-secondary px-4">Cancelar</a>
    </div>

    <div class="col-12 app-form-bottom-actions d-lg-none">
        <div class="row g-2">
            <div class="col-6">
                <button type="submit" class="btn btn-primary w-100">Salvar</button>
            </div>
            <div class="col-6">
                <a href="/admin/expenses" class="btn btn-outline-secondary w-100">Cancelar</a>
            </div>
        </div>
    </div>
</form>

<script>
const congSel = document.getElementById('congregationSelect');
const accSel = document.getElementById('chartAccountSelect');
async function loadAccounts() {
    const cid = congSel.value;
    const current = accSel.value;
    accSel.innerHTML = '<option value=\"\">-- Automático --</option>';
    try {
        const res = await fetch(`/api/financial/chart-accounts?type=expense&congregation_id=${encodeURIComponent(cid)}`);
        const data = await res.json();
        if (data && Array.isArray(data.accounts)) {
            data.accounts.forEach(a => {
                const opt = document.createElement('option');
                opt.value = a.id;
                opt.textContent = `${a.code} - ${a.name}`;
                if (String(a.id) === String(current)) opt.selected = true;
                accSel.appendChild(opt);
            });
        }
    } catch (e) {}
}
congSel.addEventListener('change', loadAccounts);
document.addEventListener('DOMContentLoaded', loadAccounts);

function toggleAccountingFields() {
    const accountable = document.getElementById('isAccountableInput');
    if (!accountable) return;
    const disabled = !accountable.checked;
    const bank = document.getElementById('bankAccountSelect');
    const chart = document.getElementById('chartAccountSelect');
    if (bank) {
        bank.disabled = disabled;
        bank.required = !disabled;
        if (disabled) bank.value = '';
    }
    if (chart) {
        chart.disabled = disabled;
        if (disabled) chart.value = '';
    }
}
document.getElementById('isAccountableInput')?.addEventListener('change', toggleAccountingFields);
toggleAccountingFields();
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
