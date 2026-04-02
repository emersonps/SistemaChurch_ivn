<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Nova Conta/Caixa</h1>
    <a href="/admin/financial/bank-accounts" class="btn btn-outline-secondary">Voltar</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="/admin/financial/bank-accounts/store" method="POST">
            <?= csrf_field() ?>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nome da Conta <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="Ex: Bradesco, Caixa Físico Sede">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" id="accountType" required>
                        <option value="caixa">Caixa Físico</option>
                        <option value="conta_corrente" selected>Conta Corrente</option>
                        <option value="poupanca">Poupança</option>
                        <option value="investimento">Investimento</option>
                        <option value="centro_custo">Centro de Custo</option>
                    </select>
                </div>
            </div>

            <div class="row bank-fields">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nome do Banco</label>
                    <input type="text" name="bank_name" class="form-control" placeholder="Ex: Bradesco, Itaú">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Agência</label>
                    <input type="text" name="agency" class="form-control" placeholder="Ex: 1234-5">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Número da Conta</label>
                    <input type="text" name="account_number" class="form-control" placeholder="Ex: 12345-6">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Saldo Inicial (R$)</label>
                    <input type="number" step="0.01" name="initial_balance" class="form-control" value="0.00">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" selected>Ativa</option>
                        <option value="inactive">Inativa</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Conta</button>
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
