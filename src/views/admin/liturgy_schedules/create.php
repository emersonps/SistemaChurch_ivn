<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Nova Escala</h1>
    <a href="/admin/liturgy-schedules" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-arrow-left me-1"></i> Voltar</a>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="card shadow-sm" style="max-width: 640px;">
    <div class="card-body">
        <form method="POST" action="/admin/liturgy-schedules/create">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Modelo de escala</label>
                <select name="template_id" class="form-select" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($templates as $t): ?>
                        <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Título da escala</label>
                <input type="text" name="title" class="form-control" placeholder="Ex: Escala Mensal - Agosto/2026" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Congregação (opcional)</label>
                <select name="congregation_id" class="form-select">
                    <option value="">Todas as congregações</option>
                    <?php foreach ($congregations as $c): ?>
                        <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Tipo de período</label>
                <select name="period_type" id="periodType" class="form-select">
                    <option value="monthly">Mensal</option>
                    <option value="weekly">Semanal</option>
                    <option value="daily">Diária</option>
                </select>
            </div>

            <div class="mb-3" id="referenceMonthField">
                <label class="form-label">Mês de referência</label>
                <input type="month" name="reference_month" class="form-control" value="<?= date('Y-m') ?>">
                <div class="form-text">Se a congregação escolhida tiver dias de culto cadastrados, as datas do mês são geradas automaticamente. Você pode adicionar/remover linhas depois.</div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Criar Escala</button>
        </form>
    </div>
</div>

<script>
document.getElementById('periodType').addEventListener('change', function () {
    document.getElementById('referenceMonthField').classList.toggle('d-none', this.value !== 'monthly');
});
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
