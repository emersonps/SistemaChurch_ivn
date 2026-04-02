<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Galeria de Fotos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/gallery/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Novo Álbum
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Data</th>
                <th>Título</th>
                <th>Local</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($albums as $a): ?>
                <tr>
                    <td><?= $a['event_date'] ? date('d/m/Y', strtotime($a['event_date'])) : 'N/A' ?></td>
                    <td><?= htmlspecialchars($a['title']) ?></td>
                    <td><?= htmlspecialchars($a['location']) ?></td>
                    <td>
                        <a href="/admin/gallery/edit/<?= $a['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar Informações">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="/admin/gallery/manage/<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary" title="Gerenciar Fotos">
                            <i class="fas fa-images"></i>
                        </a>
                        <a href="/admin/gallery/delete/<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja excluir este álbum?')" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
