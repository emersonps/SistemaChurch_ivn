<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/donations" class="text-decoration-none">Contas para Doação</a></li>
                <li class="breadcrumb-item active">Nova</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Nova Conta/Chave PIX</h1>
    </div>
    <div class="d-none d-md-flex gap-2">
        <a href="/admin/donations" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">Cancelar</a>
        <button type="submit" form="donationCreateForm" class="btn btn-dark rounded-pill fw-semibold px-3">Salvar</button>
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
</style>

<form method="POST" action="/admin/donations/create" class="app-form-with-bottom-actions" id="donationCreateForm">
    <?= csrf_field() ?>

    <div class="member-form-card">
        <div class="member-form-card-header">
            <div class="member-form-badge"><i class="fas fa-piggy-bank"></i></div>
            <div>
                <div class="member-form-card-title">Dados da Conta / Chave PIX</div>
                <div class="member-form-card-subtitle">Informações exibidas na página pública de doação.</div>
            </div>
        </div>
        <div class="member-form-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Banco <span class="required-mark">*</span></label>
                    <input type="text" name="bank_name" class="form-control" placeholder="Ex: Banco do Brasil, Nubank..." required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nome do Titular <span class="required-mark">*</span></label>
                    <input type="text" name="beneficiary_name" class="form-control" value="<?= htmlspecialchars($siteProfile['name'] ?? '') ?>" required>
                    <div class="form-text">Aparece no aplicativo de quem for pagar (máx. 25 caracteres).</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cidade do Titular <span class="required-mark">*</span></label>
                    <input type="text" name="beneficiary_city" class="form-control" placeholder="Ex: Manaus" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipo de Chave PIX <span class="required-mark">*</span></label>
                    <select name="pix_key_type" class="form-select" required>
                        <?php foreach ($pixKeyTypes as $value => $label): ?>
                            <option value="<?= $value ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Chave PIX <span class="required-mark">*</span></label>
                    <input type="text" name="pix_key" class="form-control" placeholder="CPF, e-mail, telefone ou chave aleatória" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ordem de Exibição</label>
                    <input type="number" name="display_order" class="form-control" value="0">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Agência</label>
                    <input type="text" name="agency" class="form-control" placeholder="Opcional">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Conta</label>
                    <input type="text" name="account_number" class="form-control" placeholder="Opcional">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active">Ativa</option>
                        <option value="inactive">Inativa</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5 d-md-none">
        <a href="/admin/donations" class="btn btn-outline-secondary px-4">Cancelar</a>
        <button type="submit" class="btn btn-primary px-4">Salvar</button>
    </div>
</form>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
