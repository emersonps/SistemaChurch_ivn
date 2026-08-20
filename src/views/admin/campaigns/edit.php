<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Campanha</h1>
    <a href="/admin/campaigns" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-arrow-left me-1"></i> Voltar</a>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="card shadow-sm" style="max-width: 640px;">
    <div class="card-body">
        <form method="POST" action="/admin/campaigns/edit/<?= (int)$campaign['id'] ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($campaign['title']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($campaign['description'] ?? '') ?></textarea>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Meta de Arrecadação (R$)</label>
                    <input type="text" name="goal_amount" class="form-control" value="<?= htmlspecialchars((string)$campaign['goal_amount']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Congregação</label>
                    <select name="congregation_id" class="form-select">
                        <option value="">Igreja toda</option>
                        <?php foreach ($congregations as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= (int)$campaign['congregation_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Data de Início</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($campaign['start_date']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Data de Encerramento (opcional)</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($campaign['end_date'] ?? '') ?>">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label d-block">Tipo de compromisso mensal</label>
                <div class="form-check">
                    <input type="radio" name="commitment_type" id="commitmentFixed" value="fixed" class="form-check-input" <?= $campaign['commitment_type'] === 'fixed' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="commitmentFixed">
                        <strong>Valor fixo</strong> — o participante define um valor mensal e o número de meses; as parcelas são geradas automaticamente.
                    </label>
                </div>
                <div class="form-check">
                    <input type="radio" name="commitment_type" id="commitmentFlexible" value="flexible" class="form-check-input" <?= $campaign['commitment_type'] === 'flexible' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="commitmentFlexible">
                        <strong>Valor livre</strong> — cada mês e valor são cadastrados manualmente, um de cada vez.
                    </label>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active" <?= $campaign['status'] === 'active' ? 'selected' : '' ?>>Ativa</option>
                    <option value="completed" <?= $campaign['status'] === 'completed' ? 'selected' : '' ?>>Concluída</option>
                    <option value="cancelled" <?= $campaign['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Salvar</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
