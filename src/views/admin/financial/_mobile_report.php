<?php
// src/views/admin/financial/_mobile_report.php
// Mobile/tablet (<992px) card-based presentation of the same report data already
// computed by FinancialReportController::buildReportData() ($entries, $expenses,
// $total_entries, $total_tithes, $total_offerings, $total_expenses,
// $expenses_by_category, $balance, $congregations, $filters). Desktop keeps the
// classic printable report untouched (hidden via d-none d-lg-block there).
// Redesigned for a simpler, tabbed (Resumo/Saídas/Entradas) layout per user request —
// same underlying data as the previous version, just less on screen at once.

$frmStart = new DateTimeImmutable($filters['start_date']);
$frmEnd = new DateTimeImmutable($filters['end_date']);
$frmMonthNames = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
$frmMonthLabel = $frmMonthNames[(int)$frmEnd->format('n')] . ' de ' . $frmEnd->format('Y');
$frmPeriodShort = $frmStart->format('d/m') . ' - ' . $frmEnd->format('d/m');
$frmCongLabel = $filters['congregation_name'] ?? 'Todas';

$frmMovementTotal = $total_entries + $total_expenses;
$frmEntriesPct = $frmMovementTotal > 0 ? round(($total_entries / $frmMovementTotal) * 100) : 0;
$frmExpensesPct = $frmMovementTotal > 0 ? (100 - $frmEntriesPct) : 0;
$frmIsNegative = $balance < 0;

function frmExpenseIcon($category, $description) {
    $t = mb_strtolower($category . ' ' . $description, 'UTF-8');
    if (strpos($t, 'água') !== false || strpos($t, 'agua') !== false) return 'fa-droplet';
    if (strpos($t, 'luz') !== false || strpos($t, 'energia') !== false) return 'fa-bolt';
    if (strpos($t, 'manuten') !== false || strpos($t, 'reparo') !== false) return 'fa-wrench';
    if (strpos($t, 'compra') !== false || strpos($t, 'loja') !== false || strpos($t, 'material') !== false) return 'fa-cart-shopping';
    if (strpos($t, 'aluguel') !== false) return 'fa-house';
    if (strpos($t, 'salário') !== false || strpos($t, 'salario') !== false || strpos($t, 'folha') !== false) return 'fa-sack-dollar';
    if (strpos($t, 'internet') !== false || strpos($t, 'telefone') !== false) return 'fa-wifi';
    return 'fa-receipt';
}

$frmCategoryRows = [];
arsort($expenses_by_category);
foreach ($expenses_by_category as $cat => $amount) {
    $pct = $total_expenses > 0 ? ($amount / $total_expenses) * 100 : 0;
    $frmCategoryRows[] = ['label' => $cat ?: 'Outros', 'amount' => $amount, 'pct' => $pct];
}

