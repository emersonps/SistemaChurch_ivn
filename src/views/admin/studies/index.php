<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Estudos Bíblicos e Esboços</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/studies/create" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-plus"></i> Novo Estudo
        </a>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Operação realizada com sucesso.</div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Título</th>
                <th>Descrição</th>
                <th>Congregação</th>
                <th>Data</th>
                <th>Arquivo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($studies)): ?>
                <tr><td colspan="6" class="text-center">Nenhum estudo cadastrado.</td></tr>
            <?php else: ?>
                <?php foreach ($studies as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['title']) ?></td>
                    <td><?= htmlspecialchars($s['description'] ?? '-') ?></td>
                    <td>
                        <?php if ($s['congregation_name']): ?>
                            <span class="badge bg-info"><?= htmlspecialchars($s['congregation_name']) ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Geral (Todas)</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($s['created_at'])) ?></td>
                    <td>
                        <a href="/uploads/studies/<?= $s['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </td>
                    <td>
                        <a href="/admin/studies/delete/<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir?');">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
