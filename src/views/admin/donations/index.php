<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Contas para Doação (PIX)</h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="/doacao" target="_blank" class="btn btn-sm btn-outline-success">
            <i class="fas fa-external-link-alt"></i> Ver Página Pública
        </a>
        <?php if (hasPermission('donations.manage')): ?>
        <a href="/admin/donations/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Nova Conta/Chave PIX
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    <?= htmlspecialchars($_SESSION['flash_success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <?= htmlspecialchars($_SESSION['flash_error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (empty($accounts)): ?>
    <div class="alert alert-info">Nenhuma conta cadastrada ainda. Clique em "Nova Conta/Chave PIX" para começar.</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-striped table-sm align-middle">
        <thead>
            <tr>
                <th>Ordem</th>
                <th>Banco</th>
                <th>Titular</th>
                <th>Chave PIX</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($accounts as $a): ?>
                <tr>
                    <td><?= (int)$a['display_order'] ?></td>
                    <td><?= htmlspecialchars($a['bank_name']) ?></td>
                    <td><?= htmlspecialchars($a['beneficiary_name']) ?></td>
                    <td><code><?= htmlspecialchars($a['pix_key']) ?></code></td>
                    <td><?= htmlspecialchars($pixKeyTypes[$a['pix_key_type']] ?? $a['pix_key_type']) ?></td>
                    <td>
                        <span class="badge bg-<?= $a['status'] === 'active' ? 'success' : 'secondary' ?>">
                            <?= $a['status'] === 'active' ? 'Ativa' : 'Inativa' ?>
                        </span>
                    </td>
                    <td>
                        <?php if (hasPermission('donations.manage')): ?>
                        <a href="/admin/donations/edit/<?= $a['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="/admin/donations/delete/<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja excluir esta conta/chave PIX?')" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
