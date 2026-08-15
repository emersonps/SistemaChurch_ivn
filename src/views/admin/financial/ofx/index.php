<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Conciliação Bancária (OFX)</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-sm btn-primary rounded-pill fw-semibold px-3" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fas fa-file-import me-1"></i> Importar OFX
        </button>
    </div>
</div>

<style>
    .member-form-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
    }
    .ofx-table thead th {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .ofx-table td { vertical-align: middle; }
    .status-pill {
        display: inline-block;
        padding: .2rem .65rem;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 700;
    }
    .status-pill.status-completed { background: rgba(25,135,84,0.10); color: #198754; }
    .status-pill.status-pending { background: rgba(255,193,7,0.16); color: #997404; }
    .modal-content { border-radius: 16px; border: none; overflow: hidden; }
    .modal-header { background: #fafafa; }
    #importModal .form-control, #importModal .form-select {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
    }
    #importModal .form-control:focus, #importModal .form-select:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
</style>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= $_SESSION['flash_success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="member-form-card">
    <div class="table-responsive p-2">
        <table class="table table-hover ofx-table mb-0" style="width:100%">
            <thead>
                <tr>
                    <th>Data Importação</th>
                    <th>Conta Vinculada</th>
                    <th>Arquivo</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($imports)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">Nenhuma importação realizada.</td></tr>
                <?php else: ?>
                    <?php foreach ($imports as $imp): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($imp['import_date'])) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($imp['bank_name']) ?></td>
                            <td><?= htmlspecialchars($imp['filename']) ?></td>
                            <td>
                                <?php if ($imp['status'] === 'completed'): ?>
                                    <span class="status-pill status-completed">Concluído</span>
                                <?php else: ?>
                                    <span class="status-pill status-pending">Pendente</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="/admin/financial/ofx/conciliate/<?= $imp['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold px-3">
                                    <i class="fas fa-search me-1"></i> Conciliar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/admin/financial/ofx/import" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-import me-2"></i>Importar Arquivo OFX</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Conta Bancária Correspondente <span class="text-danger">*</span></label>
                        <select name="bank_account_id" class="form-select" required>
                            <option value="">-- Selecione a Conta --</option>
                            <?php foreach ($banks as $bank): ?>
                                <option value="<?= $bank['id'] ?>"><?= htmlspecialchars($bank['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Arquivo .OFX <span class="text-danger">*</span></label>
                        <input type="file" name="ofx_file" class="form-control" accept=".ofx" required>
                        <div class="form-text">Faça o download do extrato OFX no internet banking da sua conta.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill fw-semibold px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark rounded-pill fw-semibold px-3"><i class="fas fa-upload me-1"></i> Processar Importação</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
