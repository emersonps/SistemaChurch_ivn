<?php include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Conciliação Bancária (OFX)</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fas fa-file-import"></i> Importar OFX
        </button>
    </div>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['flash_success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Data Importação</th>
                        <th>Conta Vinculada</th>
                        <th>Arquivo</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($imports as $imp): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($imp['import_date'])) ?></td>
                            <td><?= htmlspecialchars($imp['bank_name']) ?></td>
                            <td><?= htmlspecialchars($imp['filename']) ?></td>
                            <td>
                                <?php if ($imp['status'] === 'completed'): ?>
                                    <span class="badge bg-success">Concluído</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pendente</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="/admin/financial/ofx/conciliate/<?= $imp['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-search"></i> Conciliar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($imports)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">Nenhuma importação realizada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/admin/financial/ofx/import" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Importar Arquivo OFX</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Conta Bancária Correspondente</label>
                        <select name="bank_account_id" class="form-select" required>
                            <option value="">-- Selecione a Conta --</option>
                            <?php foreach ($banks as $bank): ?>
                                <option value="<?= $bank['id'] ?>"><?= htmlspecialchars($bank['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Arquivo .OFX</label>
                        <input type="file" name="ofx_file" class="form-control" accept=".ofx" required>
                        <small class="text-muted">Faça o download do extrato OFX no internet banking da sua conta.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Processar Importação</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
