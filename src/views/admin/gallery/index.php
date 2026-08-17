<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Galeria de Fotos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/gallery/create" class="btn btn-sm btn-primary rounded-pill fw-semibold px-3">
            <i class="fas fa-plus me-1"></i> Novo Álbum
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
    .gallery-table thead th {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .gallery-table td {
        vertical-align: middle;
        padding-top: .65rem;
        padding-bottom: .65rem;
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
        <table class="table table-hover gallery-table" style="width:100%">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Título</th>
                    <th>Local</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($albums)): ?>
                    <tr><td colspan="4" class="text-center py-4 text-muted">Nenhum álbum cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($albums as $a): ?>
                        <tr>
                            <td><?= $a['event_date'] ? date('d/m/Y', strtotime($a['event_date'])) : 'N/A' ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($a['title']) ?></td>
                            <td><?= htmlspecialchars($a['location']) ?></td>
                            <td class="text-end">
                                <a href="/admin/gallery/edit/<?= $a['id'] ?>" class="btn btn-sm btn-outline-secondary icon-btn" title="Editar Informações">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="/admin/gallery/manage/<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary icon-btn" title="Gerenciar Fotos">
                                    <i class="fas fa-images"></i>
                                </a>
                                <a href="/admin/gallery/delete/<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger icon-btn btn-delete-album" data-title="<?= htmlspecialchars($a['title']) ?>" title="Excluir">
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
document.querySelectorAll('.btn-delete-album').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const href = btn.getAttribute('href');
        const title = btn.getAttribute('data-title');
        Swal.fire({
            title: 'Excluir álbum?',
            text: `Tem certeza que deseja excluir o álbum "${title}"? Todas as fotos serão removidas junto.`,
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
