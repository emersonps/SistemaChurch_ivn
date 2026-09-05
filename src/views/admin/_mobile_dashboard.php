<?php
// src/views/admin/_mobile_dashboard.php
// Mobile/tablet (<992px) presentation of the same dashboard data already loaded by
// DashboardController::index() ($members_count, $tithes_sum, $offerings_sum,
// $total_financial, $congregation_stats, $birthdays, $next_events, $selected_month,
// $selected_year). Desktop keeps the classic single-page stat row/table/lists
// untouched (hidden via d-none d-lg-block/flex there) — this split into 3 separate
// mobile "screens" (Visão Geral / Aniversariantes / Próx. Eventos), chosen via
// ?view=, is mobile-only; desktop always shows everything on one page as before.

$mdbMonths = [
    '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril',
    '05' => 'Maio', '06' => 'Junho', '07' => 'Julho', '08' => 'Agosto',
    '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro',
];
$mdbMonthLabel = $mdbMonths[$selected_month] ?? $selected_month;

$mdbView = $_GET['view'] ?? 'overview';
if (!in_array($mdbView, ['overview', 'aniversariantes', 'eventos'], true)) {
    $mdbView = 'overview';
}
$mdbTitles = [
    'overview' => 'Financeiro',
    'aniversariantes' => 'Aniversariantes do Mês',
    'eventos' => 'Próximos Eventos',
];

function mdbInitials($name) {
    $parts = preg_split('/\s+/', trim((string)$name));
    $out = '';
    if (!empty($parts[0])) $out .= mb_substr($parts[0], 0, 1);
    if (count($parts) > 1) $out .= mb_substr(end($parts), 0, 1);
    return mb_strtoupper($out !== '' ? $out : '?');
}

$mdbWeekdayNames = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
$mdbAvatarPalette = ['#2563eb', '#16a34a', '#db2777', '#7c3aed', '#ea580c', '#0891b2'];

