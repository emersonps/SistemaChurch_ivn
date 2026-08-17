<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Banners</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/banners/create" class="btn btn-sm btn-primary rounded-pill fw-semibold px-3">
            <i class="fas fa-plus me-1"></i> Novo Banner
        </a>
    </div>
</div>

<style>
    .member-form-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
    }
    .banners-table thead th {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .banners-table td {
        vertical-align: middle;
        padding-top: .65rem;
        padding-bottom: .65rem;
    }
    .banner-thumb {
        max-height: 54px;
        max-width: 160px;
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: 8px;
        border: 1px solid rgba(0,0,0,0.08);
        background: #f8f9fa;
        padding: 2px;
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

<div class="member-form-card">
    <div class="table-responsive p-2">
        <table class="table table-hover banners-table" style="width:100%">
            <thead>
                <tr>
                    <th>Ordem</th>
                    <th>Imagem</th>
                    <th>Título</th>
                    <th>Link</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($banners)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">Nenhum banner cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($banners as $b): ?>
                        <tr>
                            <td><span class="order-pill"><?= $b['display_order'] ?></span></td>
                            <td>
                                <img src="/<?= $b['image_path'] ?>" alt="<?= htmlspecialchars($b['title']) ?>" class="banner-thumb">
                            </td>
                            <td class="fw-bold"><?= htmlspecialchars($b['title']) ?></td>
                            <td class="small text-muted text-truncate" style="max-width: 220px;"><?= htmlspecialchars($b['link']) ?></td>
                            <td>
                                <span class="status-pill <?= $b['active'] ? 'is-active' : 'is-inactive' ?>">
                                    <?= $b['active'] ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="/admin/banners/edit/<?= $b['id'] ?>" class="btn btn-sm btn-outline-secondary icon-btn" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="/admin/banners/delete/<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger icon-btn btn-delete-banner" data-title="<?= htmlspecialchars($b['title']) ?>" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.querySelectorAll('.btn-delete-banner').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const href = btn.getAttribute('href');
        const title = btn.getAttribute('data-title');
        Swal.fire({
            title: 'Excluir banner?',
            text: `Tem certeza que deseja excluir "${title}"? Esta ação não pode ser desfeita.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.replace(href);
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
