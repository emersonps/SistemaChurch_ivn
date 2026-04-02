<?php include __DIR__ . '/layout_developer.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gerenciador de Migrações</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/developer/migrations/run" class="btn btn-primary">
            <i class="fas fa-play"></i> Rodar Pendentes
        </a>
    </div>
</div>

<?php if (isset($_SESSION['migration_log'])): ?>
    <div class="alert alert-info">
        <h5 class="alert-heading">Log de Execução:</h5>
        <ul class="mb-0">
            <?php foreach ($_SESSION['migration_log'] as $log): ?>
                <li><?= htmlspecialchars($log) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['migration_log']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php
// Determine the last executed migration to enable rollback only on it
$lastExecuted = null;
foreach ($history as $m) {
    if ($m['status'] === 'executed') {
        // Since history might be sorted by filename, we need to find the one with the latest executed_at or rely on logic
        // But typically migrations are sequential. Let's find the one that matches the DB last record.
        // Actually, the View doesn't know DB order easily unless passed.
        // Let's rely on date sort? Or better: pass lastExecuted from Controller?
        // For now, let's assume we can only rollback if it is indeed the last one.
        // Simplified approach: Allow button, but Backend blocks. 
        // BETTER: Find the latest executed_at in the array.
    }
}

// Helper to find latest executed
$latestExecutedAt = '';
$latestFilename = '';
foreach ($history as $m) {
    if ($m['status'] === 'executed' && $m['executed_at'] > $latestExecutedAt) {
        $latestExecutedAt = $m['executed_at'];
        $latestFilename = $m['filename'];
    }
}
?>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Arquivo</th>
                        <th>Executado em</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $m): ?>
                        <tr>
                            <td>
                                <?php if ($m['status'] === 'executed'): ?>
                                    <span class="badge bg-success">Executado</span>
                                <?php elseif ($m['status'] === 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pendente</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Órfão</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($m['filename']) ?></td>
                            <td><?= $m['executed_at'] ? date('d/m/Y H:i:s', strtotime($m['executed_at'])) : '-' ?></td>
                            <td class="text-end">
                                <?php if ($m['status'] === 'executed'): ?>
                                    <?php if ($m['filename'] === $latestFilename): ?>
                                    <form action="/developer/migrations/rollback" method="POST" class="d-inline">
                                        <input type="hidden" name="filename" value="<?= htmlspecialchars($m['filename']) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza? Isso reverterá o registro da migração (e alterações no banco se suportado).')">
                                            <i class="fas fa-undo"></i> Rollback
                                        </button>
                                    </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary" disabled title="Você só pode reverter a última migração executada">
                                            <i class="fas fa-undo"></i> Rollback
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
