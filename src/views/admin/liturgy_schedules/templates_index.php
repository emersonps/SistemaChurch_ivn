<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-end pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-2">Modelos de Escala</h1>
        <ul class="nav nav-pills ls-section-nav">
            <li class="nav-item"><a class="nav-link" href="/admin/liturgy-schedules"><i class="fas fa-calendar-check me-1"></i> Escalas</a></li>
            <li class="nav-item"><a class="nav-link active" href="/admin/liturgy-schedules/templates"><i class="fas fa-sliders me-1"></i> Modelos</a></li>
        </ul>
    </div>
    <a href="/admin/liturgy-schedules/templates/create" class="btn btn-sm btn-primary rounded-pill px-3"><i class="fas fa-plus me-1"></i> Novo Modelo</a>
</div>

<style>
    .ls-section-nav { --bs-nav-link-padding-y: .35rem; margin-bottom: 0; }
    .ls-section-nav .nav-link { font-size: .85rem; font-weight: 600; color: #495057; padding: .35rem .9rem; }
    .ls-section-nav .nav-link.active { background-color: #212529; color: #fff; }
</style>

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
