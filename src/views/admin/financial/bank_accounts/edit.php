<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Conta/Caixa</h1>
    <a href="/admin/financial/bank-accounts" class="btn btn-outline-secondary">Voltar</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="/admin/financial/bank-accounts/update/<?= $account['id'] ?>" method="POST">
            <?= csrf_field() ?>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nome da Conta <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($account['name']) ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" id="accountType" required>
                        <option value="caixa" <?= $account['type'] === 'caixa' ? 'selected' : '' ?>>Caixa Físico</option>
                        <option value="conta_corrente" <?= $account['type'] === 'conta_corrente' ? 'selected' : '' ?>>Conta Corrente</option>
                        <option value="poupanca" <?= $account['type'] === 'poupanca' ? 'selected' : '' ?>>Poupança</option>
                        <option value="investimento" <?= $account['type'] === 'investimento' ? 'selected' : '' ?>>Investimento</option>
                        <option value="centro_custo" <?= $account['type'] === 'centro_custo' ? 'selected' : '' ?>>Centro de Custo</option>
                    </select>
                </div>
            </div>

            <div class="row bank-fields" style="<?= in_array($account['type'], ['caixa', 'centro_custo'], true) ? 'display:none;' : '' ?>">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nome do Banco</label>
                    <input type="text" name="bank_name" class="form-control" value="<?= htmlspecialchars($account['bank_name'] ?? '') ?>">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Agência</label>
                    <input type="text" name="agency" class="form-control" value="<?= htmlspecialchars($account['agency'] ?? '') ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Número da Conta</label>
                    <input type="text" name="account_number" class="form-control" value="<?= htmlspecialchars($account['account_number'] ?? '') ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Saldo Atual (Somente Leitura)</label>
                    <input type="text" class="form-control" value="R$ <?= number_format($account['current_balance'], 2, ',', '.') ?>" disabled>
                    <small class="text-muted">O saldo é atualizado automaticamente pelos lançamentos.</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= $account['status'] === 'active' ? 'selected' : '' ?>>Ativa</option>
                        <option value="inactive" <?= $account['status'] === 'inactive' ? 'selected' : '' ?>>Inativa</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Atualizar Conta</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('accountType').addEventListener('change', function() {
    const bankFields = document.querySelector('.bank-fields');
    if (this.value === 'caixa' || this.value === 'centro_custo') {
        bankFields.style.display = 'none';
    } else {
        bankFields.style.display = 'flex';
    }
});
</script>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