// Split $birthdays (this month) into "essa semana" (next 7 days from today, real date)
// and "próximos" (everyone else this month) — today's own birthdays are shown
// separately via the real $today_birthdays list (accurate regardless of month filter).
$mdbWeekList = [];
$mdbUpcomingList = [];
if ($mdbView === 'aniversariantes') {
    $mdbTodayIds = array_column($today_birthdays, 'id');
    $mdbToday = new DateTimeImmutable('today');
    foreach ($birthdays as $b) {
        if (in_array($b['id'], $mdbTodayIds, true)) continue;
        $bMonth = date('m', strtotime($b['birth_date']));
        $bDay = date('d', strtotime($b['birth_date']));
        $candidate = DateTimeImmutable::createFromFormat('Y-m-d', $mdbToday->format('Y') . '-' . $bMonth . '-' . $bDay);
        $diffDays = $candidate ? (int)floor(($candidate->getTimestamp() - $mdbToday->getTimestamp()) / 86400) : 999;
        $row = $b;
        $row['_candidate'] = $candidate;
        $row['_diff_days'] = $diffDays;
        if ($diffDays >= 0 && $diffDays <= 7) {
            $mdbWeekList[] = $row;
        } else {
            $mdbUpcomingList[] = $row;
        }
    }
    usort($mdbWeekList, function ($a, $b) { return $a['_diff_days'] <=> $b['_diff_days']; });
    usort($mdbUpcomingList, function ($a, $b) { return (int)date('d', strtotime($a['birth_date'])) <=> (int)date('d', strtotime($b['birth_date'])); });
}
?>
<style>
    .mdb-wrap { padding-bottom: 40px; }
    .mdb-filterrow { display: flex; gap: .5rem; margin-bottom: 1.1rem; }
    .mdb-period-pill { flex: 1 1 auto; background: #16213e; color: #fff; font-weight: 700; font-size: .82rem; padding: .6rem .9rem; border-radius: 12px; text-align: center; }
    .mdb-icon-btn { flex: 0 0 auto; width: 42px; height: 42px; border-radius: 12px; border: 1px solid rgba(17,24,39,.1); background: #fff; color: #16213e; display: flex; align-items: center; justify-content: center; }

    .mdb-stats { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; margin-bottom: 1.2rem; }
    .mdb-stat { border-radius: 16px; padding: .9rem 1rem; border: 1px solid rgba(17,24,39,.06); background: #fff; }
    .mdb-stat-icon { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .95rem; margin-bottom: .5rem; }
    .mdb-stat.is-members .mdb-stat-icon { background: rgba(37,99,235,.1); color: #2563eb; }
    .mdb-stat.is-tithes .mdb-stat-icon { background: rgba(22,163,74,.1); color: #16a34a; }
    .mdb-stat.is-offerings .mdb-stat-icon { background: rgba(8,145,178,.1); color: #0891b2; }
    .mdb-stat.is-total { background: #16213e; }
    .mdb-stat.is-total .mdb-stat-icon { background: rgba(255,255,255,.12); color: #fff; }
    .mdb-stat-label { font-size: .64rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #8b93a7; }
    .mdb-stat.is-total .mdb-stat-label { color: rgba(255,255,255,.65); }
    .mdb-stat-value { font-size: 1.05rem; font-weight: 800; color: #16213e; margin-top: .1rem; }
    .mdb-stat.is-total .mdb-stat-value { color: #fff; }

    .mdb-section-header { display: flex; align-items: center; justify-content: space-between; margin: 1.2rem 0 .7rem; }
    .mdb-section-title { font-weight: 800; font-size: .92rem; color: #16213e; }

    .mdb-cong-card { background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 16px; padding: .8rem .9rem; margin-bottom: .55rem; }
    .mdb-cong-top { display: flex; align-items: center; justify-content: space-between; }
    .mdb-cong-name { font-weight: 700; font-size: .86rem; color: #16213e; }
    .mdb-cong-members { font-size: .68rem; font-weight: 700; color: #8b93a7; background: #f1f3f7; padding: .18rem .55rem; border-radius: 999px; }
    .mdb-cong-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: .5rem; margin-top: .6rem; }
    .mdb-cong-cell-label { font-size: .6rem; font-weight: 700; text-transform: uppercase; color: #8b93a7; }
    .mdb-cong-cell-value { font-size: .78rem; font-weight: 800; color: #16213e; }

    .mdb-bday-card { display: flex; align-items: center; justify-content: space-between; gap: .6rem; background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 14px; padding: .65rem .85rem; margin-bottom: .5rem; }
    .mdb-bday-card.is-today { background: rgba(255,193,7,.08); border-color: rgba(255,193,7,.3); }
    .mdb-bday-name { font-weight: 700; font-size: .84rem; color: #16213e; }
    .mdb-today-pill { display: inline-block; margin-left: .4rem; font-size: .62rem; font-weight: 700; padding: .12rem .5rem; border-radius: 999px; background: #ffc107; color: #212529; }
    .mdb-bday-right { display: flex; align-items: center; gap: .5rem; flex: 0 0 auto; }
    .mdb-date-pill { font-size: .7rem; font-weight: 700; padding: .2rem .6rem; border-radius: 999px; background: rgba(8,145,178,.1); color: #0891b2; }
    .mdb-gift-btn { width: 30px; height: 30px; border-radius: 50%; border: 1px solid rgba(22,163,74,.3); background: #fff; color: #16a34a; display: flex; align-items: center; justify-content: center; }

    .mdb-event-card { display: flex; align-items: center; gap: .7rem; background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 14px; padding: .75rem .85rem; margin-bottom: .55rem; }
    .mdb-event-icon { flex: 0 0 auto; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: .95rem; }
    .mdb-event-id { flex: 1 1 auto; min-width: 0; }
    .mdb-event-title { font-size: .86rem; font-weight: 700; color: #16213e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .mdb-event-meta { font-size: .72rem; color: #8b93a7; margin-top: .1rem; }
    .mdb-event-right { flex: 0 0 auto; text-align: right; }
    .mdb-event-date { font-size: .7rem; font-weight: 700; padding: .2rem .6rem; border-radius: 999px; background: #f1f3f7; color: #16213e; white-space: nowrap; }
    .mdb-event-weekday { display: block; font-size: .62rem; color: #adb5bd; margin-top: .25rem; text-transform: uppercase; font-weight: 700; }

    .mdb-empty { text-align: center; color: #adb5bd; font-size: .82rem; padding: 1.2rem 0; }

    /* ---------- Celebrações (Aniversariantes) ---------- */
    .mdb-cel-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
    .mdb-cel-title { font-size: 1.3rem; font-weight: 800; color: #16213e; }
    .mdb-cel-sub { font-size: .78rem; color: #8b93a7; }
    .mdb-cel-icon { flex: 0 0 auto; width: 46px; height: 46px; border-radius: 14px; background: rgba(124,58,237,.1); color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }

    .mdb-count-pill { flex: 0 0 auto; font-size: .72rem; font-weight: 700; color: #8b93a7; background: #f1f3f7; padding: .35rem .7rem; border-radius: 999px; display: flex; align-items: center; }

    .mdb-hero-card { background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%); border-radius: 20px; padding: 1.1rem 1.2rem; color: #fff; margin-bottom: 1.2rem; }
    .mdb-hero-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: .8rem; }
    .mdb-hero-badge { font-size: .66rem; font-weight: 800; letter-spacing: .04em; background: rgba(255,255,255,.18); padding: .3rem .65rem; border-radius: 999px; }
    .mdb-hero-live { font-size: .66rem; font-weight: 800; background: rgba(22,163,74,.9); padding: .3rem .65rem; border-radius: 999px; display: inline-flex; align-items: center; gap: .3rem; }
    .mdb-hero-live i { font-size: .5rem; }
    .mdb-hero-body { display: flex; align-items: center; gap: .8rem; margin-bottom: 1rem; }
    .mdb-hero-avatar { flex: 0 0 auto; width: 56px; height: 56px; border-radius: 16px; background: #fff; color: #7c3aed; font-weight: 800; font-size: 1.3rem; display: flex; align-items: center; justify-content: center; }
    .mdb-hero-id { min-width: 0; }
    .mdb-hero-name { font-size: 1.05rem; font-weight: 800; }
    .mdb-hero-meta { font-size: .78rem; color: rgba(255,255,255,.75); margin-bottom: .4rem; }
    .mdb-hero-pill { display: inline-block; font-size: .66rem; font-weight: 700; background: rgba(255,255,255,.18); padding: .22rem .6rem; border-radius: 999px; }
    .mdb-hero-btn { width: 100%; border: none; background: #16a34a; color: #fff; border-radius: 999px; padding: .75rem; font-size: .86rem; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: .5rem; }
    .mdb-hero-btn-greeted { display: none; align-items: center; gap: .5rem; }
    .mdb-hero-btn.is-greeted { background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.4); }
    .mdb-hero-btn.is-greeted .mdb-hero-btn-default { display: none; }
    .mdb-hero-btn.is-greeted .mdb-hero-btn-greeted { display: flex; }
    .mdb-hero-more { display: flex; align-items: center; gap: .5rem; margin-top: .8rem; font-size: .76rem; color: rgba(255,255,255,.85); }
    .mdb-hero-more-avatar { flex: 0 0 auto; width: 26px; height: 26px; border-radius: 50%; background: rgba(255,255,255,.2); color: #fff; font-size: .62rem; font-weight: 800; display: flex; align-items: center; justify-content: center; }

    .mdb-range-pill { font-size: .68rem; font-weight: 700; color: #8b93a7; background: #f1f3f7; padding: .25rem .6rem; border-radius: 999px; }
    .mdb-count-text { font-size: .72rem; font-weight: 600; color: #8b93a7; }

    .mdb-list-card { display: flex; align-items: center; gap: .65rem; background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 14px; padding: .7rem .85rem; margin-bottom: .55rem; cursor: pointer; }
    .mdb-list-avatar { flex: 0 0 auto; width: 38px; height: 38px; border-radius: 50%; background: rgba(37,99,235,.1); color: #2563eb; font-weight: 800; font-size: .78rem; display: flex; align-items: center; justify-content: center; }
    .mdb-list-id { min-width: 0; flex: 1 1 auto; }
    .mdb-list-name { font-weight: 700; font-size: .84rem; color: #16213e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .mdb-list-meta { font-size: .7rem; color: #8b93a7; }
    .mdb-list-right { flex: 0 0 auto; display: flex; flex-direction: column; align-items: flex-end; gap: .3rem; }
    .mdb-list-date { font-size: .7rem; font-weight: 700; color: #2563eb; background: rgba(37,99,235,.08); padding: .2rem .55rem; border-radius: 999px; white-space: nowrap; }
    .mdb-rel-pill { font-size: .62rem; font-weight: 700; color: #6c757d; background: #f1f3f7; padding: .15rem .5rem; border-radius: 999px; }
    .mdb-rel-pill.is-today { color: #198754; background: rgba(25,135,84,.12); }
    .mdb-list-card.is-compact .mdb-list-date { color: #6c757d; background: #f1f3f7; }

    /* ---------- Birthday detail sheet ---------- */
    .mdb-detail-sheet.offcanvas-bottom { border-top-left-radius: 20px; border-top-right-radius: 20px; height: auto; max-height: 92vh; }
    .mdb-detail-close { position: absolute; top: 1rem; right: 1rem; width: 30px; height: 30px; border-radius: 50%; border: 1px solid rgba(17,24,39,.1); background: #fff; color: #6c757d; z-index: 1; }
    .mdb-detail-body { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 1.6rem 1.2rem 1.4rem; }
    .mdb-detail-avatar { width: 74px; height: 74px; border-radius: 20px; background: rgba(37,99,235,.1); color: #2563eb; font-weight: 800; font-size: 1.5rem; display: flex; align-items: center; justify-content: center; margin-bottom: .8rem; }
    .mdb-detail-datepill { font-size: .74rem; font-weight: 700; color: #2563eb; background: rgba(37,99,235,.08); padding: .3rem .8rem; border-radius: 999px; margin-bottom: .6rem; }
    .mdb-detail-name { font-size: 1.05rem; font-weight: 800; color: #16213e; }
    .mdb-detail-meta { font-size: .8rem; color: #8b93a7; margin-bottom: 1.1rem; }
    .mdb-detail-body .mdb-hero-btn { background: #16a34a; margin-bottom: .7rem; }
    .mdb-detail-actions { display: flex; gap: .6rem; width: 100%; }
    .mdb-detail-btn { flex: 1 1 0; display: flex; align-items: center; justify-content: center; gap: .4rem; border-radius: 999px; padding: .65rem; font-size: .82rem; font-weight: 700; text-decoration: none; }
    .mdb-detail-btn.is-dark { background: #16213e; color: #fff; }
    .mdb-detail-btn.is-outline { background: #fff; border: 1px solid rgba(17,24,39,.1); color: #16213e; }
    .mdb-detail-footnote { font-size: .68rem; color: #adb5bd; margin-top: 1rem; }

    .mdb-sheet .offcanvas-header { border-bottom: 1px solid rgba(0,0,0,.06); }
    .mdb-sheet.offcanvas-bottom { border-top-left-radius: 20px; border-top-right-radius: 20px; height: auto; max-height: 92vh; }

    /* ---------- Financeiro (overview) ---------- */
    .mdb-fin-toprow { display: flex; justify-content: flex-end; align-items: center; gap: .5rem; margin: -.5rem 0 .9rem; }
    .mdb-fin-pill { background: #16213e; color: #fff; font-weight: 700; font-size: .78rem; padding: .45rem .9rem; border-radius: 999px; border: none; }
    .mdb-fin-eye { flex: 0 0 auto; width: 36px; height: 36px; border-radius: 50%; border: 1px solid rgba(17,24,39,.1); background: #fff; color: #16213e; display: flex; align-items: center; justify-content: center; }

    .mdb-fin-hero { background: #16213e; border-radius: 20px; padding: 1.2rem 1.3rem 1.1rem; color: #fff; margin-bottom: 1.3rem; }
    .mdb-fin-hero-top { display: flex; align-items: flex-start; justify-content: space-between; gap: .6rem; }
    .mdb-fin-value { font-size: 1.7rem; font-weight: 800; line-height: 1.15; }
    .mdb-fin-sub { font-size: .76rem; color: rgba(255,255,255,.6); margin-top: .15rem; }
    .mdb-fin-trend { flex: 0 0 auto; text-align: right; font-size: .74rem; font-weight: 700; display: flex; align-items: center; gap: .3rem; margin-top: .3rem; }
    .mdb-fin-trend.is-up { color: #4ade80; }
    .mdb-fin-trend.is-down { color: #f87171; }
    .mdb-fin-divider { border-top: 1px solid rgba(255,255,255,.12); margin: 1rem 0 .8rem; }
    .mdb-fin-quickstats { display: flex; align-items: center; flex-wrap: wrap; gap: .35rem; font-size: .74rem; color: rgba(255,255,255,.85); }
    .mdb-fin-quickstats .is-tithe { color: #4ade80; font-weight: 700; }
    .mdb-fin-quickstats .is-offering { color: #38bdf8; font-weight: 700; }
    .mdb-fin-quickstats .sep { color: rgba(255,255,255,.35); }
    .mdb-fin-health-dot { width: 9px; height: 9px; border-radius: 50%; margin-left: auto; flex: 0 0 auto; }
    .mdb-fin-health-dot.is-positive, .mdb-fin-health-dot.is-stable { background: #4ade80; }
    .mdb-fin-health-dot.is-attention { background: #f87171; }
    .mdb-fin-health-dot.is-none { background: rgba(255,255,255,.25); }

    .mdb-conglist { background: #fff; border: 1px solid rgba(17,24,39,.06); border-radius: 16px; overflow: hidden; }
    .mdb-conglist-row { display: flex; align-items: center; gap: .6rem; padding: .8rem 1rem; border-bottom: 1px solid rgba(17,24,39,.06); text-decoration: none; }
    .mdb-conglist-row:last-child { border-bottom: none; }
    .mdb-conglist-dot { flex: 0 0 auto; width: 8px; height: 8px; border-radius: 50%; background: #d7dbe3; }
    .mdb-conglist-row.is-active .mdb-conglist-dot { background: #16213e; }
    .mdb-conglist-name { flex: 1 1 auto; min-width: 0; font-size: .84rem; font-weight: 700; color: #adb5bd; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .mdb-conglist-row.is-active .mdb-conglist-name { color: #16213e; }
    .mdb-conglist-amount { flex: 0 0 auto; font-size: .84rem; font-weight: 700; color: #adb5bd; }
    .mdb-conglist-row.is-active .mdb-conglist-amount { color: #16213e; }
    .mdb-conglist-badge { flex: 0 0 auto; font-size: .66rem; font-weight: 700; color: #8b93a7; background: #f1f3f7; padding: .18rem .5rem; border-radius: 999px; min-width: 1.6rem; text-align: center; }
    .mdb-conglist-chevron { flex: 0 0 auto; color: #ced4da; font-size: .78rem; }
    .mdb-tap-hint { text-align: center; font-size: .7rem; color: #adb5bd; margin-top: .8rem; }
</style>

<div class="mdb-wrap d-lg-none">
    <?php
    $mobilePageCategory = $mdbView === 'overview' ? 'Financeiro' : 'Painel';
    $mobilePageTitle = $mdbView === 'eventos' ? $mdbTitles[$mdbView] : null;
    include __DIR__ . '/../layout/mobile_page_header.php';
    ?>

    <?php if ($mdbView === 'overview'):
        $mdbCanReport = hasPermission('financial.view');
        $mdbPrevAbbrev = mb_strtolower(mb_substr($mdbMonths[$prev_month] ?? '', 0, 3));
        $mdbMonthStart = "$selected_year-$selected_month-01";
        $mdbMonthEnd = date('Y-m-t', strtotime($mdbMonthStart));
        $mdbHealthTier = $health_tier ?? 'none';
    ?>

        <div class="mdb-fin-toprow">
            <?php if ($canToggleFinancialValues): ?>
                <button type="button" class="mdb-fin-eye" id="toggle-dashboard-values-mobile" aria-label="Exibir valores"><i class="fas fa-eye"></i></button>
            <?php endif; ?>
            <?php if (!$hideFinancialForRole): ?>
                <button type="button" class="mdb-fin-pill" data-bs-toggle="offcanvas" data-bs-target="#mdbFilterSheet"><i class="far fa-calendar me-1"></i> <?= htmlspecialchars($mdbMonthLabel) ?> / <?= htmlspecialchars($selected_year) ?></button>
            <?php endif; ?>
        </div>

        <?php if ($hideFinancialForRole): ?>
            <div class="mdb-fin-hero">
                <div class="mdb-fin-quickstats">
                    <span><?= $members_count ?> membros</span>
                </div>
            </div>
        <?php else: ?>
        <div class="mdb-fin-hero">
            <div class="mdb-fin-hero-top">
                <div>
                    <div class="mdb-fin-value sensitive-dashboard-value" data-value="<?= htmlspecialchars($totalFinancialFormatted) ?>"><?= $canToggleFinancialValues ? 'R$ ••••••' : $totalFinancialFormatted ?></div>
                    <div class="mdb-fin-sub">Total arrecadado em <?= htmlspecialchars($mdbMonthLabel) ?></div>
                </div>
                <?php if ($financial_trend_pct !== null): ?>
                    <div class="mdb-fin-trend <?= $financial_trend_pct >= 0 ? 'is-up' : 'is-down' ?>">
                        <i class="fas fa-arrow-trend-<?= $financial_trend_pct >= 0 ? 'up' : 'down' ?>"></i>
                        <?= $financial_trend_pct >= 0 ? '+' : '' ?><?= $financial_trend_pct ?>% vs <?= htmlspecialchars($mdbPrevAbbrev) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="mdb-fin-divider"></div>
            <div class="mdb-fin-quickstats">
                <span class="is-tithe sensitive-dashboard-value" data-value="Dízimos <?= htmlspecialchars($tithesSumFormatted) ?>">Dízimos <?= $canToggleFinancialValues ? '••••••' : $tithesSumFormatted ?></span>
                <span class="sep">•</span>
                <span class="is-offering sensitive-dashboard-value" data-value="Ofertas <?= htmlspecialchars($offeringsSumFormatted) ?>">Ofertas <?= $canToggleFinancialValues ? '••••••' : $offeringsSumFormatted ?></span>
                <span class="sep">•</span>
                <span><?= $members_count ?> membros</span>
                <span class="mdb-fin-health-dot is-<?= htmlspecialchars($mdbHealthTier) ?>"></span>
            </div>
        </div>
        <?php endif; ?>

        <div class="mdb-section-header">
            <div class="mdb-section-title">POR CONGREGAÇÃO</div>
            <span class="mdb-count-text"><?= $congregations_count ?> unidade<?= $congregations_count === 1 ? '' : 's' ?></span>
        </div>
        <?php if (empty($congregation_stats)): ?>
            <div class="mdb-empty">Nenhuma congregação cadastrada.</div>
        <?php else: ?>
            <div class="mdb-conglist">
                <?php foreach ($congregation_stats as $stat):
                    $total = $stat['tithe_sum'] + $stat['offering_sum'];
                    $totalValue = 'R$ ' . number_format($total, 2, ',', '.');
                    $mdbCongHref = $mdbCanReport
                        ? '/admin/financial/report?congregation_id=' . (int)$stat['id'] . '&start_date=' . $mdbMonthStart . '&end_date=' . $mdbMonthEnd
                        : null;
                    $mdbCongTag = $mdbCongHref ? 'a' : 'div';
                ?>
                    <<?= $mdbCongTag ?> class="mdb-conglist-row <?= $total > 0 ? 'is-active' : '' ?>" <?= $mdbCongHref ? 'href="' . htmlspecialchars($mdbCongHref) . '"' : '' ?>>
                        <span class="mdb-conglist-dot"></span>
                        <span class="mdb-conglist-name"><?= htmlspecialchars($stat['congregation_name']) ?></span>
                        <?php if (!$hideFinancialForRole): ?>
                        <span class="mdb-conglist-amount sensitive-dashboard-value" data-value="<?= htmlspecialchars($totalValue) ?>"><?= $canToggleFinancialValues ? '••••••' : $totalValue ?></span>
                        <?php endif; ?>
                        <span class="mdb-conglist-badge"><?= $stat['member_count'] ?></span>
                        <?php if ($mdbCongHref): ?><i class="fas fa-chevron-right mdb-conglist-chevron"></i><?php endif; ?>
                    </<?= $mdbCongTag ?>>
                <?php endforeach; ?>
            </div>
            <?php if ($mdbCanReport): ?><div class="mdb-tap-hint">Toque para detalhes</div><?php endif; ?>
        <?php endif; ?>

    <?php elseif ($mdbView === 'aniversariantes'): ?>

        <?php if (hasPermission('members.view')): ?>
            <div class="mdb-cel-header">
                <div>
                    <div class="mdb-cel-title">Celebrações</div>
                    <div class="mdb-cel-sub">Alegria de compartilhar a vida</div>
                </div>
                <span class="mdb-cel-icon"><i class="fas fa-cake-candles"></i></span>
            </div>

            <div class="mdb-filterrow">
                <span class="mdb-period-pill"><?= htmlspecialchars($mdbMonthLabel) ?> <?= htmlspecialchars($selected_year) ?></span>
                <span class="mdb-count-pill"><?= count($birthdays) ?> aniversariante<?= count($birthdays) === 1 ? '' : 's' ?></span>
                <button type="button" class="mdb-icon-btn" data-bs-toggle="offcanvas" data-bs-target="#mdbFilterSheet" aria-label="Alterar período"><i class="fas fa-sliders-h"></i></button>
            </div>

            <?php if (!empty($today_birthdays)):
                $mdbHero = $today_birthdays[0];
                $mdbHeroExtra = array_slice($today_birthdays, 1);
            ?>
                <div class="mdb-hero-card">
                    <div class="mdb-hero-top">
                        <span class="mdb-hero-badge"><i class="fas fa-sparkles"></i> HOJE • <?= date('d/m', strtotime($mdbHero['birth_date'])) ?></span>
                        <span class="mdb-hero-live"><i class="fas fa-circle"></i> ao vivo</span>
                    </div>
                    <div class="mdb-hero-body">
                        <span class="mdb-hero-avatar"><?= htmlspecialchars(mdbInitials($mdbHero['name'])) ?></span>
                        <div class="mdb-hero-id">
                            <div class="mdb-hero-name"><?= htmlspecialchars($mdbHero['name']) ?></div>
                            <div class="mdb-hero-meta"><?= htmlspecialchars($mdbHero['congregation_name'] ?? 'Sem congregação') ?> • <?= date('d/m', strtotime($mdbHero['birth_date'])) ?></div>
                            <span class="mdb-hero-pill">faz aniversário hoje!</span>
                        </div>
                    </div>
                    <button type="button" class="mdb-hero-btn" data-birthday-id="<?= (int)$mdbHero['id'] ?>" onclick="openBirthdayCard('<?= addslashes(htmlspecialchars($mdbHero['name'])) ?>', <?= (int)$mdbHero['id'] ?>)">
                        <span class="mdb-hero-btn-default"><i class="fas fa-comment"></i> Parabenizar no WhatsApp</span>
                        <span class="mdb-hero-btn-greeted"><i class="fas fa-check"></i> Mensagem enviada</span>
                    </button>
                    <?php if (!empty($mdbHeroExtra)): ?>
                        <div class="mdb-hero-more">
                            <span class="mdb-hero-more-avatar"><?= htmlspecialchars(mdbInitials($mdbHeroExtra[0]['name'])) ?></span>
                            <span>+<?= count($mdbHeroExtra) ?> também hoje • <?= htmlspecialchars($mdbHeroExtra[0]['name']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($mdbWeekList)): ?>
                <div class="mdb-section-header">
                    <div class="mdb-section-title">Essa semana</div>
                    <span class="mdb-range-pill"><?= (new DateTimeImmutable('today'))->format('d/m') ?> a <?= (new DateTimeImmutable('today'))->modify('+7 days')->format('d/m') ?></span>
                </div>
                <?php foreach ($mdbWeekList as $idx => $b):
                    $mdbColor = $mdbAvatarPalette[$idx % count($mdbAvatarPalette)];
                    $mdbWd = $mdbWeekdayNames[(int)$b['_candidate']->format('w')];
                    $mdbRel = $b['_diff_days'] === 0 ? 'hoje' : ($b['_diff_days'] === 1 ? 'amanhã' : 'em ' . $b['_diff_days'] . ' dias');
                ?>
                    <div class="mdb-list-card" data-mdb-open data-bs-toggle="offcanvas" data-bs-target="#mdbBdaySheet"
                         data-id="<?= (int)$b['id'] ?>" data-name="<?= htmlspecialchars($b['name']) ?>"
                         data-cong="<?= htmlspecialchars($b['congregation_name'] ?? 'Sem congregação') ?>"
                         data-date="<?= date('d/m', strtotime($b['birth_date'])) ?>" data-weekday="<?= $mdbWd ?>"
                         data-rel="<?= htmlspecialchars($mdbRel) ?>" data-phone="<?= htmlspecialchars($b['phone'] ?? '') ?>">
                        <span class="mdb-list-avatar" style="background: <?= $mdbColor ?>1a; color: <?= $mdbColor ?>;"><?= htmlspecialchars(mdbInitials($b['name'])) ?></span>
                        <div class="mdb-list-id">
                            <div class="mdb-list-name"><?= htmlspecialchars($b['name']) ?></div>
                            <div class="mdb-list-meta"><?= htmlspecialchars($b['congregation_name'] ?? 'Sem congregação') ?></div>
                        </div>
                        <div class="mdb-list-right">
                            <span class="mdb-list-date"><?= date('d/m', strtotime($b['birth_date'])) ?> • <?= $mdbWd ?></span>
                            <span class="mdb-rel-pill <?= $b['_diff_days'] === 0 ? 'is-today' : '' ?>"><?= htmlspecialchars($mdbRel) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($mdbUpcomingList)): ?>
                <div class="mdb-section-header">
                    <div class="mdb-section-title">Próximos</div>
                    <span class="mdb-count-text"><?= count($mdbUpcomingList) ?> pessoa<?= count($mdbUpcomingList) === 1 ? '' : 's' ?></span>
                </div>
                <?php foreach ($mdbUpcomingList as $b): ?>
                    <div class="mdb-list-card is-compact" data-mdb-open data-bs-toggle="offcanvas" data-bs-target="#mdbBdaySheet"
                         data-id="<?= (int)$b['id'] ?>" data-name="<?= htmlspecialchars($b['name']) ?>"
                         data-cong="<?= htmlspecialchars($b['congregation_name'] ?? 'Sem congregação') ?>"
                         data-date="<?= date('d/m', strtotime($b['birth_date'])) ?>" data-weekday="" data-rel=""
                         data-phone="<?= htmlspecialchars($b['phone'] ?? '') ?>">
                        <span class="mdb-list-avatar"><?= htmlspecialchars(mdbInitials($b['name'])) ?></span>
                        <div class="mdb-list-id">
                            <div class="mdb-list-name"><?= htmlspecialchars($b['name']) ?></div>
                        </div>
                        <span class="mdb-list-date"><?= date('d/m', strtotime($b['birth_date'])) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (empty($today_birthdays) && empty($mdbWeekList) && empty($mdbUpcomingList)): ?>
                <div class="mdb-empty">Nenhum aniversariante este mês.</div>
            <?php endif; ?>
        <?php else: ?>
            <div class="mdb-empty">Você não tem permissão para ver esta lista.</div>
        <?php endif; ?>

    <?php elseif ($mdbView === 'eventos'):
        $mdbEventCatMeta = [
            'culto' => ['label' => 'Culto', 'color' => '#2563eb'],
            'evento' => ['label' => 'Evento', 'color' => '#7c3aed'],
            'convite' => ['label' => 'Convite Especial', 'color' => '#db2777'],
            'interno' => ['label' => 'Interno', 'color' => '#6c757d'],
        ];
    ?>

        <?php if (hasPermission('events.view')): ?>
            <?php if (empty($next_events)): ?>
                <div class="mdb-empty">Nenhum evento próximo.</div>
            <?php else: ?>
                <div class="mdb-section-header">
                    <div></div>
                    <span class="mdb-count-text"><?= count($next_events) ?> agendado<?= count($next_events) === 1 ? '' : 's' ?></span>
                </div>
                <?php foreach ($next_events as $e):
                    $mdbCat = $mdbEventCatMeta[$e['type'] ?? ''] ?? ['label' => 'Evento', 'color' => '#7c3aed'];
                    $mdbEventTs = strtotime($e['event_date']);
                ?>
                    <div class="mdb-event-card">
                        <span class="mdb-event-icon" style="background: <?= $mdbCat['color'] ?>1a; color: <?= $mdbCat['color'] ?>;"><i class="fas fa-calendar-alt"></i></span>
                        <div class="mdb-event-id">
                            <div class="mdb-event-title"><?= htmlspecialchars($e['title']) ?></div>
                            <div class="mdb-event-meta"><?= htmlspecialchars($mdbCat['label']) ?><?php if (!empty($e['location'])): ?> • <?= htmlspecialchars($e['location']) ?><?php endif; ?></div>
                        </div>
                        <div class="mdb-event-right">
                            <span class="mdb-event-date"><?= date('d/m', $mdbEventTs) ?></span>
                            <span class="mdb-event-weekday"><?= $mdbWeekdayNames[(int)date('w', $mdbEventTs)] ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php else: ?>
            <div class="mdb-empty">Você não tem permissão para ver esta lista.</div>
        <?php endif; ?>

    <?php endif; ?>

    <?php
    $mobilePageFooterLabel = $mdbTitles[$mdbView];
    include __DIR__ . '/../layout/mobile_page_footer.php';
    ?>
</div>

<?php if ($mdbView === 'overview' || $mdbView === 'aniversariantes'): ?>
<div class="offcanvas offcanvas-bottom mdb-sheet" tabindex="-1" id="mdbFilterSheet">
    <div class="offcanvas-header">
        <h6 class="offcanvas-title fw-bold">Período</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body">
        <form method="GET" action="/admin">
            <input type="hidden" name="view" value="<?= htmlspecialchars($mdbView) ?>">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Mês</label>
                <select name="month" class="form-select">
                    <?php foreach ($mdbMonths as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $k == $selected_month ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Ano</label>
                <select name="year" class="form-select">
                    <?php for ($y = date('Y'); $y >= 2015; $y--): ?>
                        <option value="<?= $y ?>" <?= $y == $selected_year ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-dark w-100 rounded-pill">Aplicar</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($mdbView === 'aniversariantes' && hasPermission('members.view')): ?>
<div class="offcanvas offcanvas-bottom mdb-detail-sheet" tabindex="-1" id="mdbBdaySheet">
    <button type="button" class="mdb-detail-close" data-bs-dismiss="offcanvas" aria-label="Fechar"><i class="fas fa-xmark"></i></button>
    <div class="mdb-detail-body">
        <span class="mdb-detail-avatar" id="mdbDetailAvatar"></span>
        <span class="mdb-detail-datepill"><i class="fas fa-cake-candles me-1"></i><span id="mdbDetailDate"></span></span>
        <div class="mdb-detail-name" id="mdbDetailName"></div>
        <div class="mdb-detail-meta" id="mdbDetailMeta"></div>
        <button type="button" class="mdb-hero-btn" id="mdbDetailWhatsapp">
            <span class="mdb-hero-btn-default"><i class="fas fa-comment"></i> Parabenizar no WhatsApp</span>
            <span class="mdb-hero-btn-greeted"><i class="fas fa-check"></i> Mensagem enviada</span>
        </button>
        <div class="mdb-detail-actions">
            <a href="#" id="mdbDetailCall" class="mdb-detail-btn is-dark"><i class="fas fa-phone"></i> Ligar</a>
            <a href="#" id="mdbDetailProfile" class="mdb-detail-btn is-outline"><i class="fas fa-user"></i> Ver perfil</a>
        </div>
        <div class="mdb-detail-footnote">Toque fora para fechar</div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var sheetEl = document.getElementById('mdbBdaySheet');
    if (!sheetEl) return;
    var whatsappBtn = document.getElementById('mdbDetailWhatsapp');
    var callBtn = document.getElementById('mdbDetailCall');
    var profileBtn = document.getElementById('mdbDetailProfile');

    // The offcanvas itself opens via the element's own data-bs-toggle/data-bs-target
    // attributes (same declarative pattern used everywhere else in this app) — this
    // listener only needs to populate the sheet's content before Bootstrap shows it,
    // and since it's registered first it runs first on the same click.
    document.querySelectorAll('[data-mdb-open]').forEach(function (card) {
        card.addEventListener('click', function () {
            var id = card.getAttribute('data-id');
            var name = card.getAttribute('data-name');
            var cong = card.getAttribute('data-cong');
            var date = card.getAttribute('data-date');
            var weekday = card.getAttribute('data-weekday');
            var rel = card.getAttribute('data-rel');
            var phone = card.getAttribute('data-phone');

            document.getElementById('mdbDetailAvatar').textContent = name.split(/\s+/).map(function (p) { return p[0]; }).slice(0, 2).join('').toUpperCase();
            document.getElementById('mdbDetailDate').textContent = date + (weekday ? ' • ' + weekday : '');
            document.getElementById('mdbDetailName').textContent = name;
            document.getElementById('mdbDetailMeta').textContent = cong + (rel ? ' • ' + rel : '');

            whatsappBtn.setAttribute('data-birthday-id', id);
            whatsappBtn.classList.toggle('is-greeted', typeof isBirthdayGreeted === 'function' && isBirthdayGreeted(id));
            whatsappBtn.onclick = function () { openBirthdayCard(name, parseInt(id, 10)); };

            if (phone) {
                callBtn.href = 'tel:' + phone.replace(/\D/g, '');
                callBtn.classList.remove('d-none');
            } else {
                callBtn.classList.add('d-none');
            }
            profileBtn.href = '/admin/members/show/' + id;
        });
    });

    // Reflect "already greeted today" on load (hero button).
    document.querySelectorAll('.mdb-hero-btn[data-birthday-id]').forEach(function (btn) {
        var id = btn.getAttribute('data-birthday-id');
        if (typeof isBirthdayGreeted === 'function' && isBirthdayGreeted(id)) {
            btn.classList.add('is-greeted');
        }
    });
});
</script>
<?php endif; ?>
