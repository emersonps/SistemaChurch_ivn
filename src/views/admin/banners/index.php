<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Banners</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/banners/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Novo Banner
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Ordem</th>
                <th>Imagem</th>
                <th>Título</th>
                <th>Link</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($banners as $b): ?>
                <tr>
                    <td><?= $b['display_order'] ?></td>
                    <td>
                        <img src="/<?= $b['image_path'] ?>" alt="<?= htmlspecialchars($b['title']) ?>" style="height: 50px;">
                    </td>
                    <td><?= htmlspecialchars($b['title']) ?></td>
                    <td><?= htmlspecialchars($b['link']) ?></td>
                    <td>
                        <span class="badge bg-<?= $b['active'] ? 'success' : 'secondary' ?>">
                            <?= $b['active'] ? 'Ativo' : 'Inativo' ?>
                        </span>
                    </td>
                    <td>
                        <a href="/admin/banners/edit/<?= $b['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="/admin/banners/delete/<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este banner?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
