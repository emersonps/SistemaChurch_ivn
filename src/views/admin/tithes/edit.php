<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Dízimo/Oferta</h1>
</div>

<form action="/admin/tithes/edit/<?= $tithe['id'] ?>" method="POST" class="row g-3">
    <?= csrf_field() ?>
    <div class="col-md-6">
        <label class="form-label">Nome (Membro ou Visitante)</label>
        <div class="input-group">
            <input type="text" class="form-control" name="giver_name" list="memberList" value="<?= htmlspecialchars(!empty($tithe['member_name']) ? $tithe['member_name'] : ($tithe['giver_name'] ?? '')) ?>" placeholder="Digite o nome..." onchange="checkMember(this)" autocomplete="off" required>
            <input type="hidden" name="member_id" id="member_id" value="<?= $tithe['member_id'] ?? '' ?>">
        </div>
        <small class="<?= !empty($tithe['member_id']) ? 'text-success' : 'd-none' ?>" id="member_feedback">
            <?= !empty($tithe['member_id']) ? '<i class="fas fa-check-circle"></i> Membro identificado' : '' ?>
        </small>
    </div>

    <!-- Datalist for autocomplete -->
    <datalist id="memberList">
        <?php foreach ($members as $m): ?>
            <option value="<?= htmlspecialchars($m['name']) ?>"></option>
        <?php endforeach; ?>
    </datalist>

    <div class="col-md-3">
        <label class="form-label">Tipo</label>
        <select class="form-select" name="type" required>
            <option value="Dízimo" <?= ($tithe['type'] ?? 'Dízimo') == 'Dízimo' ? 'selected' : '' ?>>Dízimo</option>
            <option value="Oferta" <?= ($tithe['type'] ?? 'Dízimo') == 'Oferta' ? 'selected' : '' ?>>Oferta</option>
        </select>
    </div>
    
    <div class="col-md-3">
        <label class="form-label">Valor (R$)</label>
        <input type="number" step="0.01" class="form-control" name="amount" value="<?= $tithe['amount'] ?>" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Data</label>
        <input type="date" class="form-control" name="payment_date" value="<?= date('Y-m-d', strtotime($tithe['payment_date'])) ?>" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Método</label>
        <select class="form-select" name="payment_method">
            <option value="Dinheiro" <?= $tithe['payment_method'] == 'Dinheiro' ? 'selected' : '' ?>>Dinheiro</option>
            <option value="PIX" <?= $tithe['payment_method'] == 'PIX' ? 'selected' : '' ?>>PIX</option>
            <option value="Cartão" <?= $tithe['payment_method'] == 'Cartão' ? 'selected' : '' ?>>Cartão</option>
            <option value="Transferência" <?= $tithe['payment_method'] == 'Transferência' ? 'selected' : '' ?>>Transferência</option>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Conta de Destino</label>
        <select name="bank_account_id" id="bankAccountSelect" class="form-select" required>
            <?php foreach ($bankAccounts as $bank): ?>
                <option value="<?= $bank['id'] ?>" <?= $tithe['bank_account_id'] == $bank['id'] ? 'selected' : '' ?>>
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
                <option value="<?= $chart['id'] ?>" <?= $tithe['chart_account_id'] == $chart['id'] ? 'selected' : '' ?>>
                    <?= $chart['code'] ?> - <?= htmlspecialchars($chart['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if (!empty($hasAccountableField)): ?>
    <div class="col-12">
        <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" id="isAccountableInput" name="is_accountable" value="1" <?= !isset($tithe['is_accountable']) || (int)$tithe['is_accountable'] === 1 ? 'checked' : '' ?>>
            <label class="form-check-label" for="isAccountableInput">Contabilizar esta entrada</label>
        </div>
        <small class="text-muted">Se desmarcado, o lançamento ficará apenas registrado, sem entrar em relatórios financeiros, fechamentos e saldos.</small>
    </div>
    <?php endif; ?>

    <div class="col-md-8">
        <label class="form-label">Observações</label>
        <input type="text" class="form-control" name="notes" value="<?= htmlspecialchars($tithe['notes'] ?? '') ?>">
    </div>

    <div class="col-12 mt-4">
        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="/admin/tithes" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<script>
    const membersData = <?php echo json_encode($members); ?>;

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

    function checkMember(input) {
        const val = input.value;
        const hiddenId = document.getElementById('member_id');
        const feedback = document.getElementById('member_feedback');
        
        hiddenId.value = ''; // Reset
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
    }
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