$mobilePageCategory = 'Financeiro';
$mobilePageTitle = null;
$mobilePageMenuItems = [
    ['icon' => 'fa-file-csv', 'label' => 'Exportar CSV', 'sub' => 'Período ' . $frmPeriodShort . ' • ' . $frmCongLabel, 'href' => "/admin/financial/export/csv?start_date={$filters['start_date']}&end_date={$filters['end_date']}&congregation_id={$filters['congregation_id']}"],
    ['icon' => 'fa-file-excel', 'label' => 'Exportar Excel (.xls)', 'href' => "/admin/financial/export/excel?start_date={$filters['start_date']}&end_date={$filters['end_date']}&congregation_id={$filters['congregation_id']}"],
    ['icon' => 'fa-print', 'label' => 'Imprimir relatório', 'sub' => 'Abre versão para impressão', 'href' => "/admin/financial/report/print?start_date={$filters['start_date']}&end_date={$filters['end_date']}&congregation_id={$filters['congregation_id']}", 'target' => '_blank'],
];
include __DIR__ . '/../../layout/mobile_page_header.php';
?>
<style>
    .frm-wrap { padding-bottom: 40px; }

    .frm-periodrow { display: flex; gap: .5rem; margin-bottom: 1rem; }
    .frm-period-pill { flex: 1 1 auto; background: #16213e; color: #fff; font-weight: 700; font-size: .82rem; padding: .6rem .9rem; border-radius: 12px; }
    .frm-filter-toggle { flex: 0 0 auto; width: 42px; height: 42px; border-radius: 12px; border: 1px solid rgba(17,24,39,.1); background: #fff; color: #16213e; }
    .frm-filter-toggle.is-active { background: #16213e; color: #fff; border-color: #16213e; }

    .frm-filter-panel { background: #fff; border: 1px solid rgba(17,24,39,.08); border-radius: 16px; padding: .9rem 1rem; margin-bottom: 1rem; }
    .frm-filter-panel-head { display: flex; align-items: center; justify-content: space-between; font-weight: 800; font-size: .82rem; color: #16213e; margin-bottom: .7rem; }
    .frm-filter-panel-head button { border: 1px solid rgba(17,24,39,.1); background: #fff; width: 26px; height: 26px; border-radius: 50%; color: #6c757d; }
    .frm-filter-hint { font-size: .68rem; color: #8b93a7; margin-top: .6rem; }

    .frm-balance-card { background: #16213e; border-radius: 18px; padding: 1.1rem 1.2rem; color: #fff; margin-bottom: 1rem; }
    .frm-balance-top { display: flex; align-items: center; justify-content: space-between; }
    .frm-balance-label { font-size: .68rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: rgba(255,255,255,.6); }
    .frm-balance-badge { font-size: .68rem; font-weight: 800; padding: .28rem .7rem; border-radius: 999px; background: rgba(220,38,38,.25); color: #fca5a5; }
    .frm-balance-badge.is-positive { background: rgba(22,163,74,.25); color: #86efac; }
    .frm-balance-value { font-size: 1.85rem; font-weight: 800; margin-top: .35rem; }
    .frm-balance-sub { font-size: .72rem; color: rgba(255,255,255,.5); margin-top: .1rem; margin-bottom: .8rem; }
    .frm-split-bar { display: flex; height: 6px; border-radius: 999px; overflow: hidden; background: rgba(255,255,255,.12); }
    .frm-split-bar .fill-in { background: #22c55e; height: 100%; }
    .frm-split-bar .fill-out { background: #ef4444; height: 100%; }
    .frm-split-labels { display: flex; justify-content: space-between; font-size: .64rem; color: rgba(255,255,255,.5); margin: .35rem 0 .9rem; }
    .frm-mini-pills { display: flex; gap: .6rem; }
    .frm-mini-pill { flex: 1 1 0; display: flex; align-items: center; gap: .5rem; background: rgba(255,255,255,.08); border-radius: 12px; padding: .55rem .7rem; }
    .frm-mini-pill-icon { width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .74rem; flex: 0 0 auto; }
    .frm-mini-pill.in .frm-mini-pill-icon { background: rgba(34,197,94,.25); color: #86efac; }
    .frm-mini-pill.out .frm-mini-pill-icon { background: rgba(239,68,68,.25); color: #fca5a5; }
    .frm-mini-pill-label { font-size: .6rem; font-weight: 700; color: rgba(255,255,255,.55); text-transform: uppercase; }
    .frm-mini-pill-value { font-size: .82rem; font-weight: 800; color: #fff; }

    .frm-segmented { display: flex; background: #f1f3f7; border-radius: 999px; padding: .25rem; margin-bottom: 1rem; }
    .frm-seg-btn { flex: 1 1 0; border: none; background: transparent; color: #6c757d; font-weight: 700; font-size: .8rem; padding: .5rem .4rem; border-radius: 999px; }
    .frm-seg-btn.active { background: #fff; color: #16213e; box-shadow: 0 2px 6px rgba(17,24,39,.08); }

    .frm-panel { display: none; }
    .frm-panel.active { display: block; }

    .frm-row-card { background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 14px; padding: .85rem 1rem; margin-bottom: .6rem; }
    .frm-row-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: .6rem; }
    .frm-row-title { font-size: .68rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; color: #8b93a7; }
    .frm-row-sub { font-size: .72rem; color: #8b93a7; margin-top: .2rem; }
    .frm-row-value { font-weight: 800; font-size: .92rem; color: #16213e; white-space: nowrap; }
    .frm-final-bar { background: #16213e; border-radius: 14px; padding: .85rem 1rem; display: flex; align-items: center; justify-content: space-between; color: #fff; margin-bottom: 1.2rem; }
    .frm-final-bar-label { font-size: .68rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; color: rgba(255,255,255,.6); }
    .frm-final-bar-sub { font-size: .72rem; color: rgba(255,255,255,.55); }
    .frm-final-bar-value { font-weight: 800; font-size: 1rem; }

    .frm-section-title { font-size: .78rem; font-weight: 800; letter-spacing: .03em; text-transform: uppercase; color: #16213e; margin-bottom: .7rem; }
    .frm-cat-card { margin-bottom: .8rem; }
    .frm-cat-top { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: .35rem; }
    .frm-cat-name { font-weight: 700; font-size: .84rem; color: #16213e; }
    .frm-cat-value { font-weight: 800; font-size: .84rem; color: #16213e; }
    .frm-cat-sub { font-size: .68rem; color: #8b93a7; margin-bottom: .35rem; }
    .frm-cat-bar { height: 7px; border-radius: 999px; background: #eef0f2; overflow: hidden; }
    .frm-cat-bar-fill { height: 100%; background: #16213e; border-radius: 999px; }
    .frm-cat-footer { font-size: .72rem; color: #2563eb; font-weight: 600; margin: .4rem 0 .2rem; }

    .frm-list-count { font-size: .74rem; color: #8b93a7; font-weight: 600; }

    .frm-item-card { background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 14px; padding: .75rem .85rem; margin-bottom: .55rem; }
    .frm-item-top { display: flex; align-items: center; gap: .6rem; }
    .frm-item-icon { flex: 0 0 auto; width: 36px; height: 36px; border-radius: 50%; background: rgba(37,99,235,.08); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: .9rem; }
    .frm-item-icon.is-in { background: rgba(22,163,74,.1); color: #16a34a; }
    .frm-item-id { min-width: 0; flex: 1 1 auto; }
    .frm-item-title { font-weight: 700; font-size: .84rem; color: #16213e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .frm-item-meta { font-size: .7rem; color: #8b93a7; margin-top: .1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .frm-item-value { flex: 0 0 auto; font-weight: 800; font-size: .86rem; color: #dc2626; }
    .frm-item-value.is-in { color: #16a34a; }
    .frm-item-detail { display: flex; gap: 1.2rem; margin-top: .65rem; padding-top: .65rem; border-top: 1px dashed rgba(17,24,39,.08); }
    .frm-item-detail-label { font-size: .62rem; font-weight: 700; text-transform: uppercase; color: #8b93a7; }
    .frm-item-detail-value { font-size: .78rem; font-weight: 700; color: #16213e; }

    .frm-total-bar { border-radius: 14px; padding: .85rem 1rem; display: flex; align-items: center; justify-content: space-between; color: #fff; margin-top: .8rem; }
    .frm-total-bar.is-out { background: #16213e; }
    .frm-total-bar.is-in { background: #16a34a; }
    .frm-total-bar-label { font-size: .7rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .frm-total-bar-value { font-weight: 800; font-size: 1rem; }

    .frm-empty { text-align: center; color: #adb5bd; font-size: .85rem; padding: 2rem 0; }
</style>

<div class="frm-wrap d-lg-none">
    <div class="frm-periodrow">
        <span class="frm-period-pill"><?= htmlspecialchars($frmPeriodShort) ?> • <?= htmlspecialchars($frmCongLabel) ?></span>
        <button type="button" class="frm-filter-toggle" id="frmFilterToggle" aria-label="Filtros"><i class="fas fa-sliders-h"></i></button>
    </div>

    <div class="frm-filter-panel d-none" id="frmFilterPanel">
        <div class="frm-filter-panel-head">
            <span>Filtros do Relatório</span>
            <button type="button" id="frmFilterClose" aria-label="Fechar"><i class="fas fa-xmark"></i></button>
        </div>
        <form method="GET" action="/admin/financial/report">
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label small mb-1">Data Início</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['start_date']) ?>">
                </div>
                <div class="col-6">
                    <label class="form-label small mb-1">Data Fim</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['end_date']) ?>">
                </div>
            </div>
            <?php if (empty($_SESSION['user_congregation_id']) || $_SESSION['user_congregation_id'] == 0): ?>
                <div class="mt-2">
                    <label class="form-label small mb-1">Congregação</label>
                    <select name="congregation_id" class="form-select form-select-sm">
                        <option value="">Todas (Geral)</option>
                        <?php foreach ($congregations as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($filters['congregation_id'] == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-dark w-100 rounded-pill mt-3">Filtrar</button>
        </form>
        <div class="frm-filter-hint">Período <?= $frmStart->format('d/m/Y') ?> a <?= $frmEnd->format('d/m/Y') ?> • Visão Geral <?= htmlspecialchars($frmCongLabel) ?></div>
    </div>

    <div class="frm-balance-card">
        <div class="frm-balance-top">
            <span class="frm-balance-label">Saldo do período</span>
            <span class="frm-balance-badge <?= !$frmIsNegative ? 'is-positive' : '' ?>"><?= $frmIsNegative ? 'Negativo' : 'Positivo' ?></span>
        </div>
        <div class="frm-balance-value"><?= $frmIsNegative ? '- ' : '' ?>R$ <?= number_format(abs($balance), 2, ',', '.') ?></div>
        <div class="frm-balance-sub"><?= $frmStart->format('d/m/Y') ?> a <?= $frmEnd->format('d/m/Y') ?></div>
        <div class="frm-split-bar">
            <div class="fill-in" style="width: <?= $frmEntriesPct ?>%;"></div>
            <div class="fill-out" style="width: <?= $frmExpensesPct ?>%;"></div>
        </div>
        <div class="frm-split-labels">
            <span><?= $frmEntriesPct ?>% entradas</span>
            <span><?= $frmExpensesPct ?>% saídas</span>
        </div>
        <div class="frm-mini-pills">
            <div class="frm-mini-pill in">
                <span class="frm-mini-pill-icon"><i class="fas fa-arrow-up"></i></span>
                <span>
                    <span class="frm-mini-pill-label d-block">Entradas</span>
                    <span class="frm-mini-pill-value">R$ <?= number_format($total_entries, 0, ',', '.') ?></span>
                </span>
            </div>
            <div class="frm-mini-pill out">
                <span class="frm-mini-pill-icon"><i class="fas fa-arrow-down"></i></span>
                <span>
                    <span class="frm-mini-pill-label d-block">Saídas</span>
                    <span class="frm-mini-pill-value">R$ <?= number_format($total_expenses, 0, ',', '.') ?></span>
                </span>
            </div>
        </div>
    </div>

    <div class="frm-segmented" id="frmSegmented">
        <button type="button" class="frm-seg-btn active" data-frm-tab="resumo">Resumo</button>
        <button type="button" class="frm-seg-btn" data-frm-tab="saidas">Saídas (<?= count($expenses) ?>)</button>
        <button type="button" class="frm-seg-btn" data-frm-tab="entradas">Entradas (<?= count($entries) ?>)</button>
    </div>

    <!-- ===================== RESUMO ===================== -->
    <div class="frm-panel active" data-frm-panel="resumo">
        <div class="frm-row-card">
            <div class="frm-row-card-top">
                <div>
                    <div class="frm-row-title">Entradas</div>
                    <div class="frm-row-sub">Dízimos R$ <?= number_format($total_tithes, 0, ',', '.') ?> | Ofertas R$ <?= number_format($total_offerings, 0, ',', '.') ?></div>
                </div>
                <div class="frm-row-value">R$ <?= number_format($total_entries, 2, ',', '.') ?></div>
            </div>
        </div>
        <div class="frm-row-card">
            <div class="frm-row-card-top">
                <div>
                    <div class="frm-row-title">Saídas</div>
                    <div class="frm-row-sub"><?= count($expenses) ?> lançamento<?= count($expenses) === 1 ? '' : 's' ?> no período</div>
                </div>
                <div class="frm-row-value">R$ <?= number_format($total_expenses, 2, ',', '.') ?></div>
            </div>
        </div>
        <div class="frm-final-bar">
            <div>
                <div class="frm-final-bar-label">Saldo Final</div>
                <div class="frm-final-bar-sub"><?= $frmIsNegative ? 'Negativo' : 'Positivo' ?> no período</div>
            </div>
            <div class="frm-final-bar-value"><?= $frmIsNegative ? '-' : '' ?> R$ <?= number_format(abs($balance), 2, ',', '.') ?></div>
        </div>

        <?php if (!empty($frmCategoryRows)): ?>
            <div class="frm-section-title">Resumo por Categoria</div>
            <?php foreach ($frmCategoryRows as $cat): ?>
                <div class="frm-cat-card">
                    <div class="frm-cat-top">
                        <span class="frm-cat-name"><?= htmlspecialchars($cat['label']) ?></span>
                        <span class="frm-cat-value">R$ <?= number_format($cat['amount'], 2, ',', '.') ?></span>
                    </div>
                    <div class="frm-cat-sub">R$ <?= number_format($cat['amount'], 2, ',', '.') ?> • <?= number_format($cat['pct'], 0, ',', '.') ?>% das saídas</div>
                    <div class="frm-cat-bar"><div class="frm-cat-bar-fill" style="width: <?= round($cat['pct']) ?>%;"></div></div>
                </div>
            <?php endforeach; ?>
            <div class="frm-cat-footer">Total saídas R$ <?= number_format($total_expenses, 2, ',', '.') ?> em <?= count($frmCategoryRows) ?> categoria<?= count($frmCategoryRows) === 1 ? '' : 's' ?></div>
        <?php endif; ?>
    </div>

    <!-- ===================== SAÍDAS ===================== -->
    <div class="frm-panel" data-frm-panel="saidas">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="frm-section-title mb-0">Detalhamento Saídas</div>
            <div class="frm-list-count"><?= count($expenses) ?> lançamento<?= count($expenses) === 1 ? '' : 's' ?></div>
        </div>
        <?php if (empty($expenses)): ?>
            <div class="frm-empty">Nenhuma saída registrada neste período.</div>
        <?php else: ?>
            <?php foreach ($expenses as $e): ?>
                <div class="frm-item-card">
                    <div class="frm-item-top">
                        <span class="frm-item-icon"><i class="fas <?= frmExpenseIcon($e['category'], $e['description']) ?>"></i></span>
                        <div class="frm-item-id">
                            <div class="frm-item-title"><?= htmlspecialchars($e['description']) ?></div>
                            <div class="frm-item-meta"><?= htmlspecialchars($e['category'] ?: 'Outros') ?> • <?= htmlspecialchars($e['congregation_name'] ?? 'Geral') ?> • <?= date('d/m', strtotime($e['expense_date'])) ?></div>
                        </div>
                        <div class="frm-item-value">- R$ <?= number_format($e['amount'], 2, ',', '.') ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="frm-total-bar is-out">
                <span class="frm-total-bar-label">Total Saídas</span>
                <span class="frm-total-bar-value">R$ <?= number_format($total_expenses, 2, ',', '.') ?></span>
            </div>
        <?php endif; ?>
    </div>

    <!-- ===================== ENTRADAS ===================== -->
    <div class="frm-panel" data-frm-panel="entradas">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="frm-section-title mb-0">Detalhamento Entradas</div>
            <div class="d-flex align-items-center gap-2">
                <div class="frm-list-count"><?= count($entries) ?> lançamento<?= count($entries) === 1 ? '' : 's' ?></div>
                <button type="button" class="frm-filter-toggle" style="width:32px;height:32px;" onclick="toggleTithes()" title="Exibir/Ocultar valores de Dízimos"><i class="fas fa-eye" id="toggleTithesIconMobile"></i></button>
            </div>
        </div>
        <?php if (empty($entries)): ?>
            <div class="frm-empty">Nenhuma entrada registrada neste período.</div>
        <?php else: ?>
            <?php foreach ($entries as $en):
                $displayName = $en['member_name'] ?? $en['giver_name'];
                if (empty($displayName)) {
                    if ($en['payment_method'] === 'Transferência/OFX' && !empty($en['notes'])) {
                        $displayName = 'OFX: ' . $en['notes'];
                    } elseif (!empty($en['notes'])) {
                        $displayName = 'Obs: ' . mb_strimwidth($en['notes'], 0, 30, '...');
                    } else {
                        $displayName = 'Não identificado';
                    }
                }
                $isTithe = preg_match('/d[ií]zimo/iu', (string)$en['type']);
            ?>
                <div class="frm-item-card">
                    <div class="frm-item-top">
                        <span class="frm-item-icon is-in"><i class="fas <?= $isTithe ? 'fa-heart' : 'fa-hand-holding-heart' ?>"></i></span>
                        <div class="frm-item-id">
                            <div class="frm-item-title"><?= htmlspecialchars($en['type']) ?> - <?= htmlspecialchars($displayName) ?></div>
                            <div class="frm-item-meta"><?= date('d/m/Y', strtotime($en['payment_date'])) ?> • <?= htmlspecialchars($en['type']) ?> • <?= htmlspecialchars($en['congregation_name'] ?? 'Geral') ?></div>
                        </div>
                        <div class="frm-item-value is-in">
                            <?php if ($isTithe): ?>
                                <span class="tithe-value d-none">+ R$ <?= number_format($en['amount'], 2, ',', '.') ?></span>
                                <span class="tithe-mask">+ ****</span>
                            <?php else: ?>
                                + R$ <?= number_format($en['amount'], 2, ',', '.') ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="frm-item-detail">
                        <div>
                            <div class="frm-item-detail-label">Tipo</div>
                            <div class="frm-item-detail-value"><?= htmlspecialchars($en['type']) ?></div>
                        </div>
                        <div>
                            <div class="frm-item-detail-label"><?= !empty($en['member_name']) ? 'Membro' : 'Doador' ?></div>
                            <div class="frm-item-detail-value"><?= htmlspecialchars($displayName) ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="frm-total-bar is-in">
                <span class="frm-total-bar-label">Total Entrada<?= count($entries) === 1 ? '' : 's' ?></span>
                <span class="frm-total-bar-value">R$ <?= number_format($total_entries, 2, ',', '.') ?></span>
            </div>
        <?php endif; ?>
    </div>

    <?php
    $mobilePageFooterLabel = 'Relatório Financeiro';
    include __DIR__ . '/../../layout/mobile_page_footer.php';
    ?>
</div>

<script>
(function () {
    var wrap = document.querySelector('.frm-wrap');
    if (!wrap) return;

    var filterToggle = document.getElementById('frmFilterToggle');
    var filterPanel = document.getElementById('frmFilterPanel');
    var filterClose = document.getElementById('frmFilterClose');
    function setFilterOpen(open) {
        filterPanel.classList.toggle('d-none', !open);
        filterToggle.classList.toggle('is-active', open);
    }
    if (filterToggle) filterToggle.addEventListener('click', function () { setFilterOpen(filterPanel.classList.contains('d-none')); });
    if (filterClose) filterClose.addEventListener('click', function () { setFilterOpen(false); });

    var segButtons = document.querySelectorAll('#frmSegmented .frm-seg-btn');
    segButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = btn.getAttribute('data-frm-tab');
            segButtons.forEach(function (b) { b.classList.toggle('active', b === btn); });
            wrap.querySelectorAll('.frm-panel').forEach(function (panel) {
                panel.classList.toggle('active', panel.getAttribute('data-frm-panel') === target);
            });
        });
    });
})();
</script>
