<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Contas para Doação (PIX)</h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="/doacao" target="_blank" class="btn btn-sm btn-outline-success rounded-pill fw-semibold px-3">
            <i class="fas fa-external-link-alt me-1"></i> Ver Página Pública
        </a>
        <?php if (hasPermission('donations.manage')): ?>
        <a href="/admin/donations/create" class="btn btn-sm btn-primary rounded-pill fw-semibold px-3">
            <i class="fas fa-plus me-1"></i> Nova Conta/Chave PIX
        </a>
        <?php endif; ?>
    </div>
</div>

<style>
    .member-form-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
    }
    .donations-table thead th {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .donations-table td {
        vertical-align: middle;
        padding-top: .65rem;
        padding-bottom: .65rem;
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .3rem .65rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
    }
    .status-pill::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
        flex: 0 0 auto;
    }
    .status-pill.is-active { background: rgba(25,135,84,0.12); color: #198754; }
    .status-pill.is-inactive { background: rgba(0,0,0,0.06); color: #6c757d; }
    .order-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #eef0f2;
        color: #495057;
        font-weight: 700;
        font-size: .78rem;
    }
    .type-pill {
        display: inline-block;
        padding: .2rem .55rem;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 700;
        background: rgba(179,0,0,0.10);
        color: #b30000;
    }
    .icon-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        padding: 0;
    }
</style>

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

<div class="member-form-card">
    <div class="table-responsive p-2">
        <table class="table table-hover donations-table align-middle" style="width:100%">
            <thead>
                <tr>
                    <th>Ordem</th>
                    <th>Banco</th>
                    <th>Titular</th>
                    <th>Chave PIX</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($accounts)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">Nenhuma conta cadastrada ainda. Clique em "Nova Conta/Chave PIX" para começar.</td></tr>
                <?php else: ?>
                    <?php foreach ($accounts as $a): ?>
                        <tr>
                            <td><span class="order-pill"><?= (int)$a['display_order'] ?></span></td>
                            <td class="fw-bold"><?= htmlspecialchars($a['bank_name']) ?></td>
                            <td><?= htmlspecialchars($a['beneficiary_name']) ?></td>
                            <td><code><?= htmlspecialchars($a['pix_key']) ?></code></td>
                            <td><span class="type-pill"><?= htmlspecialchars($pixKeyTypes[$a['pix_key_type']] ?? $a['pix_key_type']) ?></span></td>
                            <td>
                                <span class="status-pill <?= $a['status'] === 'active' ? 'is-active' : 'is-inactive' ?>">
                                    <?= $a['status'] === 'active' ? 'Ativa' : 'Inativa' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <?php if (hasPermission('donations.manage')): ?>
                                <a href="/admin/donations/edit/<?= $a['id'] ?>" class="btn btn-sm btn-outline-secondary icon-btn" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="/admin/donations/delete/<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger icon-btn btn-delete-donation" data-name="<?= htmlspecialchars($a['bank_name'] . ' — ' . $a['beneficiary_name']) ?>" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.querySelectorAll('.btn-delete-donation').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const href = btn.getAttribute('href');
        const name = btn.getAttribute('data-name');
        Swal.fire({
            title: 'Excluir conta/chave PIX?',
            text: `Tem certeza que deseja excluir "${name}"? Esta ação não pode ser desfeita.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
