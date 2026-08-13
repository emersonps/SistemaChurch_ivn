<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Conta/Chave PIX</h1>
    <a href="/admin/donations" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/admin/donations/edit/<?= $account['id'] ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Banco *</label>
                    <input type="text" name="bank_name" class="form-control" value="<?= htmlspecialchars($account['bank_name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nome do Titular *</label>
                    <input type="text" name="beneficiary_name" class="form-control" value="<?= htmlspecialchars($account['beneficiary_name']) ?>" required>
                    <div class="form-text">Aparece no aplicativo de quem for pagar (máx. 25 caracteres).</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cidade do Titular *</label>
                    <input type="text" name="beneficiary_city" class="form-control" value="<?= htmlspecialchars($account['beneficiary_city']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipo de Chave PIX *</label>
                    <select name="pix_key_type" class="form-select" required>
                        <?php foreach ($pixKeyTypes as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $account['pix_key_type'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Chave PIX *</label>
                    <input type="text" name="pix_key" class="form-control" value="<?= htmlspecialchars($account['pix_key']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Agência</label>
                    <input type="text" name="agency" class="form-control" placeholder="Opcional" value="<?= htmlspecialchars($account['agency'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Conta</label>
                    <input type="text" name="account_number" class="form-control" placeholder="Opcional" value="<?= htmlspecialchars($account['account_number'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ordem de Exibição</label>
                    <input type="number" name="display_order" class="form-control" value="<?= (int)$account['display_order'] ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= $account['status'] === 'active' ? 'selected' : '' ?>>Ativa</option>
                        <option value="inactive" <?= $account['status'] === 'inactive' ? 'selected' : '' ?>>Inativa</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
