<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Nova Campanha</h1>
    <a href="/admin/campaigns" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-arrow-left me-1"></i> Voltar</a>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="card shadow-sm" style="max-width: 640px;">
    <div class="card-body">
        <form method="POST" action="/admin/campaigns/create">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="title" class="form-control" placeholder="Ex: Reforma do Templo" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Meta de Arrecadação (R$)</label>
                    <input type="text" name="goal_amount" class="form-control" placeholder="Ex: 5000.00" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Congregação</label>
                    <select name="congregation_id" class="form-select">
                        <option value="">Igreja toda</option>
                        <?php foreach ($congregations as $c): ?>
                            <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Data de Início</label>
                    <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Data de Encerramento (opcional)</label>
                    <input type="date" name="end_date" class="form-control">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label d-block">Tipo de compromisso mensal</label>
                <div class="form-check">
                    <input type="radio" name="commitment_type" id="commitmentFixed" value="fixed" class="form-check-input" checked>
                    <label class="form-check-label" for="commitmentFixed">
                        <strong>Valor fixo</strong> — o participante define um valor mensal e o número de meses; as parcelas são geradas automaticamente.
                    </label>
                </div>
                <div class="form-check">
                    <input type="radio" name="commitment_type" id="commitmentFlexible" value="flexible" class="form-check-input">
                    <label class="form-check-label" for="commitmentFlexible">
                        <strong>Valor livre</strong> — cada mês e valor são cadastrados manualmente, um de cada vez.
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Salvar</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
