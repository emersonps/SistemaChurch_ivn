<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-end pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-2">Escalas Litúrgicas</h1>
        <ul class="nav nav-pills ls-section-nav">
            <li class="nav-item"><a class="nav-link active" href="/admin/liturgy-schedules"><i class="fas fa-calendar-check me-1"></i> Escalas</a></li>
            <li class="nav-item"><a class="nav-link" href="/admin/liturgy-schedules/templates"><i class="fas fa-sliders me-1"></i> Modelos</a></li>
        </ul>
    </div>
    <a href="/admin/liturgy-schedules/create" class="btn btn-sm btn-primary rounded-pill px-3"><i class="fas fa-plus me-1"></i> Nova Escala</a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<style>
    .ls-stat-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 14px;
        padding: 1rem 1.1rem;
        height: 100%;
    }
    .ls-stat-label { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: #868e96; font-weight: 700; }
    .ls-stat-value { font-size: 1.5rem; font-weight: 800; color: #212529; }
    .icon-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; padding: 0; }
    .ls-section-nav { --bs-nav-link-padding-y: .35rem; margin-bottom: 0; }
    .ls-section-nav .nav-link { font-size: .85rem; font-weight: 600; color: #495057; padding: .35rem .9rem; }
    .ls-section-nav .nav-link.active { background-color: #212529; color: #fff; }
</style>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="ls-stat-card">
            <div class="ls-stat-label">Total de Escalas</div>
            <div class="ls-stat-value"><?= (int)$stats['total'] ?></div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="ls-stat-card">
            <div class="ls-stat-label">Mensais</div>
            <div class="ls-stat-value"><?= (int)$stats['monthly'] ?></div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="ls-stat-card">
            <div class="ls-stat-label">Cultos Escalados</div>
            <div class="ls-stat-value"><?= (int)$stats['entries'] ?></div>
        </div>
    </div>
</div>

<?php if (empty($schedules)): ?>
    <div class="text-center text-muted py-5">Nenhuma escala criada ainda.</div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Modelo</th>
                        <th>Congregação</th>
                        <th>Período</th>
                        <th>Cultos</th>
                        <th>Próximo</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $periodLabels = ['daily' => 'Diária', 'weekly' => 'Semanal', 'monthly' => 'Mensal']; ?>
                    <?php foreach ($schedules as $s): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($s['title']) ?></td>
                            <td class="small text-muted"><?= htmlspecialchars($s['template_name']) ?></td>
                            <td><?= htmlspecialchars($s['congregation_name'] ?? 'Todas') ?></td>
                            <td><?= htmlspecialchars($periodLabels[$s['period_type']] ?? $s['period_type']) ?></td>
                            <td><?= (int)$s['entries_count'] ?></td>
                            <td class="small text-muted"><?= $s['next_date'] ? date('d/m/Y', strtotime($s['next_date'])) : '-' ?></td>
                            <td class="text-end">
                                <a href="/admin/liturgy-schedules/edit/<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline-primary">Abrir</a>
                                <form action="/admin/liturgy-schedules/delete/<?= (int)$s['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Excluir esta escala definitivamente?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger icon-btn"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
