<?php include __DIR__ . '/layout/header.php'; ?>

<style>
    .portal-tithe-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .85rem 1.25rem;
        border-top: 1px solid rgba(0,0,0,0.05);
    }
    .portal-tithe-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }
</style>

<div class="portal-page-title">Meus Dízimos</div>
<p class="text-muted mb-3">Histórico de contribuições e ofertas.</p>

<div class="portal-card mb-3">
    <div class="p-3">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold mb-1">Data Início</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold mb-1">Data Fim</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Tipo</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="Dízimo" <?= (isset($_GET['type']) && $_GET['type'] == 'Dízimo') ? 'selected' : '' ?>>Dízimo</option>
                    <option value="Oferta" <?= (isset($_GET['type']) && $_GET['type'] == 'Oferta') ? 'selected' : '' ?>>Oferta</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-dark rounded-pill fw-semibold flex-fill">Filtrar</button>
                <a href="/portal/financial" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold flex-fill">Limpar</a>
            </div>
        </form>
    </div>
</div>

<div class="portal-card">
    <?php if (empty($tithes)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-receipt fa-2x mb-3 opacity-50"></i>
            <p class="mb-0">Nenhum registro encontrado.</p>
        </div>
    <?php else: ?>
        <?php foreach ($tithes as $t):
            $isTithe = ($t['type'] ?? 'Dízimo') == 'Dízimo';
        ?>
            <div class="portal-tithe-row">
                <div class="d-flex align-items-center gap-3">
                    <span class="portal-tithe-icon <?= $isTithe ? 'portal-pill-red' : 'portal-pill-green' ?>" style="background: <?= $isTithe ? 'rgba(179,0,0,.10)' : 'rgba(25,135,84,.10)' ?>; color: <?= $isTithe ? 'var(--portal-primary)' : '#198754' ?>;">
                        <i class="fas <?= $isTithe ? 'fa-hand-holding-dollar' : 'fa-gift' ?>"></i>
                    </span>
                    <div>
                        <div class="fw-bold small"><?= htmlspecialchars($t['type'] ?? 'Dízimo') ?></div>
                        <div class="text-muted" style="font-size:.75rem;"><?= date('d/m/Y', strtotime($t['payment_date'])) ?> · <?= htmlspecialchars(ucfirst($t['payment_method'])) ?></div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <strong>R$ <?= number_format($t['amount'], 2, ',', '.') ?></strong>
                    <a href="/admin/tithes/receipt/<?= $t['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill" title="Ver Recibo">
                        <i class="fas fa-file-invoice"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
