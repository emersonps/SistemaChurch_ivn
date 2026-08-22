<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Modelos de Escala</h1>
    <div class="d-flex gap-2">
        <a href="/admin/liturgy-schedules" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-list me-1"></i> Ver Escalas</a>
        <a href="/admin/liturgy-schedules/templates/create" class="btn btn-sm btn-primary rounded-pill px-3"><i class="fas fa-plus me-1"></i> Novo Modelo</a>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (empty($templates)): ?>
    <div class="text-center text-muted py-5">Nenhum modelo de escala criado ainda.</div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Congregação</th>
                        <th>Papéis</th>
                        <th>Escalas criadas</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($templates as $t): ?>
                        <?php $roles = json_decode($t['roles_config'], true) ?: []; ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($t['name']) ?></td>
                            <td><?= htmlspecialchars($t['congregation_name'] ?? 'Todas') ?></td>
                            <td class="small text-muted"><?= htmlspecialchars(implode(', ', array_column($roles, 'label'))) ?></td>
                            <td><?= (int)$t['schedules_count'] ?></td>
                            <td class="text-end">
                                <a href="/admin/liturgy-schedules/templates/edit/<?= (int)$t['id'] ?>" class="btn btn-sm btn-outline-secondary icon-btn"><i class="fas fa-pen"></i></a>
                                <?php if ((int)$t['schedules_count'] === 0): ?>
                                    <form action="/admin/liturgy-schedules/templates/delete/<?= (int)$t['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Excluir este modelo?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger icon-btn"><i class="fas fa-trash"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
